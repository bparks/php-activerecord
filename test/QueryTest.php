<?php

require_once __DIR__.'/../lib/Predicate.php';
require_once __DIR__.'/../lib/Query.php';

use ActiveRecord\Q;
use ActiveRecord\Query;

class QueryTest extends PHPUnit\Framework\TestCase
{
    public function test_where_creates_proper_conditions()
    {
        $predicate = Q::equals(1, Q::param(1));
        $query = (new Query(null))->where($predicate); // Don't care about table for this test
        $options = $query->toOptions();

        $this->assertNotNull($options);
        $this->assertTrue(is_array($options));
        $this->assertTrue(isset($options['conditions']));
        $this->assertEquals(count($options['conditions']), 2);
        $this->assertEquals($options['conditions'][0], "1 = ?");
        $this->assertEquals($options['conditions'][1], "1");
    }

    public function test_order_by_creates_order()
    {
        $query = (new Query(null))->orderBy("column asc");
        $options = $query->toOptions();

        $this->assertNotNull($options);
        $this->assertTrue(is_array($options));
        $this->assertTrue(isset($options['order']));
        $this->assertEquals($options['order'], "column asc");
    }
}