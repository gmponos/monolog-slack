<?php

namespace Webthink\MonologSlack\Handler;

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Monolog\Formatter\FormatterInterface;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;
use Webthink\MonologSlack\Formatter\SlackFormatterInterface;
use Webthink\MonologSlack\Formatter\SlackLineFormatter;

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
     * @var null|string
     */
    private $username;

    /**
     * @var bool
     */
    private $useCustomEmoji;

    /**
     * @var bool
     */
    private $includeContextAndExtra;

    /**
     * @var Client|null
     */
    private $client;

    /**
     * @param string $webhook Slack API token
     * @param string|null $username Name of a bot
     * @param bool $useCustomEmoji If you should use custom emoji or not
     * @param int $level The minimum logging level at which this handler will be triggered
     * @param bool $bubble Whether the messages that are handled can bubble up the stack or not
     * @param bool $includeContextAndExtra Whether the attachment should include context and extra data
     * @param Client|null $client
     */
    public function __construct(
        $webhook,
        $username = null,
        $useCustomEmoji = true,
        $level = Logger::ERROR,
        $bubble = true,
        $includeContextAndExtra = true,
        Client $client = null
    ) {
        parent::__construct($level, $bubble);

        $this->webhook = $webhook;
        $this->username = $username;
        $this->useCustomEmoji = $useCustomEmoji;
        $this->includeContextAndExtra = $includeContextAndExtra;

        if ($client === null) {
            $client = new Client([
                RequestOptions::TIMEOUT => 1,
                RequestOptions::CONNECT_TIMEOUT => 1,
            ]);
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
     */
    protected function write(array $record)
    {
        try {
            $this->client->request('post', $this->webhook, [
                RequestOptions::JSON => $record['formatted'],
            ]);
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
