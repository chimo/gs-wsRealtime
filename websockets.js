var wsRealtime = {
    init: function (server, port, timeline) {
console.log('subbing to ' + timeline);
        var conn = new ab.Session(
            'ws://' + server + ':' + port,
            function () { // Connect
                conn.subscribe(timeline, function (path, data) {
                    console.log('New article published to category "' + path + '" : ' + JSON.stringify(data));
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
}

