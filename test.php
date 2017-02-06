<?php
require_once __DIR__ . '/vendor/autoload.php';

//use \Logger;

date_default_timezone_set('Europe/Moscow');

//$logger = new Logger;
//$logger->addAdapter(new SyslogAdapter('elk.local', '5000'));
//$logger->log('LOG');
//$logger->error('ERRR!');
//$logger->warning('BEWARE!');

$date = date('M j H:m:s.u');
echo($date);

$now = DateTime::createFromFormat('U.u', microtime(true));
$date = $now->format("M j H:m:s.u");


echo ' | ';
echo substr($date, 0, strlen($date) - 3);