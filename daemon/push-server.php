<?php

// We need to redefine the autoloader using spl_autoload_register
// Otherwise, composer will overwrite it with its own and break everything
// This should probably be changed in GnuSocial itself.
// From $SNROOT/lib/framework.php
spl_autoload_register(function ($cls)
{
    if (file_exists(INSTALLDIR.'/classes/' . $cls . '.php')) {
        require_once(INSTALLDIR.'/classes/' . $cls . '.php');
    } else if (file_exists(INSTALLDIR.'/lib/' . strtolower($cls) . '.php')) {
        require_once(INSTALLDIR.'/lib/' . strtolower($cls) . '.php');
    } else if (mb_substr($cls, -6) == 'Action' &&
               file_exists(INSTALLDIR.'/actions/' . strtolower(mb_substr($cls, 0, -6)) . '.php')) {
        require_once(INSTALLDIR.'/actions/' . strtolower(mb_substr($cls, 0, -6)) . '.php');
    } else if ($cls == 'OAuthRequest') {
        require_once('OAuth.php');
    } else {
        Event::handle('Autoload', array(&$cls));
    }
}, true, true);

// From composer
require __DIR__ . '/../vendor/autoload.php';

// From $SNROOT/plugins/TwitterBridge/twitterstatusfetcher.php
define('INSTALLDIR', realpath(dirname(__FILE__) . '/../../..'));
require_once INSTALLDIR . '/scripts/commandline.inc';
require_once INSTALLDIR . '/lib/common.php';
require_once INSTALLDIR . '/lib/daemon.php';

class PushServer extends Daemon
{
    function __construct($daemonize) {
       parent::__construct($daemonize);
    }

    function name() {
        return ('pushserver.' . $this->_id) ;
    }

    function run() {
        $loop   = React\EventLoop\Factory::create();
        $pusher = new wsRealtime\Pusher;

        $controlserver = common_config('websockets', 'controlserver');
        $controlport   = common_config('websockets', 'controlport');
        $webserver     = common_config('websockets', 'webserver');
        $webport       = common_config('websockets', 'webport');

        // Listen for the web server to make a ZeroMQ push after an ajax request
        $context = new React\ZMQ\Context($loop);
        $pull = $context->getSocket(ZMQ::SOCKET_PULL);
        $pull->bind('tcp://' . $controlserver . ':' . $controlport);
        $pull->on('message', array($pusher, 'onNewNotice'));

        // Set up our WebSocket server for clients wanting real-time updates
        $webSock = new React\Socket\Server($loop);
        $webSock->listen($webport, $webserver);
        $webServer = new Ratchet\Server\IoServer(
                new Ratchet\Http\HttpServer(
                    new Ratchet\WebSocket\WsServer(
                        new Ratchet\Wamp\WampServer(
                            $pusher
                            )
                        )
                    ),
                $webSock
                );

        $loop->run();
    }
}

$server = new PushServer(true);
$server->runOnce();
