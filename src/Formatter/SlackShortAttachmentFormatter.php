<?php

namespace Webthink\MonologSlack\Formatter;

/**
 * A Formatter that you can use in order to send to slack log message using the Attachment format.
 *
 * This Formatter will give the message a Short format.
 *
 * @author George Mponos <gmponos@gmail.com>
 */
class SlackShortAttachmentFormatter extends AbstractSlackAttachmentFormatter
{
    /**
     * @param array $record
     * @return mixed
     */
    protected function formatFields(array $record)
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
