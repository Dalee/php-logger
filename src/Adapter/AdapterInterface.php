<?php

namespace Dalee\Logger\Adapter;

/**
 * Interface AdapterInterface.
 * 
 * @package Dalee\Logger\Adapter
 */
interface AdapterInterface {
	/**
	 * Forms and sends Syslog message string.
	 *
	 * @param int $severity
	 * @param int $facility
	 * @param string $hostname
	 * @param string $appName
	 * @param string $date
	 * @param string $message
	 * @return string
	 */
	public function write($severity, $facility, $hostname, $appName, $date, $message);

	/**
	 * Sends message.
	 *
	 * @param string $message
	 * @return string
	 */
	public function send($message);
}
