<?php

define('BASEDIR', __DIR__ . '/..');

include BASEDIR . '/vendor/autoload.php';
include BASEDIR . '/src/bootstrap.php';


$response = $telegram->setWebhook(
    [
        'url' => 'bla-bla',
        'certificate' => '/etc/nginx/ssl/bot.pem'
    ]
);

print_r($response);
