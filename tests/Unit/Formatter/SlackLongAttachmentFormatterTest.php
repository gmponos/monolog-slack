<?php

namespace Webthink\MonologSlack\Test\Unit\Formatter;

use Monolog\Logger;
use Webthink\MonologSlack\Formatter\SlackLongAttachmentFormatter;
use Webthink\MonologSlack\Test\Unit\TestCase;

class SlackLongAttachmentFormatterTest extends TestCase
{
    /**
     * @var int
     */
    private $jsonFlags;

    /**
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $this->jsonFlags = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;
    }

    /**
     * @return array
     */
    public function dataGetAttachmentColor()
    {
        return [
            [Logger::DEBUG, '#cccccc'],
            [Logger::INFO, '#468847'],
            [Logger::NOTICE, '#3a87ad'],
            [Logger::WARNING, '#c09853'],
            [Logger::ERROR, '#f0ad4e'],
            [Logger::CRITICAL, '#FF7708'],
            [Logger::ALERT, '#C12A19'],
            [Logger::EMERGENCY, '#000000'],
        ];
    }

    /**
     * @test
     * @dataProvider dataGetAttachmentColor
     * @param int $logLevel
     * @param string $expectedColour
     */
    public function getAttachmentColor($logLevel, $expectedColour)
    {
        $formatter = new SlackLongAttachmentFormatter();
        $data = $formatter->format($this->getRecord($logLevel));
        $this->assertSame($expectedColour, $data['attachments'][0]['color']);
    }

    public function testNoUsernameByDefault()
    {
        $data = $this->createFormatter()->format($this->getRecord());
        $this->assertArrayNotHasKey('username', $data);
    }

    public function testAddsCustomUsername()
    {
        $formatter = new SlackLongAttachmentFormatter('Monolog bot');
        $data = $formatter->format($this->getRecord());

        $this->assertArrayHasKey('username', $data);
        $this->assertSame('Monolog bot', $data['username']);
    }

    /**
     * @return array
     */
    public function dataGetEmojiProvider()
    {
        return [
            [':loudspeaker:'],
            [':information_source:'],
            [':exclamation:'],
            [':warning:'],
            [':x:'],
            [':rotating_light:'],
            [':fire:'],
            [':bomb:'],
        ];
    }

    /**
     * @test
     * @dataProvider dataGetEmojiProvider
     * @param string $expected
     */
    public function getEmojiIcon($expected)
    {
        $data = $this->createFormatter(null, $expected)->format($this->getRecord(Logger::ALERT));
        $this->assertSame($expected, $data['icon_emoji']);
    }

    public function testThatEmojiIconIsNotSend()
    {
        $data = $this->createFormatter()->format($this->getRecord());
        $this->assertArrayNotHasKey('icon_emoji', $data);
    }

    public function testAddsTimestampToAttachment()
    {
        $record = $this->getRecord();
        $data = $this->createFormatter()->format($record);

        $attachment = $data['attachments'][0];
        $this->assertArrayHasKey('ts', $attachment);
        $this->assertSame($record['datetime']->getTimestamp(), $attachment['ts']);
    }

    public function testAddsOneAttachment()
    {
        $data = $this->createFormatter()->format($this->getRecord());

        $this->assertArrayHasKey('attachments', $data);
        $this->assertArrayHasKey(0, $data['attachments']);
        $this->assertInternalType('array', $data['attachments'][0]);
    }

    public function testAddsFallbackAndTextToAttachment()
    {
        $data = $this->createFormatter()->format($this->getRecord(Logger::WARNING, 'Test message'));

        $this->assertSame('Test message', $data['attachments'][0]['text']);
        $this->assertSame('Test message', $data['attachments'][0]['fallback']);
    }

    public function testAddsLongAttachmentWithoutContextAndExtra()
    {
        $formatter = new SlackLongAttachmentFormatter(null, true, false);
        $data = $formatter->format($this->getRecord(Logger::ERROR, 'test', ['test' => 1]));

        $attachment = $data['attachments'][0];
        $this->assertArrayHasKey('title', $attachment);
        $this->assertArrayHasKey('fields', $attachment);
        $this->assertCount(0, $attachment['fields']);
        $this->assertSame('test.ERROR', $attachment['title']);
    }

    public function testAddsFieldDoesNotExceed2000Characters()
    {
        $trace = '';
        for ($i = 0; $i < 3000; $i++) {
            $trace .= (string)mt_rand(1, 100);
        }

        $data = $this->createFormatter()
            ->format($this->getRecord(Logger::WARNING, 'Test message', ['exception_trace' => $trace]));
        $this->assertStringEndsWith('... (truncated)', $data['attachments'][0]['fields'][0]['value']);
    }

    public function testAddsInternalFieldDoesNotExceed2000Characters()
    {
        $trace = '';
        for ($i = 0; $i < 3000; $i++) {
            $trace .= (string)mt_rand(1, 100);
        }

        $formatter = new SlackLongAttachmentFormatter();
        $data = $formatter->format($this->getRecord(Logger::WARNING, 'Test message', ['exception_trace' => [$trace]]));

        $this->assertStringEndsWith('... (truncated)```', $data['attachments'][0]['fields'][0]['value']);
    }

    /**
     * @param string|null $username
     * @param string $userIcon
     * @param bool $includeContextAndExtra
     * @return SlackLongAttachmentFormatter
     */
    private function createFormatter($username = null, string $userIcon = null, $includeContextAndExtra = true)
    {
        return new SlackLongAttachmentFormatter($username, $userIcon, $includeContextAndExtra);
    }
}
