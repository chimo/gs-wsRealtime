<?php
/**
 * StatusNet, the distributed open-source microblogging tool
 *
 * Plugin to do "real time" updates using Websockets
 *
 * PHP version 5
 *
 * LICENCE: This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @category  Plugin
 * @package   StatusNet
 * @author    Stephane Berube <chimo@chromic.org>
 * @copyright Stephane Berube
 * @license   http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License version 3.0
 * @link      http://github.com/chimo/wsRealtime
 */

if (!defined('STATUSNET') && !defined('LACONICA') && !defined('GNUSOCIAL')) {
    exit(1);
}

require_once INSTALLDIR.'/plugins/Realtime/RealtimePlugin.php';
require_once INSTALLDIR.'/plugins/Realtime/classes/Realtime_channel.php';

class WebSocketsPlugin extends RealtimePlugin
{
    public $webserver     = null;
    public $webport       = null;
    public $controlserver = null;
    public $controlport   = null;
    protected $_socket    = null;

    function __construct($webserver=null, $webport=8080, $controlport=5555, $controlserver=null)
    {
        global $config;

        $this->webserver     = (empty($webserver)) ? $config['site']['server'] : $webserver;
        $this->webport       = $webport;
        $this->controlserver = (empty($controlserver)) ? 'locahost' : $controlserver;
        $this->controlport   = $controlport;
		
        parent::__construct();
    }

    /**
     * Pull settings from config file/database if set.
     */
    function initialize()
    {
        $settings = array('webserver',
            'webport',
            'controlserver',
            'controlport');

        foreach ($settings as $name) {
            $val = common_config('websockets', $name);
            if ($val !== false) {
                $this->$name = $val;
            }
        }

        return parent::initialize();
    }

    function _getScripts()
    {
        $scripts = parent::_getScripts();

        $scripts[] = $this->path('/js/lib/when.js');
        $scripts[] = $this->path('/js/lib/autobahn.min.js');
        $scripts[] = $this->path('/js/websockets.js');

        return $scripts;
    }

    function _updateInitialize($timeline, $user_id)
    {
        $script = parent::_updateInitialize($timeline, $user_id);
        $ours = sprintf("wsRealtime.init(%s, %s, %s);",
        				json_encode($this->webserver),
        				json_encode($this->webport),
        				json_encode($timeline));
        return $script . " " . $ours;
    }

    function _connect()
    {
        $context = new ZMQContext();
        $socket = $context->getSocket(ZMQ::SOCKET_PUSH, 'my pusher');
        $socket->connect("tcp://" . $this->controlserver . ":" . $this->controlport);

        $this->_socket = $socket;

        // TODO: Error handling.
    }

    function _publish($channel, $message)
    {
        $message['channel'] = $channel;
        $message = json_encode($message);
        $message = addslashes($message);

        $this->_socket->send($message); 
    }

    function _disconnect()
    {
/*        if (!$this->persistent) {
            $cnt = fwrite($this->_socket, "QUIT\n");
            @fclose($this->_socket);
        } */
    }

    function _pathToChannel($path)
    {
/*        if (!empty($this->channelbase)) {
            array_unshift($path, $this->channelbase);
        } */
        return implode('-', $path);
    }

    function onGetValidDaemons(&$daemons) {
        $daemons[] = INSTALLDIR . '/plugins/WebSockets/daemon/pushserver.php';
    }

    function onPluginVersion(&$versions)
    {
        $versions[] = array('name' => 'Websockets',
                            'version' => defined('STATUSNET_VERSION') ? STATUSNET_VERSION : GNUSOCIAL_VERSION,
                            'author' => 'Stephane Berube',
                            'homepage' => 'http://github.com/chimo/wsRealtime',
                            'rawdescription' =>
                            // TRANS: Plugin description.
                            _m('Plugin to do "real time" updates using Websockets.'));
        return true;
    }
}
