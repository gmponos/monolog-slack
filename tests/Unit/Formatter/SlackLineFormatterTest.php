<?php

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
        $formatter = new SlackLineFormatter(null, false);
        $data = $formatter->format($this->getRecord(Logger::WARNING, 'Test message'));

        $this->assertArrayHasKey('text', $data);
        $this->assertStringEndsWith('test.WARNING: Test message [] []' . "\n", $data['text']);
    }
}
