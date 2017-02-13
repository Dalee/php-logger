<?php

namespace Dalee\Logger\Tests\Unit;

use Dalee\Logger\Adapter\SyslogAdapter;
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
		$logger = new Logger();
		$this->assertEquals(16, $logger->getFacility());
		$logger->setFacility(3);
		$this->assertEquals(3, $logger->getFacility());

		$setWrongFacility = function() {
			(new Logger())->setFacility(-1);
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
			(new Logger())->setHostname('> invalid');
		};
		$setTooLongHostname = function () {
			(new Logger())->setHostname("aaaaaaaaaaaaaaaaaaaaaaaaaaa" .
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
		$logger = new Logger();

		$logger->setAppName('worker-node1');
		$this->assertEquals('worker-node1', $logger->getAppName());
		$logger->setAppName('worker-node1');
		$this->assertEquals('worker-node1', $logger->getAppName());

		$setInvalidApp = function () {
			(new Logger())->setAppName('> invalid');
		};
		$setTooLongApp = function () {
			(new Logger())->setAppName("aaaaaaaaaaaaaaaaaaaaaaaaaaa" .
				"aaaaaaaaaaaaaaaaaaaaaaaaaaa" .
				"aaaaaaaaaaaaaaaaaaaaaaaaaaa" .
				"aaaaaaaaaaaaaaaaaaaaaaaaaaa" .
				"aaaaaaaaaaaaaaaaaaaaaaaaaaa"
			);
		};

		$this->assertException($setInvalidApp, '\UnexpectedValueException');
		$this->assertException($setTooLongApp, '\UnexpectedValueException');
	}

	public function testLogLevelAcceptance() {
		$logger = new Logger();

		$this->assertEquals(Logger::SEVERITY_DEBUG, $logger->getLogLevel());
		$logger->setLogLevel('emerg');
		$this->assertEquals(Logger::SEVERITY_EMERGENCY, $logger->getLogLevel());
		$logger->setLogLevel('alert');
		$this->assertEquals(Logger::SEVERITY_ALERT, $logger->getLogLevel());
		$logger->setLogLevel('critical');
		$this->assertEquals(Logger::SEVERITY_CRITICAL, $logger->getLogLevel());
		$logger->setLogLevel('error');
		$this->assertEquals(Logger::SEVERITY_ERROR, $logger->getLogLevel());
		$logger->setLogLevel('warning');
		$this->assertEquals(Logger::SEVERITY_WARNING, $logger->getLogLevel());
		$logger->setLogLevel('notice');
		$this->assertEquals(Logger::SEVERITY_NOTICE, $logger->getLogLevel());
		$logger->setLogLevel('info');
		$this->assertEquals(Logger::SEVERITY_INFORMATIONAL, $logger->getLogLevel());
		$logger->setLogLevel('debug');
		$this->assertEquals(Logger::SEVERITY_DEBUG, $logger->getLogLevel());

		$invalidVal = function () {
			(new Logger())->setLogLevel('bad');
		};

		$this->assertException($invalidVal, '\InvalidArgumentException');
	}
	
	public function testLogLevelBehavior() {
		$logger = new Logger();
		$mock = $this->getMock('\Dalee\Logger\Adapter\SyslogAdapter');
		$this->messages = [];

		$mock
			->expects($this->any())
			->method('write')
			->will($this->returnCallback(function($severity, $facility, $hostname, $appName, $date, $message){
				array_push($this->messages, ['severity' => $severity, 'message' => $message]);
			}));

		$logger->addAdapter($mock);
		
		$logger->setLogLevel('warning');
		$logger->log('DEBUG level should not be logged');
		$logger->info('INFO level should not be logged');
		$logger->notice('NOTICE should not be logged');
		$logger->warning('WARNING level should be logged');
		$logger->error('ERROR level should be logged');
		$logger->critical('CRITICAL level should be logged');
		$logger->alert('ALERT level should be logged');
		$logger->emerg('EMERG level should be logged');

		$this->assertEquals(5, count($this->messages));

		$this->assertEquals($this->messages[0]['message'], 'WARNING level should be logged');
		$this->assertEquals($this->messages[1]['message'], 'ERROR level should be logged');
		$this->assertEquals($this->messages[2]['message'], 'CRITICAL level should be logged');
		$this->assertEquals($this->messages[3]['message'], 'ALERT level should be logged');
		$this->assertEquals($this->messages[4]['message'], 'EMERG level should be logged');

		$logger->clearAdapters();
		$adapters = $logger->getAdapters();
		$this->assertEquals(0, count($adapters));
	}
}
