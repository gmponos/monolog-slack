<?php

declare(strict_types=1);

namespace Webthink\MonologSlack\Utility;

use GuzzleHttp\ClientInterface as GuzzleClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;
use Webthink\MonologSlack\Utility\Exception\TransferException;

/**
 * This is a class that wraps a Guzzle Client in order to send records to slack.
 *
 * @deprecated Use a PSR-18 client instead.
 */
class GuzzleClient implements ClientInterface
{
    /**
     * @var GuzzleClientInterface
     */
    private $client;

    /**
     * @param GuzzleClientInterface $client
     */
    public function __construct(GuzzleClientInterface $client)
    {
        @trigger_error('Using the custom HTTP Client implementation is deprecated and will be removed on 2.x. Use a PSR-18 HTTP Client instead.', E_USER_DEPRECATED);
        $this->client = $client;
    }

    /**
     * @param string $webhook
     * @param array $data
     * @return void
     * @throws TransferException
     */
    public function send(string $webhook, array $data): void
    {
        try {
            $this->client->request('post', $webhook, [
                RequestOptions::JSON => $data,
            ]);
        } catch (GuzzleException $e) {
            throw new TransferException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
