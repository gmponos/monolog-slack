<?php

declare(strict_types=1);

namespace Webthink\MonologSlack\Test\Unit\Handler;

use Monolog\Formatter\LineFormatter;
use Monolog\Logger;
use PHPUnit\Framework\MockObject\MockObject;
use Webthink\MonologSlack\Formatter\SlackFormatterInterface;
use Webthink\MonologSlack\Formatter\SlackShortAttachmentFormatter;
use Webthink\MonologSlack\Handler\SlackWebhookHandler;
use Webthink\MonologSlack\Test\Unit\TestCase;
use Webthink\MonologSlack\Utility\ClientInterface;
use Webthink\MonologSlack\Utility\Exception\TransferException;

final class SlackWebhookHandlerTest extends TestCase
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
        $this->client = $this->createMock(ClientInterface::class);
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
                $this->assertStringStartsWith('test.CRITICAL: test', $value['text']);
                return true;
            }))
            ->willReturn(null);
        $this->handler->handle($this->getRecord(Logger::CRITICAL));
    }

    /**
     * @test
     */
    public function clientWillThrowException()
    {
        $this->client->expects($this->once())->method('send')
            ->with('www.dummy.com', $this->callback(function (array $value) {
                $this->assertSame(':rotating_light:', $value['icon_emoji']);
                $this->assertStringStartsWith('test.CRITICAL: test', $value['text']);
                return true;
            }))
            ->willThrowException(new TransferException('Bad Request', 400));

        $this->expectException(\Exception::class);
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
     */
    public function setFormatterWillThrowException()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf('Expected an instance of %s', SlackFormatterInterface::class));
        $this->handler->setFormatter(new LineFormatter());
    }
}
