<?php
/*
 * THIS IS JUST PROOF OF CONCEPT
 * Do not carp much...
 */

define('BASEDIR', __DIR__ . '/..');

include BASEDIR . '/vendor/autoload.php';
include BASEDIR . '/src/bootstrap.php';

try {
    $update = $telegram->getWebhookUpdates();

    $botan->track($update['message'], 'WebhookUpdate');

    $chatID = $update['message']['chat']['id'];
    $message = $update['message']['text'];
    $userID = $update['message']['from']['id']; // get user id

    $script = json_decode(file_get_contents(BASEDIR . '/config/script.json'), true);
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

    $sender->sendMessages($chatID, $userID, $message, $options);
    
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
