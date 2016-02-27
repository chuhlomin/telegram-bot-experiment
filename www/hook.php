<?php

define('BASEDIR', __DIR__ . '/..');

include BASEDIR . '/vendor/autoload.php';
include BASEDIR . '/src/bootstrap.php';

// process user state

$telegram->sendChatAction(
    [
        'chat_id' => 4885399,
        'action' => 'typing'
    ]
);

sleep(5); // todo: calculate, avg 200 words per minute

$response = $telegram->sendMessage([
    'chat_id' => 4885399,
    'text' => 'Hello World',
    'reply_markup' => $telegram->replyKeyboardMarkup(
        [
            'keyboard' => [
                ['one', 'two', 'three'],
                ['four']
            ],
            'resize_keyboard' => true,
            'one_time_keyboard' => true,
        ]
    )
]);
