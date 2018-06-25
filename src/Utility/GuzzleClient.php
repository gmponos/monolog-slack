<?php

namespace Webthink\MonologSlack\Utility;

use GuzzleHttp\ClientInterface as GuzzleClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;
use Webthink\MonologSlack\Utility\Exception\TransferException;

/**
 * This is a class that wraps a Guzzle Client in order to send records to slack.
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
        $this->client = $client;
    }

    /**
     * @param string $webhook
     * @param array $data
     * @throws TransferException
     */
    public function send($webhook, array $data): void
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