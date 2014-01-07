wsRealtime
==========

Plugin to do "real time" updates using Websockets

## Requirements

* [Composer](http://getcomposer.org/)
* [ZeroMQ](http://zeromq.org/)
* [ZeroMQ-PHP](http://pecl.php.net/package/zmq)

libevent is also recommended (see the [Ratchet documentation about "deployment"](http://socketo.me/docs/deploy))

## Setup

* Put the files from this repository in `$SNROOT/plugins/WebSockets/`
* Run composer: `composer install`
* Run the WebSockets daemon: `php push-server.php`
* Add the following to `$SNROOT/config.php` (replace $SERVER with your hostname):

```php
$config['websockets']['webserver'] = 'example.org';
$config['websockets']['webport'] = '8080';
$config['websockets']['controlserver'] = '127.0.0.1';
$config['websockets']['controlport'] = '5555';
addPlugin('WebSockets');
```
