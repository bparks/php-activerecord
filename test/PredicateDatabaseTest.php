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
        $predicate = Q::notExists('events', Q::greaterThan('payment_paid', 0));
        $actual = $predicate->toAnsiSql($params, $table);
        $this->assertNotNull($actual);
        error_log($actual);
    }
}