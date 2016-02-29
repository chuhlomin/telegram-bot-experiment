<?php

namespace tests\models;


use src\models\UrlReplacer;

class UrlReplacerTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function shouldReplaceUrl()
    {
        $replacer = new UrlReplacer();

        /** @var \src\models\Botan|\Mockery\Mock $botanMock */
        $botanMock = \Mockery::mock('\src\models\Botan');

        $botanMock->shouldReceive('shortenUrl')
            ->with('http://yandex.ru', 123456789)
            ->andReturn('http://some-short-link.com');

        $result = $replacer->replaceUrls(
            $botanMock,
            'Hey, here is some link: [link](http://yandex.ru).',
            123456789
        );

        self::assertEquals(
            'Hey, here is some link: [link](http://some-short-link.com).',
            $result
        );
    }

    /** @test */
    public function shouldReplaceUrls()
    {
        $replacer = new UrlReplacer();

        /** @var \src\models\Botan|\Mockery\Mock $botanMock */
        $botanMock = \Mockery::mock('\src\models\Botan');

        $botanMock->shouldReceive('shortenUrl')
            ->with('http://yandex.ru', 123456789)
            ->andReturn('http://some-short-link.com');

        $botanMock->shouldReceive('shortenUrl')
            ->with('http://google.com/search?t="123"', 123456789)
            ->andReturn('http://some-short-link-two.com');

        $result = $replacer->replaceUrls(
            $botanMock,
            'Hey, here is some link: [link](http://yandex.ru). ' .
            'And here is [another one](http://google.com/search?t="123"). ' .
            'And first link [one more time](http://yandex.ru).',
            123456789
        );

        self::assertEquals(
            'Hey, here is some link: [link](http://some-short-link.com). ' .
            'And here is [another one](http://some-short-link-two.com). ' .
            'And first link [one more time](http://some-short-link.com).',
            $result
        );
    }

    /** @test */
    public function shouldWorkWithoutUrls()
    {
        $replacer = new UrlReplacer();

        /** @var \src\models\Botan|\Mockery\Mock $botanMock */
        $botanMock = \Mockery::mock('\src\models\Botan');

        $result = $replacer->replaceUrls(
            $botanMock,
            'Hey, how are you?',
            123456789
        );

        self::assertEquals(
            'Hey, how are you?',
            $result
        );
    }
}
