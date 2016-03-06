<?php

namespace tests\models;


use src\models\Sender;

class SenderTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function shouldSendOneMessageWithoutOptions()
    {
        /** @var \Monolog\Logger|\Mockery\Mock $loggerMock */
        $loggerMock = \Mockery::mock('\Monolog\Logger')->shouldIgnoreMissing();

        /** @var \Telegram\Bot\Api|\Mockery\Mock $telegramMock */
        $telegramMock = \Mockery::mock('\Telegram\Bot\Api');
        $telegramMock->shouldReceive('sendChatAction')->once();
        $telegramMock->shouldReceive('replyKeyboardMarkup')->once();
        $telegramMock->shouldReceive('sendMessage')
            ->with(
                [
                    'chat_id' => 1,
                    'text' => 'Hi!',
                    'parse_mode' => 'Markdown',
                    'disable_web_page_preview' => true,
                    'reply_markup' => null
                ]
            )
            ->once();

        /** @var \src\models\UrlReplacer|\Mockery\Mock $urlReplacerMock */
        $urlReplacerMock = \Mockery::mock('\src\models\UrlReplacer');
        $urlReplacerMock->shouldReceive('replaceUrls')->andReturn('Hi!');

        $sender = new Sender($loggerMock, $telegramMock, $urlReplacerMock);

        $sender->sendMessages(1, 2, ['Hi!'], []);
    }
}
