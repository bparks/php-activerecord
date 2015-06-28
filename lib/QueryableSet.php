<?php

namespace ActiveRecord;

class QueryableSet implements \FluentRepository\IQueryable
{
	private /* array */ $_list;

	function __construct ($list)
	{
		$this->_list = $list;
	}

	function where ($expr)
	{
		$new_list = array();
		foreach ($this->_list as $item)
			if ($expr($item))
				$new_list[] = $item;
		return new QueryableSet($new_list);
	}

	function order_by ($expr)
	{
		$new_list = clone $this->_list;
		usort($new_list, $expr);
		return new QueryableSet($new_list);
	}

	function order_by_descending ($expr)
	{
		$new_list = clone $this->_list;
		usort($new_list, $expr);
		return new QueryableSet(array_reverse($new_list));
	}

	function skip ($count)
	{
		return new QueryableSet(array_slice($this->_list, $count));
	}

	function take ($count)
	{
		return new QueryableSet(array_slice($this->_list, 0, $count));
	}

	function select ($expr)
	{
		return new QueryableSet(array_map($expr, $this->_list));
	}

	function any ($expr = null)
	{
		if ($expr)
			return $this->where($expr)->any();
		else
			return !empty($this->_list);
	}

	function first ($expr = null)
	{
		if ($expr)
			return $this->where($expr)->first();
		else
			return count($this->_list) > 0 ? $this->_list[0] : null;
	}

	function to_array ()
	{
		return $this->_list;
	}
}