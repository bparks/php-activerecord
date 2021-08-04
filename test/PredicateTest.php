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
}