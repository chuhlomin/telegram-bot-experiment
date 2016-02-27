<?php

define('BASEDIR', __DIR__ . '/..');

include BASEDIR . '/vendor/autoload.php';
include BASEDIR . '/src/bootstrap.php';


$response = $telegram->removeWebhook();

print_r($response);
