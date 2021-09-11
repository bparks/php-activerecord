<?php
require_once __DIR__.'/../lib/Predicate.php';

use ActiveRecord\Q;
use ActiveRecord\Table;

class PredicateDatabaseTest extends DatabaseTest
{
    public function test_exists()
    {
        $params = [];
        $table = Table::load('Venue');
        $predicate = Q::exists('events', Q::greaterThan('payment_paid', 0));
        $actual = $predicate->toAnsiSql($params, $table);
        $this->assertNotNull($actual);
        $this->assertEquals('exists (select 1 from `events` where `venues`.venue_id = `events`.id and (payment_paid > 0))', $actual);
    }
    
    public function test_notExists()
    {
        $params = [];
        $table = Table::load('Venue');
        $predicate = Q::notExists('events', Q::greaterThan('payment_paid', 0));
        $actual = $predicate->toAnsiSql($params, $table);
        $this->assertNotNull($actual);
        $this->assertEquals('not exists (select 1 from `events` where `venues`.venue_id = `events`.id and (payment_paid > 0))', $actual);
    }
}