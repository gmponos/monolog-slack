<?php

namespace Webthink\MonologSlack\Utility;

use Webthink\MonologSlack\Utility\Exception\TransferException;

class CurlClient implements ClientInterface
{
    private static $retriableErrorCodes = [
        CURLE_COULDNT_RESOLVE_HOST,
        CURLE_COULDNT_CONNECT,
        CURLE_HTTP_NOT_FOUND,
        CURLE_READ_ERROR,
        CURLE_OPERATION_TIMEOUTED,
        CURLE_HTTP_POST_ERROR,
        CURLE_SSL_CONNECT_ERROR,
    ];

    /**
     * @param string $webhook
     * @param array $data
     * @throws TransferException
     */
    public function send($webhook, array $data): void
    {
        $ch = curl_init();
        $options = [
            CURLOPT_URL => $webhook,
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Content-type: application/json'],
            CURLOPT_POSTFIELDS => json_encode($data),
        ];

        if (defined('CURLOPT_SAFE_UPLOAD')) {
            $options[CURLOPT_SAFE_UPLOAD] = true;
        }

        curl_setopt_array($ch, $options);

        $this->execute($ch);
    }

    /**
     * Executes a CURL request with optional retries and exception on failure
     *
     * @param  resource $ch curl handler
     * @param int $retries
     * @param bool $closeAfterDone
     * @throws TransferException
     */
    private function execute($ch, int $retries = 5, bool $closeAfterDone = true)
    {
        while ($retries--) {
            if (curl_exec($ch) === false) {
                $curlErrno = curl_errno($ch);

                if (false === in_array($curlErrno, self::$retriableErrorCodes, true) || !$retries) {
                    $curlError = curl_error($ch);

                    if ($closeAfterDone) {
                        curl_close($ch);
                    }

                    throw new TransferException(sprintf('Curl error (code %s): %s', $curlErrno, $curlError));
                }

                continue;
            }

            if ($closeAfterDone) {
                curl_close($ch);
            }
            break;
        }
    }
}