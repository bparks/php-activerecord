<?php

namespace ActiveRecord;

class Query
{
    public function __construct($table, $predicate=null)
    {
        $this->table = $table;
        $this->where = $predicate;
    }

    public function execute()
    {
        $params = [];
        $sql = 'select * from '.$this->table->get_fully_qualified_table_name();

        if ($this->where)
            $sql .= 'where '.$this->where->toAnsiSql($params);
        
        return $this->table->find_by_sql($sql, array_map(fn ($param) => $param->value(), $params));
    }
}