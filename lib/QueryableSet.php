<?php

namespace ActiveRecord;

class QueryableSet implements \ActiveRecord\IQueryable
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
		$new_list = $this->_list;
		usort($new_list, function ($a, $b) use ($expr) {
            $va = $expr($a);
            $vb = $expr($b);
            if ($va == $vb) return 0;
            if ($va > $vb) return 1;
            return -1;
        });
		return new QueryableSet($new_list);
	}

	function order_by_descending ($expr)
	{
		$new_list = $this->_list;
		usort($new_list, function ($a, $b) use ($expr) {
            $va = $expr($a);
            $vb = $expr($b);
            if ($va == $vb) return 0;
            if ($va > $vb) return -1;
            return 1;
        });
		return new QueryableSet($new_list);
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

	// Countable
	function count ()
	{
		return count($this->_list);
	}

	function to_array ()
	{
		return $this->_list;
	}

	// Traversable
	private $position = 0;

	function rewind() {
        $this->position = 0;
    }

    function current() {
        return $this->_list[$this->position];
    }

    function key() {
        return $this->position;
    }

    function next() {
        ++$this->position;
    }

    function valid() {
        return isset($this->_list[$this->position]);
	}

	// ArrayAccess
	public function offsetSet($offset, $value) {
        if (is_null($offset)) {
            $this->_list[] = $value;
        } else {
            $this->_list[$offset] = $value;
        }
    }

    public function offsetExists($offset) {
        return isset($this->_list[$offset]);
    }

    public function offsetUnset($offset) {
        unset($this->_list[$offset]);
    }

    public function offsetGet($offset) {
        return isset($this->_list[$offset]) ? $this->_list[$offset] : null;
	}
}
