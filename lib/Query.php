<?php

namespace ActiveRecord;

class Query
{
    private ?Table $table;
    private ?Predicate $where = null;
    private ?string $order = null;

    public function __construct($table, /** @deprecated */ Predicate $predicate=null)
    {
        $this->table = $table;
        $this->where = $predicate;
    }

    public function where(Predicate $predicate): Query
    {
        if ($this->where) {
            //Merge
            $this->where = Q::and($this->where, $predicate);
        } else {
            //Overwrite
            $this->where = $predicate;
        }

        return $this;
    }

    public function orderBy($order): Query
    {
        $this->order = $order;

        return $this;
    }

    public function toOptions()
    {
        if ($this->where) {
            $sql = $this->where->toAnsiSql($params);
            $values = array_map(fn ($param) => $param->value(), $params);
            array_unshift($values, $sql);
            $options['conditions'] = $values;
        }

        if ($this->order) {
            $options['order'] = $this->order;
        }

        return $options;
    }

    public function execute()
    {
        $options = $this->toOptions();
        return $this->table->find($options);
    }
}