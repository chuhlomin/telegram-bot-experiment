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

            // avg person write 250 words in a minute -> 250 words in a 60 sec -> 4.16666667 in sec
            // but it's too slow for UX, so 2 sec
            $typingType = min(str_word_count($message) / 2, 3);

            if ($typingType > 1) { // with less amount of seconds you will not notice that "typing" action
                $this->telegram->sendChatAction(
                    [
                        'chat_id' => $chatID,
                        'action' => 'typing'
                    ]
                );
                sleep($typingType);
            }

            $message = $this->urlReplacer->replaceUrls($message, $userID);

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
