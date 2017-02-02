<?php

abstract class AbstractAdapter {

	protected function calcPriority($facility, $severity) {
		// just to be sure!
		if ($facility < 0) {$facility =  0;}
		if ($facility > 23) {$facility = 23;}
		if ($severity < 0) {$severity =  0;}
		if ($severity > 7) {$severity =  7;}
		
		return ($facility * 8 + $severity);
	}

	protected function cleanMessage($message) {
		return filter_var($message, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH);
	}
	
	abstract public function write($severity, $facility, $hostname, $app, $date, $message);

	abstract protected function send($message);
}