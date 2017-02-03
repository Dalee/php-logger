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
	private $facility;

	/** @var int */
	private $severity;

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
	 * @param int $severity
	 * @param string $hostname
	 * @param string $app
	 * @throws Exception on incorrect $hostname / $app
	 */
	function __construct($facility=1, $severity=7, $hostname="", $app="") {
		$this->facility = $facility;
		$this->severity = $severity;
		$this->hostname = $hostname;
		$this->app = $app;

		if ($this->hostname == "") {
			$host = gethostname();

			$this->hostname = $host && $this->hostnameCheck($host) ? $host : 'webserver';
		} elseif (!$this->hostnameCheck($this->hostname)) {
			throw new Exception('Hostname should be either IP or correct FQDN and no longer than 255 chars');
		}

		if ($this->app == "") {
			$this->app = "php";
		} elseif (!preg_match('/^[a-z0-9_.-]{1,48}$/i' , $this->app)) {
			throw new Exception('Incorrect app name, it should match: /^[a-z0-9_.-]{1,48}$/i');
		}
	}

	/**
	 * @param int $val
	 * @return $this
	 */
	public function setFacility($val) {
		$this->facility = $val;

		return $this;
	}

	/**
	 * @param string $val
	 * @return $this
	 */
	public function setHostname($val) {
		$this->hostname = $val;

		return $this;
	}

	/**
	 * @param string $val
	 * @return $this
	 */
	public function setApp($val) {
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

		$fqdnCheck = fqdnCheck && strlen($hostname) <= 255;
		$fqdnCheck = fqdnCheck && $this->isValidFQDN($hostname);
		$fqdnCheck = fqdnCheck || filter_var($hostname, FILTER_VALIDATE_IP);
		
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
		$this->_log(7, $message);
	}

	/**
	 * @param string $message
	 */
	public function emerg($message) {
		$this->_log(0, $message);
	}

	/**
	 * @param string $message
	 */
	public function alert($message) {
		$this->_log(1, $message);
	}

	/**
	 * @param string $message
	 */
	public function critical($message) {
		$this->_log(2, $message);
	}

	/**
	 * @param string $message
	 */
	public function error($message) {
		$this->_log(3, $message);
	}

	/**
	 * @param string $message
	 */
	public function warning($message) {
		$this->_log(4, $message);
	}

	/**
	 * @param string $message
	 */
	public function notice($message) {
		$this->_log(5, $message);
	}

	/**
	 * @param string $message
	 */
	public function info($message) {
		$this->_log(6, $message);
	}

	/**
	 * @param string $message
	 */
	public function debug($message) {
		$this->_log(7, $message);
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
		$date = date('M j H:m:s');

		foreach ($this->adapters as $adapter) {
			$adapter->write($severity, $facility, $hostname, $app, $date, $message);
		}
	}
}
