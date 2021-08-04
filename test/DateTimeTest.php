<?php
use ActiveRecord\DateTime as DateTime;
use ActiveRecord\DatabaseException;

class DateTimeTest extends PHPUnit\Framework\TestCase
{
	public function assert_datetime_equals($expected, $actual)
	{
		$this->assertEquals($expected->format(DateTime::ISO8601), $actual->format(DateTime::ISO8601));
	}

	public function setUp(): void
	{
		$this->date = new DateTime();
		$this->original_format = DateTime::$DEFAULT_FORMAT;
	}

	public function tearDown(): void
	{
		DateTime::$DEFAULT_FORMAT = $this->original_format;
	}

	private function assert_dirtifies($method /*, method params, ...*/)
	{
		try {
			$model = new Author();
		} catch (DatabaseException $e) {
			$this->markTestSkipped('failed to connect. '.$e->getMessage());
		}
		$datetime = new DateTime();
		$datetime->attribute_of($model,'some_date');

		$args = func_get_args();
		array_shift($args);

		call_user_func_array(array($datetime,$method),$args);
		$this->assert_has_keys('some_date', $model->dirty_attributes());
	}

	public function test_should_flag_the_attribute_dirty()
	{
		$this->assert_dirtifies('setDate',2001,1,1);
		$this->assert_dirtifies('setISODate',2001,1);
		$this->assert_dirtifies('setTime',1,1);
		$this->assert_dirtifies('setTimestamp',1);
	}

	public function test_set_iso_date()
	{
		$a = new \DateTime();
		$a->setISODate(2001,1);

		$b = new DateTime();
		$b->setISODate(2001,1);

		$this->assert_datetime_equals($a,$b);
	}

	public function test_set_time()
	{
		$a = new \DateTime();
		$a->setTime(1,1);

		$b = new DateTime();
		$b->setTime(1,1);

		$this->assert_datetime_equals($a,$b);
	}

	public function test_get_format_with_friendly()
	{
		$this->assertEquals('Y-m-d H:i:s', DateTime::get_format('db'));
	}

	public function test_get_format_with_format()
	{
		$this->assertEquals('Y-m-d', DateTime::get_format('Y-m-d'));
	}

	public function test_get_format_with_null()
	{
		$this->assertEquals(\DateTime::RFC2822, DateTime::get_format());
	}

	public function test_format()
	{
		$this->assertTrue(is_string($this->date->format()));
		$this->assertTrue(is_string($this->date->format('Y-m-d')));
	}

	public function test_format_by_friendly_name()
	{
		$d = date(DateTime::get_format('db'));
		$this->assertEquals($d, $this->date->format('db'));
	}

	public function test_format_by_custom_format()
	{
		$format = 'Y/m/d';
		$this->assertEquals(date($format), $this->date->format($format));
	}

	public function test_format_uses_default()
	{
		$d = date(DateTime::$FORMATS[DateTime::$DEFAULT_FORMAT]);
		$this->assertEquals($d, $this->date->format());
	}

	public function test_all_formats()
	{
		foreach (DateTime::$FORMATS as $name => $format)
			$this->assertEquals(date($format), $this->date->format($name));
	}

	public function test_change_default_format_to_format_string()
	{
		DateTime::$DEFAULT_FORMAT = 'H:i:s';
		$this->assertEquals(date(DateTime::$DEFAULT_FORMAT), $this->date->format());
	}

	public function test_change_default_format_to_friently()
	{
		DateTime::$DEFAULT_FORMAT = 'short';
		$this->assertEquals(date(DateTime::$FORMATS['short']), $this->date->format());
	}

	public function test_to_string()
	{
		$this->assertEquals(date(DateTime::get_format()), "" . $this->date);
	}
}
?>
