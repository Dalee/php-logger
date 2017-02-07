<?php

namespace Dalee\Logger\Tests\Unit;

use Dalee\Logger\Logger;

class LoggerTest extends ApplicationTestCase {

	public function testItAcceptsCorrectFacility() {
		$logger = new Logger(5);
		$this->assertEquals(5, $logger->getFacility());

		$InitWithWrongFacility = function() {
			$logger = new Logger(355);
		};
		$this->assertException($InitWithWrongFacility, '\UnexpectedValueException');
	}

	public function testFacilityChange() {
		$logger = new Logger;
		$this->assertEquals(16, $logger->getFacility());
		$logger->setFacility(3);
		$this->assertEquals(3, $logger->getFacility());

		$setWrongFacility = function() {
			(new Logger)->setFacility(-1);
		};
		$this->assertException($setWrongFacility, '\UnexpectedValueException');
	}

	public function testCorrectHostname() {
		$logger = new Logger(1);
		$logger->setHostname('awesome-site.ru');
		$this->assertEquals('awesome-site.ru', $logger->getHostname());
		$logger->setHostname('127.0.0.1');
		$this->assertEquals('127.0.0.1', $logger->getHostname());

		$setInvalidHostname = function () {
			(new Logger)->setHostname('> invalid');
		};
		$setTooLongHostname = function () {
			(new Logger)->setHostname("aaaaaaaaaaaaaaaaaaaaaaaaaaa" .
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
				"aaaaaaaaaaaaaaaaaaaaaaaaaaa" .
				"aaaaaaaaaaaaaaaaaaaaaaaaaaa"
			);
		};

		$this->assertException($setInvalidHostname, '\UnexpectedValueException');
		$this->assertException($setTooLongHostname, '\UnexpectedValueException');
	}

	public function testCorrectAppName() {
		$logger = new Logger;

		$logger->setAppName('worker-node1');
		$this->assertEquals('worker-node1', $logger->getAppName());
		$logger->setAppName('worker-node1');
		$this->assertEquals('worker-node1', $logger->getAppName());

		$setInvalidApp = function () {
			(new Logger)->setAppName('> invalid');
		};
		$setTooLongApp = function () {
			(new Logger)->setAppName("aaaaaaaaaaaaaaaaaaaaaaaaaaa" .
				"aaaaaaaaaaaaaaaaaaaaaaaaaaa" .
				"aaaaaaaaaaaaaaaaaaaaaaaaaaa" .
				"aaaaaaaaaaaaaaaaaaaaaaaaaaa" .
				"aaaaaaaaaaaaaaaaaaaaaaaaaaa"
			);
		};

		$this->assertException($setInvalidApp, '\UnexpectedValueException');
		$this->assertException($setTooLongApp, '\UnexpectedValueException');
	}
}
