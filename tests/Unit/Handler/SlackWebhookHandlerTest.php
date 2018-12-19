<?php

namespace Webthink\MonologSlack\Test\Unit\Handler;

use GuzzleHttp\Psr7\Request;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\WhatFailureGroupHandler;
use Monolog\Logger;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Webthink\MonologSlack\Formatter\SlackFormatterInterface;
use Webthink\MonologSlack\Formatter\SlackShortAttachmentFormatter;
use Webthink\MonologSlack\Handler\SlackWebhookHandler;
use Webthink\MonologSlack\Test\Unit\TestCase;

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
     * @var RequestFactoryInterface
     */
    private $requestFactory;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        parent::setUp();
        $this->client = $this->getMockBuilder(ClientInterface::class)->getMock();
        $this->requestFactory = $this->getMockBuilder(RequestFactoryInterface::class)->getMock();
        $this->handler = new SlackWebhookHandler($this->client, $this->requestFactory, 'www.dummy.com');
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
        $this->requestFactory->expects($this->once())
            ->method('createRequest')
            ->with('POST', 'www.dummy.com')
            ->willReturn(new Request('POST', 'www.dummy.com'));

        $this->client->expects($this->once())
            ->method('sendRequest')
            ->with($this->callback(function (RequestInterface $value) {
                $body = $value->getBody()->getContents();
                $this->assertStringContainsString('test.CRITICAL: test', $body);
                return true;
            }));
        $this->handler->handle($this->getRecord(Logger::CRITICAL));
    }

    /**
     * @test
     */
    public function clientWillThrowException()
    {
        $this->requestFactory->expects($this->once())
            ->method('createRequest')
            ->with('POST', 'www.dummy.com')
            ->willReturn(new Request('POST', 'www.dummy.com'));

        $this->client->expects($this->once())
            ->method('sendRequest')
            ->with($this->callback(function (RequestInterface $value) {
                $body = $value->getBody()->getContents();
                $this->assertStringContainsString('test.CRITICAL: test', $body);
                return true;
            }))
            ->willThrowException(new \Exception());

        $this->expectException(\Exception::class);
        $this->handler->handle($this->getRecord(Logger::CRITICAL));
    }

    /**
     * @test
     */
    public function clientWillThrowExceptionWrappedIntoWhatFailureGroup()
    {
        $this->requestFactory->expects($this->once())
            ->method('createRequest')
            ->with('POST', 'www.dummy.com')
            ->willReturn(new Request('POST', 'www.dummy.com'));

        $this->client->expects($this->once())
            ->method('sendRequest')
            ->with($this->callback(function (RequestInterface $value) {
                $body = $value->getBody()->getContents();
                $this->assertStringContainsString('test.CRITICAL: test', $body);
                return true;
            }))
            ->willThrowException(new \Exception());

        $handler = new WhatFailureGroupHandler([$this->handler]);
        $handler->handle($this->getRecord(Logger::CRITICAL));
    }

    /**
     * @test
     */
    public function handlerDoesNotHandleTheRecord()
    {
        $this->client->expects($this->never())->method('sendRequest');
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
