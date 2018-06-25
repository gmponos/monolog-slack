<?php

namespace Webthink\MonologSlack\Test\Unit\Handler;

use Monolog\Formatter\LineFormatter;
use Monolog\Logger;
use PHPUnit\Framework\MockObject\MockObject;
use Webthink\MonologSlack\Formatter\SlackShortAttachmentFormatter;
use Webthink\MonologSlack\Handler\SlackWebhookHandler;
use Webthink\MonologSlack\Test\Unit\TestCase;
use Webthink\MonologSlack\Utility\ClientInterface;
use Webthink\MonologSlack\Utility\Exception\TransferException;

class SlackWebhookHandlerTest extends TestCase
{
    /**
     * @var ClientInterface|MockObject
     */
    private $client;

    /**
     * @var SlackWebhookHandler
     */
    private $handler;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        parent::setUp();
        $this->client = $this->getMockBuilder(ClientInterface::class)->getMock();
        $this->handler = new SlackWebhookHandler('www.dummy.com', null, 'rotating_light', Logger::ERROR, true, $this->client);
    }

    /**
     * @test
     */
    public function setAcceptedFormatter()
    {
        $formatter = new SlackShortAttachmentFormatter();
        $this->handler->setFormatter($formatter);
        $this->assertAttributeSame($formatter, 'formatter', $this->handler);
    }

    /**
     * @test
     */
    public function handlerWillHandleTheRecord()
    {
        $this->client->expects($this->once())->method('send')
            ->with('www.dummy.com', $this->callback(function ($value) {
                if (!is_array($value)) {
                    return false;
                }

                $this->assertSame(':rotating_light:', $value['icon_emoji']);
                $this->assertStringEndsWith("test.CRITICAL: test [] []\n", $value['text']);
                return true;
            }))
            ->willReturn(null);
        $this->handler->handle($this->getRecord(Logger::CRITICAL));
    }

    /**
     * @test
     * @expectedException \Webthink\MonologSlack\Utility\Exception\TransferException
     */
    public function clientWillThrowExceptionButHandlerWillFailSilently()
    {
        $this->client->expects($this->once())->method('send')
            ->with('www.dummy.com', $this->callback(function (array $value) {
                $this->assertSame(':rotating_light:', $value['icon_emoji']);
                $this->assertStringEndsWith("test.CRITICAL: test [] []\n", $value['text']);
                return true;
            }))
            ->willThrowException(new TransferException('Bad Request', 400));
        $this->handler->handle($this->getRecord(Logger::CRITICAL));
    }

    /**
     * @test
     */
    public function handlerDoesNotHandleTheRecord()
    {
        $this->client->expects($this->never())->method('send');
        $this->handler->handle($this->getRecord());
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Expected a slack formatter
     */
    public function setFormatterWillThrowException()
    {
        $this->handler->setFormatter(new LineFormatter());
    }
}
