# Dalee php-logger

[![Build Status](https://travis-ci.org/Dalee/php-logger.svg?branch=master)](https://travis-ci.org/Dalee/php-logger)
[![codecov](https://codecov.io/gh/Dalee/php-logger/branch/master/graph/badge.svg)](https://codecov.io/gh/Dalee/php-logger)

PHP syslog device implementation, according to the RFC 3164 rules.

Currently WIP.

## Usage

WIP

### Basic usage

```
use Dalee\Logger\Logger;
use Dalee\Logger\Adapter\SyslogAdapter;

$logger = new Logger(1, 'my-app');
$logger->addAdapter(new SyslogAdapter('example.com', '5000'));
$logger->log('LOG');
$logger->error('ERRR!');
$logger->warning('BEWARE!');
```
