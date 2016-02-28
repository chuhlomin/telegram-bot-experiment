<?php
/*
 * THIS IS JUST PROOF OF CONCEPT
 * Do not carp much...
 */

define('BASEDIR', __DIR__ . '/..');

include BASEDIR . '/vendor/autoload.php';
include BASEDIR . '/src/bootstrap.php';

try {
    $script = json_decode(file_get_contents(BASEDIR . '/config/script.json'), true);
    
    $update = $telegram->getWebhookUpdates();

    $chatID = $update['message']['chat']['id'];
    $message = $update['message']['text'];
    $userID = $update['message']['from']['id']; // get user id

    $stateID = $memcached->get('state:' . $userID) ?: $script['start']; // get current user state

    $monolog->addInfo(
        sprintf(
            'user_id:%s state:%s: %s',
            $userID,
            $stateID,
            $message
        )
    );

    $state = new \src\models\State($script, $stateID);

    $messages = [];
    $stateChanged = false;

    try {
        $stateID = $state->getFollowupByMessage($message);
        $stateChanged = true;
    } catch (\src\exceptions\UserMessageNotFoundInAvailableOptions $e) {
        $messages[] = 'Sorry, did not catch what just you said. Could you pick from provided options?';
    }

    if ($stateChanged) {
        $state = new \src\models\State($script, $stateID);

        $memcached->set('state:' . $userID, $stateID);

        $messages[] = $state->getBotMessage();

        while (($stateID = $state->getFollowup()) !== null) {
            $state = new \src\models\State($script, $stateID);
            $messages[] = $state->getBotMessage();
        }
    }

    $options = $state->getResponseOptions();

    $lastMessage = end($messages);
    foreach ($messages as $message) {
        $isLastMessage = $message === $lastMessage;

        if ($isLastMessage) {
            $response = $telegram->sendMessage([
                'chat_id' => $chatID,
                'text' => $message,
                'reply_markup' => $telegram->replyKeyboardMarkup(
                    [
                        'keyboard' => $options,
                        'resize_keyboard' => true,
                        'one_time_keyboard' => true
                    ]
                )
            ]);
        } else {
            $response = $telegram->sendMessage([
                'chat_id' => $chatID,
                'text' => $message
            ]);

            // 200 words in a minute -> 200 words in a 60 sec -> 3.33333333 in sec
            sleep(str_word_count($message) / 3.33);
        }
    }
} catch (\Exception $e) {
    $message = 'Error...';
    $monolog->addError($e->getMessage());
}
