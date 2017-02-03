<?php

class LoggerTest extends ApplicationTestCase {
	public function testMessageWithcorrectSeverity() {
		$mock = $this->getMock(
			'Logger', ['_log']
		);

		$mock
			->expects($this->any())
			->method('_log')
			->will($this->returnArgument(0));

		$this->assertEquals(7, $mock->log('message'));
		$this->assertEquals(0, $mock->emerg('message'));
		$this->assertEquals(1, $mock->alert('message'));
		$this->assertEquals(2, $mock->critical('message'));
		$this->assertEquals(3, $mock->error('message'));
		$this->assertEquals(4, $mock->warning('message'));
		$this->assertEquals(5, $mock->notice('message'));
		$this->assertEquals(6, $mock->info('message'));
		$this->assertEquals(7, $mock->debug('message'));
	}

	public function testItAcceptsCorrectFacility() {
		$logger = new Logger(5);
		$this->assertEquals(5, $logger->getFacility());
		$logger = new Logger(355);
		$this->assertEquals(23, $logger->getFacility());
		$logger = new Logger(-1);
		$this->assertEquals(0, $logger->getFacility());
	}

	public function testFacilityChange() {
		$logger = new Logger;
		$this->assertEquals(16, $logger->getFacility());
		$logger->setFacility(3);
		$this->assertEquals(3, $logger->getFacility());
		$logger->setFacility(-200);
		$this->assertEquals(0, $logger->getFacility());
		$logger->setFacility(9999);
		$this->assertEquals(23, $logger->getFacility());
	}

	public function testCorrectHostname() {
		$logger = new Logger(1, 'awesome-site.ru', 'php');
		$this->assertEquals('awesome-site.ru', $logger->getHostname());
		$logger->setHostname('127.0.0.1');
		$this->assertEquals('127.0.0.1', $logger->getHostname());

		$setInvalidHostname = function () {
			$logger = new Logger();
			$logger->setHostname('> invalid');
		};
		$setTooLongHostname = function () {
			$logger = new Logger();
			$logger->setHostname("aaaaaaaaaaaaaaaaaaaaaaaaaaa" .
				"aaaaaaaaaaaaaaaaaaaaaaaaaaa" .
				"aaaaaaaaaaaaaaaaaaaaaaaaaaa" .
				"aaaaaaaaaaaaaaaaaaaaaaaaaaa" .
				"aaaaaaaaaaaaaaaaaaaaaaaaaaa" .
				"aaaaaaaaaaaaaaaaaaaaaaaaaaa" .
				"aaaaaaaaaaaaaaaaaaaaaaaaaaa" .
				"aaaaaaaaaaaaaaaaaaaaaaaaaaa" .
				"aaaaaaaaaaaaaaaaaaaaaaaaaaa" .
				"aaaaaaaaaaaaaaaaaaaaaaaaaaa" .
				"aaaaaaaaaaaaaaaaaaaaaaaaaaa" .
				"aaaaaaaaaaaaaaaaaaaaaaaaaaa");
		};

		$this->assertException($setInvalidHostname);
		$this->assertException($setTooLongHostname);
	}

	public function testCorrectApp() {
		$logger = new Logger;

		$logger->setApp('worker-node1');
		$this->assertEquals('worker-node1', $logger->getApp());
		$logger->setApp('worker-node1');
		$this->assertEquals('worker-node1', $logger->getApp());

		$setInvalidApp = function () {
			$logger = new Logger();
			$logger->setApp('> invalid');
		};
		$setTooLongApp = function () {
			$logger = new Logger();
			$logger->setApp("aaaaaaaaaaaaaaaaaaaaaaaaaaa" .
				"aaaaaaaaaaaaaaaaaaaaaaaaaaa" .
				"aaaaaaaaaaaaaaaaaaaaaaaaaaa" .
				"aaaaaaaaaaaaaaaaaaaaaaaaaaa" .
				"aaaaaaaaaaaaaaaaaaaaaaaaaaa");
		};

		$this->assertException($setInvalidApp);
		$this->assertException($setTooLongApp);
	}
}
