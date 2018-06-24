<?php

namespace Webthink\MonologSlack\Formatter;

use Monolog\Formatter\LineFormatter;

/**
 * A simple formatter that you can use in order to send to slack log message.
 *
 * @author George Mponos <gmponos@gmail.com>
 */
class SlackLineFormatter extends LineFormatter implements SlackFormatterInterface
{
    /**
     * Name of a bot
     *
     * @var string|null
     */
    protected $username;

    /**
     * User icon e.g. 'ghost', 'http://example.com/user.png'
     *
     * @var string
     */
    protected $emoji;

    /**
     * @param string|null $username
     * @param string $emoji
     */
    public function __construct(string $username = null, string $emoji = null)
    {
        parent::__construct();
        $this->username = $username;
        $this->emoji = $emoji;
    }

    /**
     * @param array $record
     * @return array
     */
    public function format(array $record)
    {
        $data['text'] = parent::format($record);

        if ($this->username !== null) {
            $data['username'] = $this->username;
        }

        if ($this->emoji !== null) {
            $data['icon_emoji'] = sprintf(':%s:', $this->emoji);
        }

        return $data;
    }
}
