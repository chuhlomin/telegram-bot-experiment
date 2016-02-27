<?php

define('BASEDIR', __DIR__ . '/..');

include BASEDIR . '/vendor/autoload.php';
include BASEDIR . '/src/bootstrap.php';

try {
    $update = $telegram->getWebhookUpdates();
    $chatID = $update['message']['chat']['id'];

    $monolog->addInfo(
        sprintf(
            '%s: %s',
            $update['message']['from']['username'],
            $update['message']['text']
        )
    );

    // process user state
    
    $message = 'Hey, ' . $update['message']['chat']['first_name'];
    $options = [
        ['Hey']
    ];

    $response = $telegram->sendMessage([
        'chat_id' => $chatID,
        'text' => $message,
        'reply_markup' => $telegram->replyKeyboardMarkup(
            [
                'keyboard' => $options,
                'resize_keyboard' => false,
                'one_time_keyboard' => true,
            ]
        )
    ]);
} catch (\Exception $e) {
    $message = 'Error...';
    $monolog->addError($e->getMessage());
}
