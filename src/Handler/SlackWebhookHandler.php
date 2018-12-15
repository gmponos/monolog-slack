<?php

namespace Webthink\MonologSlack\Handler;

use GuzzleHttp\Psr7\Request;
use Monolog\Formatter\FormatterInterface;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Handler\HandlerInterface;
use Monolog\Logger;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Webthink\MonologSlack\Formatter\SlackFormatterInterface;
use Webthink\MonologSlack\Formatter\SlackLineFormatter;

/**
 * Sends notifications through a Slack Webhook.
 *
 * @author George Mponos <gmponos@gmail.com>
 */
class SlackWebhookHandler extends AbstractProcessingHandler
{
    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var string
     */
    private $webhook;

    /**
     * @var string|null
     */
    private $username;

    /**
     * @var string|null
     */
    private $useCustomEmoji;

    /**
     * @var RequestFactoryInterface
     */
    private $requestFactory;

    /**
     * @param ClientInterface $client
     * @param RequestFactoryInterface $requestFactory
     * @param string $webhook Slack Webhook string
     * @param string|null $username Name of a bot
     * @param string|null $useCustomEmoji The custom emoji you want to use. Set null if you do not wish to use a custom one.
     * @param string|int $level The minimum logging level at which this handler will be triggered
     * @param bool $bubble Whether the messages that are handled can bubble up the stack or not
     */
    public function __construct(
        ClientInterface $client,
        RequestFactoryInterface $requestFactory,
        string $webhook,
        string $username = null,
        string $useCustomEmoji = null,
        $level = Logger::ERROR,
        bool $bubble = true
    ) {
        parent::__construct($level, $bubble);

        $this->client = $client;
        $this->requestFactory = $requestFactory;
        $this->webhook = $webhook;
        $this->username = $username;
        $this->useCustomEmoji = $useCustomEmoji;
    }

    /**
     * @param FormatterInterface $formatter
     * @return self
     * @throws \InvalidArgumentException
     */
    public function setFormatter(FormatterInterface $formatter): HandlerInterface
    {
        if (!$formatter instanceof SlackFormatterInterface) {
            throw new \InvalidArgumentException('Expected a slack formatter');
        }

        return parent::setFormatter($formatter);
    }

    /**
     * @param array $record
     * @return void
     */
    protected function write(array $record): void
    {
        $body = json_encode($record['formatted']);
        if ($body === false) {
            throw new \InvalidArgumentException('Could not format record to json');
        };

        $request = $this->requestFactory->createRequest('POST', $this->webhook);
        $request = $request->withHeader('Content-Type', ['application/json']);
        $request->getBody()->write($body);
        $request->getBody()->rewind();

        $this->client->sendRequest($request);
    }

    /**
     * @return SlackLineFormatter
     */
    protected function getDefaultFormatter(): FormatterInterface
    {
        return new SlackLineFormatter($this->username, $this->useCustomEmoji);
    }
}
