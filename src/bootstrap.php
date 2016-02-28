<?php

$parser = new \Symfony\Component\Yaml\Parser();

$configFileContent = file_get_contents(BASEDIR . '/config/default.yaml');

$config = $parser->parse($configFileContent);

$monolog = new \Monolog\Logger('telegram_bot');
$monolog->pushHandler(new \Monolog\Handler\StreamHandler('/var/log/telegram-bot-experiment/hook.log'));

$memcached = new Memcached();
$memcached->addServer('localhost', 11211);

$telegram = new \Telegram\Bot\Api($config['bot_token']);
