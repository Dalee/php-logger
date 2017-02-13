<?php

namespace Dalee\Logger;

use Dalee\Logger\Adapter\AdapterInterface;

/**
 * Syslog Dictionary
 *
 *  Facility values:
 *     0 kernel messages
 *     1 user-level messages
 *     2 mail system
 *     3 system daemons
 *     4 security/authorization messages
 *     5 messages generated internally by syslogd
 *     6 line printer subsystem
 *     7 network news subsystem
 *     8 UUCP subsystem
 *     9 clock daemon
 *    10 security/authorization messages
 *    11 FTP daemon
 *    12 NTP subsystem
 *    13 log audit
 *    14 log alert
 *    15 clock daemon
 *    16 local user 0 (local0) (default value)
 *    17 local user 1 (local1)
 *    18 local user 2 (local2)
 *    19 local user 3 (local3)
 *    20 local user 4 (local4)
 *    21 local user 5 (local5)
 *    22 local user 6 (local6)
 *    23 local user 7 (local7)
 *  Severity values:
 *    0 Emergency: system is unusable
 *    1 Alert: action must be taken immediately
 *    2 Critical: critical conditions
 *    3 Error: error conditions
 *    4 Warning: warning conditions
 *    5 Notice: normal but significant condition (default value)
 *    6 Informational: informational messages
 *    7 Debug: debug-level messages
 */
class Logger {

	/** @var int */
	const SEVERITY_EMERGENCY = 0;

	/** @var int */
	const SEVERITY_ALERT = 1;

	/** @var int */
	const SEVERITY_CRITICAL = 2;

	/** @var int */
	const SEVERITY_ERROR = 3;

	/** @var int */
	const SEVERITY_WARNING = 4;

	/** @var int */
	const SEVERITY_NOTICE = 5;

	/** @var int */
	const SEVERITY_INFORMATIONAL = 6;

	/** @var int */
	const SEVERITY_DEBUG = 7;

	/** @var int */
	const FACILITY_KERNEL = 0;

	/** @var int */
	const FACILITY_USER = 1;

	/** @var int */
	const FACILITY_MAIL = 2;

	/** @var int */
	const FACILITY_SYSTEM = 3;

	/** @var int */
	const FACILITY_SECURITY0 = 4;

	/** @var int */
	const FACILITY_SECURITY = 4;

	/** @var int */
	const FACILITY_SYSLOG = 5;

	/** @var int */
	const FACILITY_PRINTER = 6;

	/** @var int */
	const FACILITY_NEWS = 7;

	/** @var int */
	const FACILITY_UUCP = 8;

	/** @var int */
	const FACILITY_CLOCK0 = 9;

	/** @var int */
	const FACILITY_CLOCK = 9;

	/** @var int */
	const FACILITY_SECURITY1 = 10;

	/** @var int */
	const FACILITY_FTP = 11;

	/** @var int */
	const FACILITY_NTP = 12;

	/** @var int */
	const FACILITY_AUDIT = 13;

	/** @var int */
	const FACILITY_ALERT = 14;

	/** @var int */
	const FACILITY_CLOCK1 = 15;

	/** @var int */
	const FACILITY_LOCAL0 = 16;

	/** @var int */
	const FACILITY_LOCAL1 = 17;

	/** @var int */
	const FACILITY_LOCAL2 = 18;

	/** @var int */
	const FACILITY_LOCAL3 = 19;

	/** @var int */
	const FACILITY_LOCAL4 = 20;

	/** @var int */
	const FACILITY_LOCAL5 = 21;

	/** @var int */
	const FACILITY_LOCAL6 = 22;

	/** @var int */
	const FACILITY_LOCAL7 = 23;

	/** @var int */
	private $facility;

	/** @var string */
	private $hostname;

	/** @var string */
	private $appName;

	/** @var string */
	private $logLevel;

	/** @var array */
	private $adapters = [];

	/**
	 * Logger constructor.
	 *
	 * @param int $facility
	 * @param string $appName
	 * @throws \UnexpectedValueException on incorrect $facility / $appName
	 */
	public function __construct($facility=self::FACILITY_LOCAL0, $appName='php') {
		$this->setFacility($facility);

		$this->hostname = gethostname();

		$this->appName = $appName;
		$this->logLevel = isset($_ENV['LOGGER_LEVEL']) ? $this->parseLogLevelVal(['LOGGER_LEVEL']) : self::SEVERITY_DEBUG;
	}

	/**
	 * @param string $logLevel
	 * @return int
	 */
	protected function parseLogLevelVal($logLevel) {
		$severity = null;

		switch ($logLevel) {
			case 'emerg': {
				$severity = self::SEVERITY_EMERGENCY;
				break;
			}
			case 'alert': {
				$severity = self::SEVERITY_ALERT;
				break;
			}
			case 'critical': {
				$severity = self::SEVERITY_CRITICAL;
				break;
			}
			case 'error': {
				$severity = self::SEVERITY_ERROR;
				break;
			}
			case 'warning': {
				$severity = self::SEVERITY_WARNING;
				break;
			}
			case 'notice': {
				$severity = self::SEVERITY_NOTICE;
				break;
			}
			case 'info': {
				$severity = self::SEVERITY_INFORMATIONAL;
				break;
			}
			case 'debug': {
				$severity = self::SEVERITY_DEBUG;
				break;
			}
		}
		
		if ($severity === null) {
			throw new \InvalidArgumentException('Unexpected logger level value');
		}

		return $severity;
	}

