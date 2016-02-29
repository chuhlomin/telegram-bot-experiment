<?php

date_default_timezone_set('America/New_York');

$parser = new \Symfony\Component\Yaml\Parser();

$configFileContent = file_get_contents(BASEDIR . '/config/default.yaml');

$config = $parser->parse($configFileContent);

$monolog = new \Monolog\Logger('telegram_bot');
$monolog->pushHandler(
    new \Monolog\Handler\StreamHandler('/var/log/telegram-bot-experiment/hook.log', \Monolog\Logger::INFO)
);

$memcached = new Memcached();
$memcached->addServer('localhost', 11211);

$telegram = new \Telegram\Bot\Api($config['bot_token']);

$botan = new \src\models\Botan($config['botan_token']);
$urlReplacer = new \src\models\UrlReplacer();
