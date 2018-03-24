<?php

namespace Webthink\MonologSlack\Test\Unit\Handler;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use Monolog\Formatter\LineFormatter;
use Monolog\Logger;
use Webthink\MonologSlack\Formatter\SlackShortAttachmentFormatter;
use Webthink\MonologSlack\Handler\SlackWebhookHandler;
use Webthink\MonologSlack\Test\Unit\TestCase;

class SlackWebhookHandlerTest extends TestCase
{
    /**
     * @var Client|\PHPUnit_Framework_MockObject_MockObject
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
        $this->client = $this->getMockBuilder(Client::class)->disableOriginalConstructor()->getMock();
        $this->handler = new SlackWebhookHandler('www.dummy.com', null, 'rotating_light', Logger::ERROR, true, true, $this->client);
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
        $this->client->expects($this->once())->method('request')
            ->with(
                'post',
                'www.dummy.com',
                $this->callback(function ($value) {
                    if (!is_array($value)) {
                        return false;
                    }

                    $this->assertSame(':rotating_light:', $value['json']['icon_emoji']);
                    $this->assertStringEndsWith("test.CRITICAL: test [] []\n", $value['json']['text']);
                    return true;
                })
            )
            ->willReturn(null);
        $this->handler->handle($this->getRecord(Logger::CRITICAL));
    }

    /**
     * @test
     */
    public function clientWillThrowExceptionButHandlerWillFailSilently()
    {
        $this->client->expects($this->once())->method('request')
            ->with(
                'post',
                'www.dummy.com',
                $this->callback(function (array $value) {
                    $this->assertSame(':rotating_light:', $value['json']['icon_emoji']);
                    $this->assertStringEndsWith("test.CRITICAL: test [] []\n", $value['json']['text']);
                    return true;
                })
            )
            ->willThrowException(new RequestException('Bad Request', new Request('get', 'www.dummy.com')));
        $this->handler->handle($this->getRecord(Logger::CRITICAL));
    }

    /**
     * @test
     */
    public function handlerDoesNotHandleTheRecord()
    {
        $this->client->expects($this->never())->method('request');
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
