# Dalee php-logger

[![Build Status](https://travis-ci.org/Dalee/php-logger.svg?branch=master)](https://travis-ci.org/Dalee/php-logger)

PHP syslog device implementation, according to the RFC 3164 rules.

Currently WIP.

## Usage

WIP

### Standalone

```
use Dalee\ELK\Logger;
use Dalee\ELK\Adapters\SyslogAdapter;

$logger = new Logger;
$logger->addAdapter(new SyslogAdapter('example.com', '514'));

$logger->log('Will send UDP syslog packet');
```