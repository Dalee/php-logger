<?php

namespace Dalee\Logger\Tests\Unit\Adapters;

use Dalee\Logger\Tests\Unit\ApplicationTestCase;
use Dalee\Logger\Logger;
use Dalee\Logger\Adapter\AbstractAdapter;
use Dalee\Logger\Adapter\SyslogAdapter;

class SyslogAdapterTest extends ApplicationTestCase {

	public function testPriorityCalculation() {
		$mock = $this->getMock(
			'\Dalee\Logger\Adapter\SyslogAdapter'
		);

		$mock
			->expects($this->any())
			->method('calcPriority');

		$reflection = new \ReflectionObject($mock);
		$method = $reflection->getMethod('calcPriority');
		$method->setAccessible(true);

		$this->assertEquals(165, $method->invokeArgs($mock, [20, Logger::SEVERITY_NOTICE]));
		$this->assertEquals(0, $method->invokeArgs($mock, [0, Logger::SEVERITY_EMERGENCY]));
	}

	public function testCleanMessage() {
		$message = "\x12|this|is|sparta\x09\x01\x02";
		$mock = $this->getMock(
			'\Dalee\Logger\Adapter\SyslogAdapter'
		);

		$mock
			->expects($this->any())
			->method('cleanMessage');

		$reflection = new \ReflectionObject($mock);
		$method = $reflection->getMethod('cleanMessage');
		$method->setAccessible(true);

		$this->assertEquals("|this|is|sparta", $method->invoke($mock, $message));
	}

	public function testMessageFormat() {
		$syslog = new \Dalee\Logger\Adapter\SyslogAdapter;

		$res = $syslog->write(
			Logger::SEVERITY_EMERGENCY,
			0,
			'localhost',
			'app',
			'2016-12-01 23:23:23.4554',
			'syslog goes here'
		);

		$this->assertEquals("<0>2016-12-01 23:23:23.4554 localhost app: syslog goes here", $res);
	}

	public function testMessageFormatNoHostname() {
		$syslog = new \Dalee\Logger\Adapter\SyslogAdapter;

		$res = $syslog->write(
			Logger::SEVERITY_EMERGENCY,
			0,
			'',
			'app',
			'2016-12-01 23:23:23.4554',
			'syslog goes here'
		);

		$this->assertEquals("<0>2016-12-01 23:23:23.4554 app: syslog goes here", $res);
	}

	public function testMessageFormatNoApp() {
		$syslog = new \Dalee\Logger\Adapter\SyslogAdapter;

		$res = $syslog->write(
			Logger::SEVERITY_EMERGENCY,
			0,
			'localhost',
			'',
			'2016-12-01 23:23:23.4554',
			'syslog goes here'
		);

		$this->assertEquals("<0>2016-12-01 23:23:23.4554 localhost syslog goes here", $res);
	}

	public function testMessageFormatNoHostnameNoApp() {
		$syslog = new \Dalee\Logger\Adapter\SyslogAdapter;

		$res = $syslog->write(
			Logger::SEVERITY_EMERGENCY,
			0,
			'',
			'',
			'2016-12-01 23:23:23.4554',
			'syslog goes here'
		);

		$this->assertEquals("<0>2016-12-01 23:23:23.4554 syslog goes here", $res);
	}

	public function testMessageSendIfNoData() {
		$syslog = new \Dalee\Logger\Adapter\SyslogAdapter;

		$res = $syslog->write(
			Logger::SEVERITY_EMERGENCY,
			0,
			'localhost',
			'app',
			'2016-12-01 23:23:23.4554',
			"\x01\x02"
		);

		$this->assertFalse(!!$res);
	}
}
