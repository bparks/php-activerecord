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
        if ($this->op != 'null')
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
        else
        {
            return $value;
        }
    }

    private function toAnsiOperator($operator)
    {
        if (in_array($operator, ['=', '!=', '>', '>=', '<', '<=', 'between', 'and', 'or']))
            return $operator;
        
        if ($operator == 'null')
            return 'is null';

        throw new ActiveRecordException("Unkown operator '$operator'");
    }
}

class Parameter
{
    public function __construct($value)
    {
        $this->value = $value;
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

    static function and($x, $y): Predicate
    {
        return new Predicate($x, 'and', $y);
    }

    static function or($x, $y): Predicate
    {
        return new Predicate($x, 'or', $y);
    }

    static function param($value): Parameter
    {
        return new Parameter($value);
    }
}