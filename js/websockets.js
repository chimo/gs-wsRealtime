(function () {
    'use strict';
    /*global ab: false, RealtimeUpdate: false */

    window.wsRealtime = {
        init: function (server, httpPort, httpsPort, timeline) {
            var isHTTPS = (document.location.protocol === 'https:'),
                protocol = isHTTPS ? 'wss' : 'ws',
                port = isHTTPS ? httpsPort : httpPort,
                conn = new ab.Session(
                    protocol + '://' + server + ':' + port,
                    function () { // Connect
                        conn.subscribe(timeline, function (undefined, data) {
                            RealtimeUpdate.receive(data);
                        });
                    },
                    function () { // Connection closed
                        console.warn('WebSocket connection closed');
                    },
                    { // Additional parameters, we're ignoring the WAMP sub-protocol for older browsers
                        'skipSubprotocolCheck': true
                    }
                );
        }
    };
}());

