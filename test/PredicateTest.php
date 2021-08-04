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

    public function test_string_parameter()
    {
        $params = [];
        $this->assertEquals(Q::equals('col_name', Q::param('bob'))->toAnsiSql($params), "col_name = ?");
        $this->assertEquals(count($params), 1);
        $this->assertTrue($params[0] instanceof ActiveRecord\Parameter);
        $this->assertEquals($params[0]->value(), 'bob');
    }

    public function test_compound_predicate()
    {
        $params = [];
        $this->assertEquals(Q::and(Q::equals(1, 1), Q::isNotNull(1))->toAnsiSql($params), "1 = 1 and 1 is not null");
    }

    public function test_compound_predicate_with_multiple_parameters()
    {
        $params = [];
        $predicate = Q::and(Q::equals('col_name', Q::param('bob')), Q::or(Q::equals('col2', Q::param(1)), Q::equals('col3', Q::param(2))));
        $actual = $predicate->toAnsiSql($params);

        $this->assertEquals($actual, "col_name = ? and (col2 = ? or col3 = ?)");
        $this->assertEquals(count($params), 3);
        $this->assertTrue($params[0] instanceof ActiveRecord\Parameter);
        $this->assertEquals($params[0]->value(), 'bob');
        $this->assertTrue($params[1] instanceof ActiveRecord\Parameter);
        $this->assertEquals($params[1]->value(), 1);
        $this->assertTrue($params[2] instanceof ActiveRecord\Parameter);
        $this->assertEquals($params[2]->value(), 2);
    }
}