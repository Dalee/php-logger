<?php

namespace Dalee\Logger\Adapter;

abstract class AbstractAdapter {

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
