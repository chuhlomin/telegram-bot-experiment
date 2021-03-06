<?php

namespace src\models;

use Monolog\Logger;
use Telegram\Bot\Api;

class Sender
{
    /** @var Logger */
    private $logger;

    /** @var Api */
    private $telegram;

    /** @var UrlReplacer */
    private $urlReplacer;

    public function __construct(Logger $logger, Api $telegram, UrlReplacer $urlReplacer)
    {
        $this->logger = $logger;
        $this->telegram = $telegram;
        $this->urlReplacer = $urlReplacer;
    }

    public function sendMessages($chatID, $userID, array $messages, array $options)
    {
        $lastMessage = end($messages);
        $this->logger->addDebug(sprintf('Last message should be: %s', $lastMessage));

        foreach ($messages as $message) {
            $this->logger->addDebug(sprintf('Current message: %s', $message));
            $isLastMessage = ($message === $lastMessage);
            $this->logger->addDebug(sprintf('Is last: %s', var_export($isLastMessage, true)));
            $this->logger->addDebug(sprintf('Options are: %s', json_encode($options)));

            // do not delay, network can be slow enough itself :-)
            // yes, we loosing imitation of typing...

            $message = $this->urlReplacer->replaceUrls($message, $userID);

            $this->logger->addInfo(
                'OUT',
                [
                    'channel' => 'telegram',
                    'user_id' => $userID,
                    'message' => $message
                ]
            );

            if ($isLastMessage) {
                $this->telegram->sendMessage([
                    'chat_id' => $chatID,
                    'text' => $message,
                    'parse_mode' => 'Markdown',
                    'disable_web_page_preview' => true,
                    'reply_markup' => $this->telegram->replyKeyboardMarkup(
                        [
                            'keyboard' => $options,
                            'resize_keyboard' => true,
                            'one_time_keyboard' => false
                        ]
                    )
                ]);
            } else {
                $this->telegram->sendMessage([
                    'chat_id' => $chatID,
                    'text' => $message,
                    'parse_mode' => 'Markdown',
                    'disable_web_page_preview' => true
                ]);
            }
        }
    }
}
