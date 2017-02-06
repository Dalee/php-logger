<?php

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
	private $facility;

	/** @var string */
	private $hostname;

	/** @var string */
	private $app;

	/** @var array */
	private $adapters = [];

	/**
	 * Logger constructor.
	 *
	 * @param int $facility
	 * @param string $app
	 * @throws Exception on incorrect $hostname / $app
	 */
	public function __construct($facility=16, $app="php") {
		// just to be sure!
		if ($facility < 0) {
			$facility =  0;
		}
		if ($facility > 23) {
			$facility = 23;
		}

		$this->facility = $facility;

		$host = gethostname();
		$this->hostname = $host && $this->hostnameCheck($host) ? $host : 'webserver';

		if ($app !== "php") {
			$this->setApp($app);
		}
	}

	/**
	 * @param int $val
	 * @return $this
	 */
	public function setFacility($val) {
		$facility = $val;

		if ($facility < 0) {
			$facility =  0;
		}
		if ($facility > 23) {
			$facility = 23;
		}
		
		$this->facility = $facility;

		return $this;
	}

	/**
	 * @param string $val
	 * @return $this
	 * @throws Exception on incorrect $hostname
	 */
	public function setHostname($val) {
		if (!$this->hostnameCheck($val)) {
			throw new Exception('Hostname should be either IP or correct FQDN and no longer than 255 chars');
		}
		
		$this->hostname = $val;

		return $this;
	}

	/**
	 * @param string $val
	 * @return $this
	 * @throws Exception on incorrect $app
	 */
	public function setApp($val) {
		if (!preg_match('/^[a-z0-9_.-]{1,48}$/i', $val)) {
			throw new Exception('Incorrect app name, it should match: /^[a-z0-9_.-]{1,48}$/i');
		}
		$this->app = $val;

		return $this;
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
	public function getApp() {
		return $this->app;
	}

	/**
	 * Check if hostname is correct FQDN or IP.
	 *
	 * @param $hostname
	 * @return bool
	 */
	protected function hostnameCheck($hostname) {
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
		return (!empty($FQDN) && preg_match('/(?=^.{1,254}$)(^(?:(?!\d|-)[a-z0-9\-]{1,63}(?<!-)\.)+(?:[a-z]{2,})$)/i', $FQDN) > 0);
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
	 * @param AbstractAdapter $adapter
	 */
	public function addAdapter(AbstractAdapter $adapter) {
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
		$facility = $this->facility;
		$hostname = $this->hostname;
		$app = $this->app;

		$now = DateTime::createFromFormat('U.u', microtime(true));
		$date = $now->format("M j H:m:s.u");
		$date = substr($date, 0, strlen($date) - 3);

		foreach ($this->adapters as $adapter) {
			$adapter->write($severity, $facility, $hostname, $app, $date, $message);
		}
	}
}
