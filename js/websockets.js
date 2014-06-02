(function () {
    'use strict';
    /*global ab: false, RealtimeUpdate: false */

    window.wsRealtime = {
        init: function (server, port, timeline) {
            var conn = new ab.Session(
                    'ws://' + server + ':' + port,
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

