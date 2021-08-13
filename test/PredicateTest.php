<?php
require_once __DIR__.'/../lib/Predicate.php';

use ActiveRecord\Q;
use ActiveRecord\Table;

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

    public function test_in()
    {
        $params = [];
        $this->assertEquals(Q::in('col_name', ['a', 'b'])->toAnsiSql($params), "col_name in (a, b)");
    }

    public function test_in_params()
    {
        $params = [];
        $this->assertEquals(Q::in('col_name', [Q::param('a'), Q::param('b')])->toAnsiSql($params), "col_name in (?, ?)");
        $this->assertEquals(2, count($params));
    }

    public function test_like()
    {
        $params = [];
        $this->assertEquals(Q::like('col_name', 'other_name')->toAnsiSql($params), "col_name like concat('%', other_name, '%')");
    }

    public function test_like_param()
    {
        $params = [];
        $this->assertEquals(Q::like('col_name', Q::param('other_name'))->toAnsiSql($params), "col_name like concat('%', ?, '%')");
        $this->assertEquals(1, count($params));
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

    public function test_cleaner_way_of_writing_compound_predicate()
    {
        $params = [];
        $predicate = Q::equals('col_name', Q::param('bob'))->and(Q::equals('col2', Q::param(1))->or(Q::equals('col3', Q::param(2))));
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

    public function test_exists()
    {
        $params = [];
        $table = new Table('SampleModel');
        $predicate = Q::notExists('events', Q::greaterThan('payment_paid', 0));
        $actual = $predicate->toAnsiSql($params, $table);
        $this->assertNotNull($actual);
    }
}

class SampleModel extends ActiveRecord\Model
{
    static $has_many = [
        'events'
    ];
}