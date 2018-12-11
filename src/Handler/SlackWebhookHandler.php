<?php

namespace Webthink\MonologSlack\Handler;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\RequestOptions;
use Monolog\Formatter\FormatterInterface;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;
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
     * @var \Webthink\MonologSlack\Utility\ClientInterface|\Psr\Http\Client\ClientInterface
     */
    private $client;

    /**
     * @param string $webhook Slack Webhook string
     * @param string|null $username Name of a bot
     * @param string|null $useCustomEmoji The custom emoji you want to use. Set null if you do not wish to use a custom one.
     * @param int $level The minimum logging level at which this handler will be triggered
     * @param bool $bubble Whether the messages that are handled can bubble up the stack or not
     * @param \Webthink\MonologSlack\Utility\ClientInterface|\Psr\Http\Client\ClientInterface|null $client
     */
    public function __construct(
        string $webhook,
        string $username = null,
        string $useCustomEmoji = null,
        int $level = Logger::ERROR,
        bool $bubble = true,
        $client = null
    ) {
        parent::__construct($level, $bubble);

        $this->webhook = $webhook;
        $this->username = $username;
        $this->useCustomEmoji = $useCustomEmoji;

        if ($client === null) {
            $client = \Http\Adapter\Guzzle6\Client::createWithConfig([
                RequestOptions::TIMEOUT => 1,
                RequestOptions::CONNECT_TIMEOUT => 1,
                RequestOptions::HTTP_ERRORS => false,
            ]);
        }

        $this->client = $client;
    }

    /**
     * @param FormatterInterface $formatter
     * @return self
     * @throws \InvalidArgumentException
     */
    public function setFormatter(FormatterInterface $formatter)
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
        try {
            if ($this->client instanceof \Psr\Http\Client\ClientInterface) {
                $body = json_encode($record['formatted']);
                if ($body === false) {
                    throw new \InvalidArgumentException('Could not format record to json');
                };

                $this->client->sendRequest(
                    new Request('POST', $this->webhook, ['Content-Type' => ['application/json']], $body)
                );
                return;
            }

            $this->client->send($this->webhook, $record['formatted']);
        } finally {
            return;
        }
    }

    /**
     * @return SlackLineFormatter
     */
    protected function getDefaultFormatter()
    {
        return new SlackLineFormatter($this->username, $this->useCustomEmoji);
    }
}
