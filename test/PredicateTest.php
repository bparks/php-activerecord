<?php
require_once __DIR__.'/../lib/Predicate.php';

use ActiveRecord\Q;

class PredicateTest extends PHPUnit\Framework\TestCase
{
    public function test_simple_equality()
    {
        $params = [];
        $this->assertEquals(Q::equals(1, 1)->toAnsiSql($params), "1 = 1");
    }

    public function test_between()
    {
        $params = [];
        $this->assertEquals(Q::between(2, 1, 3)->toAnsiSql($params), "2 between 1 and 3");
    }

    public function test_null()
    {
        $params = [];
        $this->assertEquals(Q::isNull('col_name')->toAnsiSql($params), "col_name is null");
    }
}