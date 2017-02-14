<?php

namespace Dalee\Logger\Adapter;

/**
 * Class SyslogAdapter
 * 
 * @package Dalee\Logger\Adapter
 */
class SyslogAdapter extends AbstractAdapter {

	/** @var string */
	private $server;

	/** @var int */
	private $port;

	/**
	 * SyslogAdapter constructor.
	 * 
	 * @param string $server
	 * @param int $port
	 */
	public function __construct($server = '127.0.0.1', $port = 514) {
		$this->server = $server;
		$this->port = $port;
	}

	/**
	 * @inheritdoc
	 */
	public function write($severity, $facility, $hostname, $appName, $date, $message) {
		$priority = $this->calcPriority($facility, $severity);
		$msg = $this->cleanMessage($message);

		if (!strlen($msg)) {
			return;
		}

		$header = sprintf('<%s>%s', $priority, $date);

		if ($hostname) {
			$header = $header . ' ' . $hostname;
		}

		if ($appName) {
			$header = $header . ' ' .$appName . ':';
		}

		$header = $header . ' ' . $msg;

		return $this->send($header);
	}

	/**
	 * @inheritdoc
	 */
	public function send($message) {
		$fp = fsockopen('udp://' . $this->server, $this->port, $errno, $errstr);
		if ($fp) {
			fwrite($fp, $message);
			fclose($fp);
			$result = $message;
		} else {
			$result = "ERROR: $errno - $errstr";
		}

		return $result;
	}

	/**
	 * Calculate Syslog message priority.
	 * @link https://tools.ietf.org/html/rfc5424#section-6.2.1
	 *
	 * @param int $facility
	 * @param int $severity
	 * @return int
	 */
	protected function calcPriority($facility, $severity) {
		return ($facility * 8 + $severity);
	}

	/**
	 * Format message.
	 * @link https://tools.ietf.org/html/rfc5424#section-6.4
	 *
	 * @param string $message
	 * @return string
	 */
	protected function cleanMessage($message) {
		return filter_var($message, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH);
	}
}
