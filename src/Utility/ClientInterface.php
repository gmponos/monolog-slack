<?php

namespace Webthink\MonologSlack\Utility;

use Webthink\MonologSlack\Utility\Exception\TransferException;

/**
 * You can implement this interface in order to create your own custom way of sending messages to Slack.
 */
interface ClientInterface
{
    /**
     * @param string $webhook
     * @param array $data
     * @throws TransferException
     * @return void
     */
    public function send($webhook, array $data): void;
}
