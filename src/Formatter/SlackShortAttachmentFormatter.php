<?php

declare(strict_types=1);

namespace Webthink\MonologSlack\Formatter;

/**
 * A Formatter that you can use in order to send to slack log message using the Attachment format.
 *
 * This Formatter will give the message a Short format meaning that all context will be put together.
 *
 * @author George Mponos <gmponos@gmail.com>
 */
class SlackShortAttachmentFormatter extends AbstractSlackAttachmentFormatter
{
    /**
     * @param array $record
     * @return array
     */
    protected function formatFields(array $record): array
    {
        $value = $this->truncateStringIfNeeded($this->toJson($record, true));
        $value = sprintf('```%s```', $value);
        return [
            [
                'title' => '',
                'value' => $value,
                'short' => false,
            ],
        ];
    }
}
