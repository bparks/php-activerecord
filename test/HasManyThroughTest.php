<?php
include 'helpers/foo.php';

use foo\bar\biz\User;
use foo\bar\biz\Newsletter;

class HasManyThroughTest extends DatabaseTest
{
	public function test_gh101_has_many_through()
	{
		$user = User::find(1);
		$newsletter = Newsletter::find(1);

		$this->assertEquals($newsletter->id, $user->newsletters[0]->id);
		$this->assertEquals(
			'foo\bar\biz\Newsletter',
			get_class($user->newsletters[0])
		);
		$this->assertEquals($user->id, $newsletter->users[0]->id);
		$this->assertEquals(
			'foo\bar\biz\User',
			get_class($newsletter->users[0])
		);
	}

	public function test_gh101_has_many_through_include()
	{
		$user = User::find(1, array(
			'include' => array(
				'user_newsletters'
			)
		));

		$this->assertEquals(1, $user->id);
		$this->assertEquals(1, $user->user_newsletters[0]->id);
	}

	public function test_gh107_has_many_through_include_eager()
	{
		$venue = Venue::find(1, array('include' => array('events')));
		$this->assertEquals(1, $venue->events[0]->id);

		$venue = Venue::find(1, array('include' => array('hosts')));
		$this->assertEquals(1, $venue->hosts[0]->id);
	}

	public function test_gh107_has_many_though_include_eager_with_namespace()
	{
		$user = User::find(1, array(
			'include' => array(
				'newsletters'
			)
		));

		$this->assertEquals(1, $user->id);
		$this->assertEquals(1, $user->newsletters[0]->id);
	}
}
# vim: noet ts=4 nobinary
?>
