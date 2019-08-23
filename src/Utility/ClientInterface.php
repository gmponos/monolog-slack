<?php

declare(strict_types=1);

namespace Webthink\MonologSlack\Utility;

use Webthink\MonologSlack\Utility\Exception\TransferException;

/**
 * You can implement this interface in order to create your own custom way of sending messages to Slack.
 *
 * @deprecated Use a PSR-18 client instead.
 */
interface ClientInterface
{
    /**
     * @param string $webhook
     * @param array $data
     * @return void
     * @throws TransferException
     */
    public function send(string $webhook, array $data): void;
}
