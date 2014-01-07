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
addPlugin('WebSockets', array('webserver' => '$SERVER', 'webport' => '8080'));
```
