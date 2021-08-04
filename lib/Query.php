<?php

namespace ActiveRecord;

class Query
{
    public function __construct($table, $predicate=null)
    {
        $this->table = $table;
        $this->where = $predicate;
    }

    public function toOptions()
    {
        if ($this->where) {
            $sql = $this->where->toAnsiSql($params);
            $values = array_map(fn ($param) => $param->value(), $params);
            array_unshift($values, $sql);
            $options['conditions'] = $values;
        }

        return $options;
    }

    public function execute()
    {
        $options = $this->toOptions();
        return $this->table->find($options);
    }
}