<?php

require_once 'AbstractAdapter.php';

class SyslogAdapter extends AbstractAdapter {
	private $server;
	private $port;
	
	function __construct($server='127.0.0.1', $port=514) {
		$this->server = $server;
		$this->port = $port;
	}

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