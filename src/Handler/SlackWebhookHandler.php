<?php

namespace Webthink\MonologSlack\Handler;

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Monolog\Formatter\FormatterInterface;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;
use Webthink\MonologSlack\Formatter\SlackFormatterInterface;
use Webthink\MonologSlack\Formatter\SlackLineFormatter;
use Webthink\MonologSlack\Utility\ClientInterface;
use Webthink\MonologSlack\Utility\GuzzleClient;

/**
 * Sends notifications through Slack API
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
     * @var ClientInterface
     */
    private $client;

    /**
     * @param string $webhook Slack Webhook string
     * @param string|null $username Name of a bot
     * @param string|null $useCustomEmoji The custom emoji you want to use. Set null if you do not wish to use a custom one.
     * @param int $level The minimum logging level at which this handler will be triggered
     * @param bool $bubble Whether the messages that are handled can bubble up the stack or not
     * @param ClientInterface|null $client
     */
    public function __construct(
        string $webhook,
        string $username = null,
        string $useCustomEmoji = null,
        int $level = Logger::ERROR,
        bool $bubble = true,
        ClientInterface $client = null
    ) {
        parent::__construct($level, $bubble);

        $this->webhook = $webhook;
        $this->username = $username;
        $this->useCustomEmoji = $useCustomEmoji;

        if ($client === null) {
            $client = new GuzzleClient(
                new Client([
                    RequestOptions::TIMEOUT => 1,
                    RequestOptions::CONNECT_TIMEOUT => 1,
                ])
            );
        }
        $this->client = $client;
    }

    /**
     * @param FormatterInterface $formatter
     * @return $this|\Monolog\Handler\HandlerInterface
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
     * @throws \Webthink\MonologSlack\Utility\Exception\TransferException
     */
    protected function write(array $record): void
    {
        $this->client->send($this->webhook, $record['formatted']);
    }

    /**
     * @return SlackLineFormatter
     */
    protected function getDefaultFormatter()
    {
        return new SlackLineFormatter($this->username, $this->useCustomEmoji);
    }
}
