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
* Add the following to `$SNROOT/config.php` (replace $SERVER with your hostname):

```php
$config['websockets']['webserver'] = '$SERVER';       // Your SN/GS hostname
$config['websockets']['webport'] = '8080';            // webport to use over HTTP
$config['websockets']['sslport'] = '8081';            // webport to use over HTTPS
$config['websockets']['controlserver'] = '127.0.0.1'; // Server where the daemon is running
$config['websockets']['controlport'] = '5555';        // Port on which the daemon is running
addPlugin('WebSockets');
```

## HTTPS / SSL / TLS

Ratchet doesn't support SSL. One work-around is to use nginx to proxy those requests.  
Something based on the following nginx config might work.  
Replace $SERVER with your SN / GS hostname.  
Make sure to point to your SSL cert/key.  

```
upstream websocket {
        server $SERVER:8080;
}

server {
    server_name $SERVER;

    listen 8081 ssl;
    ssl_certificate /PATH/TO/CERT.crt;
    ssl_certificate_key /PATH/TO/CERTKEY.key;

    access_log /var/log/wss-access.log;
    error_log /var/log/wss-error.log;

    location / {
                proxy_pass http://websocket;
                proxy_http_version 1.1;
                proxy_set_header Upgrade $http_upgrade;
                proxy_set_header Connection "upgrade";
                proxy_set_header Host $host;

                proxy_set_header X-Real-IP $remote_addr;
                proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
                proxy_set_header X-Forwarded-Proto https;
                proxy_redirect off;
        }
}
```

Another work-around is to use stunnel. I haven't looked into this yet.
