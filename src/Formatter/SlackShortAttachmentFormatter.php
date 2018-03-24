<?php

namespace Webthink\MonologSlack\Formatter;

class SlackShortAttachmentFormatter extends AbstractSlackAttachmentFormatter
{
    /**
     * @param array $record
     * @return mixed
     */
    protected function formatFields(array $record)
    {
        $value = $this->toJson($record, true);
        if (strlen($value) > 1950) {
            $value = substr($value, 0, 1950) . '... (truncated)';
        }

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
