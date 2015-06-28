<?php

namespace FluentRepository;

interface IQueryable
{
	function where($expression);
	function order_by($expression);
	function order_by_descending($expression);
	function skip($count);
	function take($count);
	function select($expr);
	function any($expr = null);
	function first($expr = null);
	function to_array();
}