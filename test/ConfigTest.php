<?php

use ActiveRecord\Config;
use ActiveRecord\ConfigException;

class TestLogger
{
	private function log() {}
}

class ConfigTest extends PHPUnit\Framework\TestCase
{
	public function setUp(): void
	{
		$this->config = new Config();
		$this->connections = array('development' => 'mysql://blah/development', 'test' => 'mysql://blah/test');
		$this->config->set_connections($this->connections);
	}

	/**
	 * @expectedException ActiveRecord\ConfigException
	 */
	public function test_set_connections_must_be_array()
	{
		$this->expectException(ActiveRecord\ConfigException::class);
		$this->config->set_connections(null);
	}

	public function test_get_connections()
	{
		$this->assertEquals($this->connections,$this->config->get_connections());
	}

	public function test_get_connection()
	{
		$this->assertEquals($this->connections['development'],$this->config->get_connection('development'));
	}

	public function test_get_invalid_connection()
	{
		$this->assertNull($this->config->get_connection('whiskey tango foxtrot'));
	}

	public function test_get_default_connection_and_connection()
	{
		$this->config->set_default_connection('development');
		$this->assertEquals('development',$this->config->get_default_connection());
		$this->assertEquals($this->connections['development'],$this->config->get_default_connection_string());
	}

	public function test_get_default_connection_and_connection_string_defaults_to_development()
	{
		$this->assertEquals('development',$this->config->get_default_connection());
		$this->assertEquals($this->connections['development'],$this->config->get_default_connection_string());
	}

	public function test_get_default_connection_string_when_connection_name_is_not_valid()
	{
		$this->config->set_default_connection('little mac');
		$this->assertNull($this->config->get_default_connection_string());
	}

	public function test_default_connection_is_set_when_only_one_connection_is_present()
	{
		$this->config->set_connections(array('development' => $this->connections['development']));
		$this->assertEquals('development',$this->config->get_default_connection());
	}

	public function test_set_connections_with_default()
	{
		$this->config->set_connections($this->connections,'test');
		$this->assertEquals('test',$this->config->get_default_connection());
	}

	public function test_initialize_closure()
	{
		$test = $this;

		Config::initialize(function($cfg) use ($test)
		{
			$test->assertNotNull($cfg);
			$test->assertEquals('ActiveRecord\Config',get_class($cfg));
		});
	}

	public function test_logger_object_must_implement_log_method()
	{
		try {
			$this->config->set_logger(new TestLogger);
			$this->fail();
		} catch (ConfigException $e) {
			$this->assertEquals($e->getMessage(), "Logger object must implement a public log method");
		}
	}
}
?>
