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
            'user_id:%s state:%s message:%s',
            $userID,
            $stateID,
            $message
        )
    );

    $state = new \src\models\State($script, $stateID);

    $messages = [];
    $shouldGetBotMessages = true;

    if ($script['start'] !== $stateID) { // not first state
        $monolog->addDebug(sprintf('This is not start state ID, so, trying to find next state by message'));
        try {
            $stateID = $state->getFollowupByMessage($message);
            $monolog->addDebug(sprintf('Next state is: %s', $stateID));
            $state = new \src\models\State($script, $stateID);
        } catch (\src\exceptions\UserMessageNotFoundInAvailableOptions $e) {
            $monolog->addDebug(sprintf('Cold not get next state from state \'%s\' for message: %s ', $stateID, $message));
            $messages[] = 'Sorry, did not catch what just you said. Could you pick from provided options?';
            $shouldGetBotMessages = false;
        }
    }

    if ($shouldGetBotMessages) {
        $botMessage = $state->getBotMessage();
        $monolog->addDebug(sprintf('Adding new bot message to pool: %s', $botMessage));
        $messages[] = $botMessage;

        while (($nextStateID = $state->getFollowup()) !== null) {
            $monolog->addDebug(sprintf('Next state ID: %s', $nextStateID));
            $state = new \src\models\State($script, $nextStateID);
            $stateID = $nextStateID;
            $botMessage = $state->getBotMessage();
            $monolog->addDebug(sprintf('Adding new bot message to pool: %s', $botMessage));
            $messages[] = $botMessage;
        }
    }

    $monolog->addDebug(sprintf('Updating user state: %s', $stateID));
    $memcached->set('state:' . $userID, $stateID);

    $monolog->addDebug(sprintf('Trying to get options for state: %s', $stateID));
    $options = $state->getResponseOptions();
    $monolog->addDebug(sprintf('User options: %s', json_encode($options)));

    $lastMessage = end($messages);
    $monolog->addDebug(sprintf('Last message should be: %s', $lastMessage));

    foreach ($messages as $message) {
        $monolog->addDebug(sprintf('Current message: %s', $message));
        $isLastMessage = ($message === $lastMessage);
        $monolog->addDebug(sprintf('Is last: %s', var_export($isLastMessage, true)));
        $monolog->addDebug(sprintf('Options are: %s', json_encode($options)));

        $response = $telegram->sendChatAction(
            [
                'chat_id' => $chatID,
                'action' => 'typing'
            ]
        );

        // avg person write 250 words in a minute -> 250 words in a 60 sec -> 4.16666667 in sec
        // but it's too slow for UX, so 2 sec
        sleep(str_word_count($message) / 2);

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
                'text' => $message,
                'reply_markup' => $telegram->replyKeyboardHide(
                    [
                        'hide_keyboard' => true
                    ]
                )
            ]);
        }
    }
} catch (\Exception $e) {
    $message = 'Error...';
    $monolog->addError(
        sprintf(
            '%s [%s:%s]: %s',
            get_class($e),
            $e->getFile(),
            $e->getLine(),
            $e->getMessage()
        )
    );
}