	/**
	 * @param string $logLevel
	 * @return $this
	 */
	public function setLogLevel($logLevel) {
		$this->logLevel = $this->parseLogLevelVal($logLevel);

		return $this;
	}

	/**
	 * @param int $facility
	 * @return $this
	 * @throws \UnexpectedValueException on incorrect $facility
	 */
	public function setFacility($facility) {
		if ($facility < self::FACILITY_KERNEL || $facility > self::FACILITY_LOCAL7) {
			throw new \UnexpectedValueException(sprintf(
				'Invalid facility value, must be from %d to %d.',
				self::FACILITY_KERNEL,
				self::FACILITY_LOCAL7
			));
		}

		$this->facility = $facility;

		return $this;
	}

	/**
	 * @param string $val
	 * @return $this
	 * @throws \UnexpectedValueException on incorrect $hostname
	 */
	public function setHostname($hostname) {
		if (!$this->isHostnameValid($hostname)) {
			throw new \UnexpectedValueException('Hostname should be either IP or correct FQDN and no longer than 255 chars.');
		}
		
		$this->hostname = $hostname;

		return $this;
	}

	/**
	 * @param string $appName
	 * @return $this
	 * @throws \UnexpectedValueException on incorrect $appName
	 */
	public function setAppName($appName) {
		if (!preg_match('/^[a-z0-9_.-]{1,48}$/i', $appName)) {
			throw new \UnexpectedValueException('Incorrect app name, it should match: /^[a-z0-9_.-]{1,48}$/i.');
		}
		$this->appName = $appName;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getLogLevel() {
		return $this->logLevel;
	}
	
	/**
	 * @return int
	 */
	public function getFacility() {
		return $this->facility;
	}

	/**
	 * @return string
	 */
	public function getHostname() {
		return $this->hostname;
	}

	/**
	 * @return string
	 */
	public function getAppName() {
		return $this->appName;
	}

	/**
	 * Check if hostname is correct FQDN or IP.
	 *
	 * @param $hostname
	 * @return bool
	 */
	protected function isHostnameValid($hostname) {
		$fqdnCheck = true;

		$fqdnCheck = $fqdnCheck && strlen($hostname) <= 255;
		$fqdnCheck = $fqdnCheck && $this->isValidFQDN($hostname);
		$fqdnCheck = $fqdnCheck || filter_var($hostname, FILTER_VALIDATE_IP);

		return $fqdnCheck;
	}

	/**
	 * Checks if valid FQDN
	 *
	 * @param $FQDN
	 * @return bool
	 */
	function isValidFQDN($FQDN) {
		return (!empty($FQDN) && preg_match(
				'/(?=^.{1,254}$)(^(?:(?!\d|-)[a-z0-9\-]{1,63}(?<!-)\.)+(?:[a-z]{2,})$)/i',
				$FQDN
			) > 0);
	}

	/**
	 * Clear all registered adapters.
	 */
	public function clearAdapters() {
		$this->adapters = [];
	}

	/**
	 * Get current adapters list.
	 *
	 * @return array
	 */
	public function getAdapters() {
		return $this->adapters;
	}

	/**
	 * Register new adapter.
	 *
	 * @param AdapterInterface $adapter
	 */
	public function addAdapter(AdapterInterface $adapter) {
		array_push($this->adapters, $adapter);
	}

	/**
	 * @param string $message
	 */
	public function log($message) {
		$this->_log(self::SEVERITY_DEBUG, $message);
	}

	/**
	 * @param string $message
	 */
	public function emerg($message) {
		$this->_log(self::SEVERITY_EMERGENCY, $message);
	}

	/**
	 * @param string $message
	 */
	public function alert($message) {
		$this->_log(self::SEVERITY_ALERT, $message);
	}

	/**
	 * @param string $message
	 */
	public function critical($message) {
		$this->_log(self::SEVERITY_CRITICAL, $message);
	}

	/**
	 * @param string $message
	 */
	public function error($message) {
		$this->_log(self::SEVERITY_CRITICAL, $message);
	}

	/**
	 * @param string $message
	 */
	public function warning($message) {
		$this->_log(self::SEVERITY_WARNING, $message);
	}

	/**
	 * @param string $message
	 */
	public function notice($message) {
		$this->_log(self::SEVERITY_NOTICE, $message);
	}

	/**
	 * @param string $message
	 */
	public function info($message) {
		$this->_log(self::SEVERITY_INFORMATIONAL, $message);
	}

	/**
	 * @param string $message
	 */
	public function debug($message) {
		$this->_log(self::SEVERITY_DEBUG, $message);
	}

	/**
	 * Invokes log on adapters.
	 *
	 * @param int $severity
	 * @param string $message
	 */
	private function _log($severity, $message) {
		if ($severity > $this->logLevel) {
			return;
		}

		$facility = $this->facility;
		$hostname = $this->hostname;
		$appName = $this->appName;

		$now = \DateTime::createFromFormat('U.u', microtime(true));
		$date = $now->format('M j H:m:s.u');
		$date = substr($date, 0, strlen($date) - 3);

		foreach ($this->adapters as $adapter) {
			$adapter->write($severity, $facility, $hostname, $appName, $date, $message);
		}
	}
}
