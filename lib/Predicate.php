<?php

namespace ActiveRecord;

class Predicate
{
    public function __construct($left, $op, $right = null, $addtl = null)
    {
        $this->left = $left;
        $this->op = $op;
        $this->right = $right;
        $this->addtl = $addtl;
    }

    public function toAnsiSql(&$params)
    {
        $retval = $this->toAnsiOperand($this->left, $params).' '.$this->toAnsiOperator($this->op);
        if ($this->op != 'null' && $this->op != 'notnull')
            $retval .= ' '.$this->toAnsiOperand($this->right, $params);
        if ($this->op == 'between')
            $retval .= ' and '.$this->toAnsiOperand($this->addtl, $params);
        return $retval;
    }

    private function toAnsiOperand($value, &$params)
    {
        if ($value instanceof Parameter)
        {
            $params[] = $value;
            return "?";
        }
        else if ($value instanceof Predicate)
        {
            $subexpr = $value->toAnsiSql($params);
            if (($this->op == 'and' || $this->op == 'or') && ($value->op == 'and' || $value->op == 'or'))
                return "($subexpr)";
            return $subexpr;
        }
        else if ($value instanceof Aggregate)
        {
            return $value->toAnsiSql($params);
        }
        else if (is_array($value))
        {
            return '('.implode(', ', array_map(function ($x) use (&$params) {
                return $this->toAnsiOperand($x, $params);
            }, $value)).')';
        }
        else
        {
            return $value;
        }
    }

    private function toAnsiOperator($operator)
    {
        if (in_array($operator, ['=', '!=', '>', '>=', '<', '<=', 'between', 'and', 'or', 'in', 'like']))
            return $operator;
        
        if ($operator == 'null')
            return 'is null';
        if ($operator == 'notnull')
            return 'is not null';

        throw new ActiveRecordException("Unkown operator '$operator'");
    }

    public function __call($method, $args)
    {
        if (!method_exists(Q::class, $method))
            throw new Exception("Unknown predicate operator '$method'");

        array_unshift($args, $this);
        
        return call_user_func_array("\ActiveRecord\Q::$method", $args);
    }
}

class JoinPredicate
{
    public function __construct($operator, $relationship, $predicate)
    {
        $this->operator = $operator;
        $this->relationship = $relationship;
        $this->predicate = $predicate;
    }

    public function toAnsiSql(&$params, Table $table)
    {
        $rel = $table->get_relationship($this->relationship);
        $join_table = $rel->get_table();

        $from_table_name = $table->get_fully_qualified_table_name();
        $join_table_name = $join_table->get_fully_qualified_table_name();
        $foreign_key = $rel->foreign_key[0];
        $primary_key = $rel->primary_key[0];

        $sql = "exists (select 1 from $join_table_name where $from_table_name.$foreign_key = $join_table_name.$primary_key";
        if ($this->predicate)
            $sql .= ' and ('.$this->predicate->toAnsiSql($params).')';
        return $sql.')';
    }
}

class Parameter
{
    public function __construct($value)
    {
        $this->value = $value;
    }

    public function value()
    {
        return $this->value;
    }
}

class Aggregate
{
    public function __construct(string $function, ...$values)
    {
        $this->function = $function;
        $this->values = $values;
    }

    public function toAnsiOperand($value, &$params)
    {
        if ($value instanceof Parameter)
        {
            $params[] = $value;
            return "?";
        }
        else if ($value == '%')
        {
            return "'%'";
        }
        else
        {
            return $value;
        }
    }

    public function toAnsiSql(&$params)
    {
        if ($this->function == 'concat')
            return 'concat('.implode(', ', array_map(function ($x) use (&$params) {
                return $this->toAnsiOperand($x, $params);
            }, $this->values)).')';
        
        throw new ActiveRecordException("Unkown aggregate '$this->function'");
    }
}

class Q
{
    static function equals($x, $y): Predicate
    {
        return new Predicate($x, '=', $y);
    }

    static function notEquals($x, $y): Predicate
    {
        return new Predicate($x, '!=', $y);
    }

    static function greaterThan($x, $y): Predicate
    {
        return new Predicate($x, '>', $y);
    }

    static function greaterOrEqual($x, $y): Predicate
    {
        return new Predicate($x, '>=', $y);
    }

    static function lessThan($x, $y): Predicate
    {
        return new Predicate($x, '<', $y);
    }

    static function lessOrEqual($x, $y): Predicate
    {
        return new Predicate($x, '<=', $y);
    }

    static function between($x, $y, $z): Predicate
    {
        return new Predicate($x, 'between', $y, $z);
    }

    static function isNull($x): Predicate
    {
        return new Predicate($x, 'null');
    }

    static function isNotNull($x): Predicate
    {
        return new Predicate($x, 'notnull');
    }

    static function and($x, $y): Predicate
    {
        return new Predicate($x, 'and', $y);
    }

    static function or($x, $y): Predicate
    {
        return new Predicate($x, 'or', $y);
    }

    static function in($x, array $y): Predicate
    {
        return new Predicate($x, 'in', $y);
    }

    static function like($x, $y): Predicate
    {
        return new Predicate($x, 'like', Q::concat('%', $y, '%'));
    }

    static function param($value): Parameter
    {
        return new Parameter($value);
    }

    static function concat(...$values): Aggregate
    {
        return new Aggregate('concat', ...$values);
    }

    static function exists($relationship, Predicate $predicate): JoinPredicate
    {
        return new JoinPredicate('exists', $relationship, $predicate);
    }

    static function notExists($relationship, Predicate $predicate): JoinPredicate
    {
        return new JoinPredicate('exists', $relationship, $predicate);
    }
}