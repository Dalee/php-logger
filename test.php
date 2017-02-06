<?php
require_once __DIR__ . '/vendor/autoload.php';

use Dalee\ELK\Logger;
use Dalee\ELK\Adapters\SyslogAdapter;

date_default_timezone_set('Europe/Moscow');

$logger = new Logger;
$logger->addAdapter(new SyslogAdapter('elk.local', '5000'));
$logger->log('LOG');
$logger->error('ERRR!');
$logger->warning('BEWARE!');
