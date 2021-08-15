<?php
require_once __DIR__ . '/DatabaseLoader.php';

class DatabaseTest extends PHPUnit\Framework\TestCase
{
	protected $conn;
	protected $connection_name;

	public static $log = false;
	public static $db;

	public function setUp(): void
	{
		ActiveRecord\Table::clear_cache();

		$config = ActiveRecord\Config::instance();
		$this->original_default_connection = $config->get_default_connection();

		if ($this->connection_name)
			$config->set_default_connection($this->connection_name);

		if ($this->connection_name == 'sqlite' || $config->get_default_connection() == 'sqlite')
		{
			// need to create the db. the adapter specifically does not create it for us.
			static::$db = substr(ActiveRecord\Config::instance()->get_connection('sqlite'),9);
			new SQLite3(static::$db);
		}

		$connection_name = $this->connection_name;
		try {
			$this->conn = ActiveRecord\ConnectionManager::get_connection($this->connection_name);
		} catch (ActiveRecord\DatabaseException $e) {
			$this->markTestSkipped($this->connection_name . ' failed to connect. '.$e->getMessage());
		}

		$GLOBALS['ACTIVERECORD_LOG'] = false;

		$loader = new DatabaseLoader($this->conn);
		$loader->reset_table_data();

		if (self::$log)
			$GLOBALS['ACTIVERECORD_LOG'] = true;
	}

	public function tearDown(): void
	{
		if ($this->original_default_connection)
			ActiveRecord\Config::instance()->set_default_connection($this->original_default_connection);
	}

	public function assert_exception_message_contains($contains, $closure)
	{
		$message = "";

		try {
			$closure();
		} catch (ActiveRecord\UndefinedPropertyException $e) {
			$message = $e->getMessage();
		}

		$this->assertContains($contains, $message);
	}

	/**
	 * Returns true if $regex matches $actual.
	 *
	 * Takes database specific quotes into account by removing them. So, this won't
	 * work if you have actual quotes in your strings.
	 */
	public function assert_sql_has($needle, $haystack)
	{
		$needle = str_replace(array('"','`'),'',$needle);
		$haystack = str_replace(array('"','`'),'',$haystack);
		return $this->assertContains($needle, $haystack);
	}

	public function assert_sql_doesnt_has($needle, $haystack)
	{
		$needle = str_replace(array('"','`'),'',$needle);
		$haystack = str_replace(array('"','`'),'',$haystack);
		return $this->assertNotContains($needle, $haystack);
	}
}
?>
