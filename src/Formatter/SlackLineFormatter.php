<?php

declare(strict_types=1);

namespace Webthink\MonologSlack\Formatter;

use Monolog\Formatter\LineFormatter;
use Monolog\Formatter\NormalizerFormatter;

/**
 * A simple formatter that you can use in order to send to slack log message.
 *
 * @author George Mponos <gmponos@gmail.com>
 */
class SlackLineFormatter extends NormalizerFormatter implements SlackFormatterInterface
{
    /**
     * Username to use as display for the webhook
     *
     * @var string|null
     */
    protected $username;

    /**
     * User icon e.g. 'ghost'
     *
     * @var string|null
     */
    protected $emoji;

    /**
     * @var LineFormatter
     */
    private $lineFormatter;

    /**
     * @param string|null $username
     * @param string|null $emoji
     * @param string|null $format
     */
    public function __construct(?string $username = null, ?string $emoji = null, ?string $format = null)
    {
        parent::__construct();
        $format = $format ?: '%channel%.%level_name%: %message% %context% %extra%';
        $this->lineFormatter = new LineFormatter($format, null, false, true);
        $this->username = $username;
        $this->emoji = $emoji;
    }

    /**
     * @param array $record
     * @return array
     */
    public function format(array $record): array
    {
        $data['text'] = $this->lineFormatter->format($record);

        if ($this->username !== null) {
            $data['username'] = $this->username;
        }

        if ($this->emoji !== null) {
            $data['icon_emoji'] = sprintf(':%s:', $this->emoji);
        }

        return $data;
    }
}
