<?php

declare(strict_types=1);

namespace Webthink\MonologSlack\Test\Unit\Formatter;

use Monolog\Logger;
use Webthink\MonologSlack\Formatter\SlackLineFormatter;
use Webthink\MonologSlack\Test\Unit\TestCase;

final class SlackLineFormatterTest extends TestCase
{
    public function testNoUsernameByDefault()
    {
        $record = new SlackLineFormatter();
        $data = $record->format($this->getRecord());
        $this->assertArrayNotHasKey('username', $data);
    }

    public function testAddsCustomUsername()
    {
        $formatter = new SlackLineFormatter('Monolog bot');
        $data = $formatter->format($this->getRecord());

        $this->assertArrayHasKey('username', $data);
        $this->assertSame('Monolog bot', $data['username']);
    }

    public function testNoIcon()
    {
        $formatter = new SlackLineFormatter(null);
        $data = $formatter->format($this->getRecord());

        $this->assertArrayNotHasKey('icon_emoji', $data);
    }

    public function testAttachmentsNotPresent()
    {
        $formatter = new SlackLineFormatter();
        $data = $formatter->format($this->getRecord());
        $this->assertArrayNotHasKey('attachments', $data);
    }

    public function testTextEqualsFormatterOutput()
    {
        $formatter = new SlackLineFormatter();
        $data = $formatter->format($this->getRecord(Logger::WARNING, 'Test message'));

        $this->assertArrayHasKey('text', $data);
        $this->assertStringStartsWith('test.WARNING: Test message', $data['text']);
    }

    /**
     * @dataProvider correctEmojiProvider
     * @param string $emoji
     * @param string $expectedEmoji
     */
    public function testCorrectlyParsesEmoji(string $emoji, string $expectedEmoji)
    {
        $formatter = new SlackLineFormatter(null, $emoji);
        $data = $formatter->format($this->getRecord());

        $this->assertArrayHasKey('icon_emoji', $data);
        $this->assertSame($expectedEmoji, $data['icon_emoji']);
    }

    /**
     * @return array
     */
    public function correctEmojiProvider()
    {
        return [
            ['loudspeaker', ':loudspeaker:'],
            [':information_source', ':information_source:'],
            ['exclamation:', ':exclamation:'],
            [':warning:', ':warning:'],
        ];
    }

    public function testNoChannel()
    {
        $formatter = new SlackLineFormatter(null, null, null);
        $data = $formatter->format($this->getRecord());

        $this->assertArrayNotHasKey('channel', $data);
    }

    public function testHasChannel()
    {
        $formatter = new SlackLineFormatter(null, null, null, 'my-slack-channel');
        $data = $formatter->format($this->getRecord());

        $this->assertArrayHasKey('channel', $data);
        $this->assertSame('my-slack-channel', $data['channel']);
    }
}
