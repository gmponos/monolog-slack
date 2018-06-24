<?php

namespace Webthink\MonologSlack\Formatter;

class SlackShortAttachmentFormatter extends AbstractSlackAttachmentFormatter
{
    /**
     * @param array $record
     * @return array
     */
    protected function formatFields(array $record): array
    {
        $value = $this->toJson($record, true);
        if (strlen($value) > 1900) {
            $value = substr($value, 0, 1900) . '... (truncated)';
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
