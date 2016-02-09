<?php

namespace ActiveRecord;

interface IQueryable extends \Iterator
{
	function where($expression);
	function order_by($expression);
	function order_by_descending($expression);
	function skip($count);
	function take($count);
	function select($expr);
	function any($expr = null);
	function first($expr = null);
	function count();
	function to_array();
}