# Dalee php-logger

[![Build Status](https://travis-ci.org/Dalee/php-logger.svg?branch=master)](https://travis-ci.org/Dalee/php-logger)
[![codecov](https://codecov.io/gh/Dalee/php-logger/branch/master/graph/badge.svg)](https://codecov.io/gh/Dalee/php-logger)

PHP syslog device implementation, according to the [RFC 3164 rules](https://tools.ietf.org/html/rfc5424).

Currently WIP.

## Usage

### Configuration

Global configuration options:

 * `facility` - facility, default value: `16` (`LOCAL0`) (syslog parameter)
 * `hostname` - hostname (syslog parameter), default value: `gethostname()`
 * `appName` - application name (syslog parameter), default value: 'php'
 * `logger_level` - output event level, default value is 'debug', possible values are:
   * `emerg`
   * `alert`
   * `critical`
   * `error`
   * `warning`
   * `notice`
   * `info`
   * `debug`

> `logger_level` also can be set via environment variable `LOGGER_LEVEL`

Log methods:

`void log($message: string)` - Debug: debug-level messages (severity = 7)

`void emerg($message: string)` - Emergency: system is unusable (severity = 0)

`void alert($message: string)` - Alert: action must be taken immediately (severity = 1)

`void critical($message: string)` - Critical: critical conditions (severity = 2)

`void error($message: string)` - Error: error conditions (severity = 3)

`void warning($message: string)` - Warning: warning conditions (severity = 4)

`void notice($message: string)` - Notice: normal but significant condition (default value) (severity = 5)

`void info($message: string)` - Informational: informational messages (severity = 6)

`void debug($message: string)` - Debug: debug-level messages (severity = 7)

### Adapter configuration

#### Syslog

 * `server` - valid fqdn or ip address of Syslog/Logstash daemon
 * `port` - udp4 port number

Sample output (udp4 packet):
```
<0>2016-11-26 23:23:23.4554 localhost app: hello world
```

### Basic usage

```
use Dalee\Logger\Logger;
use Dalee\Logger\Adapter\SyslogAdapter;

$logger = new Logger(1, 'my-app');
$logger->addAdapter(new SyslogAdapter('example.com', '514'));
$logger->log('LOG');
$logger->error('ERRR!');
$logger->warning('BEWARE!');
```
