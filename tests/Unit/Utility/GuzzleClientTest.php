<?php

declare(strict_types=1);

namespace Webthink\MonologSlack\Test\Unit\Utility;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use PHPUnit\Framework\MockObject\MockObject;
use Webthink\MonologSlack\Test\Unit\TestCase;
use Webthink\MonologSlack\Utility\Exception\TransferException;
use Webthink\MonologSlack\Utility\GuzzleClient;

final class GuzzleClientTest extends TestCase
{
    /**
     * @var ClientInterface|MockObject
     */
    private $guzzle;

    /**
     * @var GuzzleClient
     */
    private $client;

    public function setUp()
    {
        parent::setUp();
        $this->guzzle = $this->getMockBuilder(ClientInterface::class)->getMock();
        $this->client = new GuzzleClient($this->guzzle);
    }

    public function testSendWithoutException()
    {
        $this->guzzle->expects($this->once())->method('request')->with('post', 'www.test.com', ['json' => []]);
        $this->client->send('www.test.com', []);
    }

    public function testSendWithException()
    {
        $this->guzzle->expects($this->once())->method('request')->willThrowException(
            new RequestException('Bad Request', new Request('post', 'www.test.com'))
        );

        $this->expectException(TransferException::class);
        $this->client->send('www.test.com', []);
    }
}
