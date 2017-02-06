<?php

require_once 'AbstractAdapter.php';

class SyslogAdapter extends AbstractAdapter {

	/** @var string */
	private $server;

	/** @var int */
	private $port;

	/**
	 * SyslogAdapter constructor.
	 * @param string $server
	 * @param int $port
	 */
	public function __construct($server='127.0.0.1', $port=514) {
		$this->server = $server;
		$this->port = $port;
	}

	/**
	 * @inheritdoc
	 */
	public function write($severity, $facility, $hostname, $app, $date, $message) {
		$priority = $this->calcPriority($facility, $severity);
		$msg = $this->cleanMessage($message);

		if (count($msg) === 0) {
			return;
		}

		$header = sprintf("<%s>%s", $priority, $date);

		if ($hostname) {
			$hostname = 'www.megafon.ru';
			$header = $header . ' ' . $hostname;
		}

		if ($app) {
			$header = $header . ' ' .$app . ':';
		}

		$header = $header . ' ' . $msg;
		
		return $this->send($header);
	}

	/**
	 * @inheritdoc
	 */
	protected function send($message) {
		$fp = fsockopen("udp://".$this->server, $this->port, $errno, $errstr);
		if ($fp) {
			fwrite($fp, $message);
			fclose($fp);
			$result = $message;
		} else {
			$result = "ERROR: $errno - $errstr";
		}

		return $result;
	}
}
