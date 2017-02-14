<?php

namespace Dalee\Logger\Tests\Unit;

use Dalee\Logger\Adapter\SyslogAdapter;
use Dalee\Logger\Logger;

class LoggerTest extends ApplicationTestCase {
	protected $logger;

	public function setUp() {
		$this->logger = new Logger();
	}

	public function tearDown() {
		unset($this->logger);
	}

	public function testItAcceptsCorrectFacility() {
		$logger = new Logger(5);
		$this->assertEquals(5, $logger->getFacility());
	}

	public function testItThrowsExceptionOnWrongFacility() {
		$InitWithWrongFacility = function() {
			$logger = new Logger(355);
		};
		$this->assertException($InitWithWrongFacility, '\UnexpectedValueException');
	}

	public function testFacilityChange() {
		$this->assertEquals(1, $this->logger->getFacility());
		$this->logger->setFacility(3);
		$this->assertEquals(3, $this->logger->getFacility());
	}

	public function testItTrowsExceptionOnWrongFacilityPassedToSetter() {
		$setWrongFacility = function() {
			$this->logger->setFacility(355);
		};
		$setWrongFacility2 = function() {
			$this->logger->setFacility(-1);
		};

		$this->assertException($setWrongFacility, '\UnexpectedValueException');
		$this->assertException($setWrongFacility2, '\UnexpectedValueException');
	}

	public function testCorrectHostname() {
		$this->logger->setHostname('awesome-site.ru');
		$this->assertEquals('awesome-site.ru', $this->logger->getHostname());
		$this->logger->setHostname('127.0.0.1');
		$this->assertEquals('127.0.0.1', $this->logger->getHostname());
	}

	public function testExceptionOnWrongHostname() {
		$setInvalidHostname = function () {
			$this->logger->setHostname('> invalid');
		};
		$setTooLongHostname = function () {
			$this->logger->setHostname("aaaaaaaaaaaaaaaaaaaaaaaaaaa" .
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
		$this->logger->setAppName('worker-node1');
		$this->assertEquals('worker-node1', $this->logger->getAppName());
		$this->logger->setAppName('worker-node1');
		$this->assertEquals('worker-node1', $this->logger->getAppName());
	}

	public function testExceptionOnWrongAppName() {
		$setInvalidApp = function () {
			$this->logger->setAppName('> invalid');
		};
		$setTooLongApp = function () {
			$this->logger->setAppName("aaaaaaaaaaaaaaaaaaaaaaaaaaa" .
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
		$this->assertEquals(Logger::SEVERITY_DEBUG, $this->logger->getLogLevel());
		$this->logger->setLogLevel('emerg');
		$this->assertEquals(Logger::SEVERITY_EMERGENCY, $this->logger->getLogLevel());
		$this->logger->setLogLevel('alert');
		$this->assertEquals(Logger::SEVERITY_ALERT, $this->logger->getLogLevel());
		$this->logger->setLogLevel('critical');
		$this->assertEquals(Logger::SEVERITY_CRITICAL, $this->logger->getLogLevel());
		$this->logger->setLogLevel('error');
		$this->assertEquals(Logger::SEVERITY_ERROR, $this->logger->getLogLevel());
		$this->logger->setLogLevel('warning');
		$this->assertEquals(Logger::SEVERITY_WARNING, $this->logger->getLogLevel());
		$this->logger->setLogLevel('notice');
		$this->assertEquals(Logger::SEVERITY_NOTICE, $this->logger->getLogLevel());
		$this->logger->setLogLevel('info');
		$this->assertEquals(Logger::SEVERITY_INFORMATIONAL, $this->logger->getLogLevel());
		$this->logger->setLogLevel('debug');
		$this->assertEquals(Logger::SEVERITY_DEBUG, $this->logger->getLogLevel());
	}

	public function testLogLevelException() {
		$invalidVal = function () {
			$this->logger->setLogLevel('bad');
		};

		$this->assertException($invalidVal, '\InvalidArgumentException');
	}
	
	public function testLogLevelBehavior() {
		$mock = $this->getMock('\Dalee\Logger\Adapter\AdapterInterface');
		$this->messages = [];

		$mock
			->expects($this->any())
			->method('write')
			->will($this->returnCallback(function($severity, $facility, $hostname, $appName, $date, $message){
				array_push($this->messages, ['severity' => $severity, 'message' => $message]);
			}));

		$this->logger->addAdapter($mock);
		
		$this->logger->setLogLevel('warning');
		$this->logger->log('DEBUG level should not be logged');
		$this->logger->info('INFO level should not be logged');
		$this->logger->notice('NOTICE should not be logged');
		$this->logger->warning('WARNING level should be logged');
		$this->logger->error('ERROR level should be logged');
		$this->logger->critical('CRITICAL level should be logged');
		$this->logger->alert('ALERT level should be logged');
		$this->logger->emerg('EMERG level should be logged');

		$this->assertEquals(5, count($this->messages));

		$this->assertEquals($this->messages[0]['message'], 'WARNING level should be logged');
		$this->assertEquals($this->messages[1]['message'], 'ERROR level should be logged');
		$this->assertEquals($this->messages[2]['message'], 'CRITICAL level should be logged');
		$this->assertEquals($this->messages[3]['message'], 'ALERT level should be logged');
		$this->assertEquals($this->messages[4]['message'], 'EMERG level should be logged');

		$this->logger->clearAdapters();
		$adapters = $this->logger->getAdapters();
		$this->assertEquals(0, count($adapters));
	}
}
