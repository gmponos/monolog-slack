<?php

declare(strict_types=1);

namespace Webthink\MonologSlack\Utility\Exception;

/**
 * This is the exception that will be throw in cases when a client could not communicate with slack.
 *
 * @internal
 * @deprecated Use a PSR-18 client instead.
 */
class TransferException extends \Exception
{
}
