<?php

namespace Webthink\MonologSlack\Formatter;

class SlackLongAttachmentFormatter extends AbstractSlackAttachmentFormatter
{
    /**
     * @param array $record
     * @return mixed
     */
    protected function formatFields(array $record)
    {
        $result = [];
        foreach ($record as $key => $value) {
            if (is_array($value)) {
                $value = $this->toJson($value, true);
                if (strlen($value) > 1950) {
                    $value = substr($value, 0, 1900) . '... (truncated)';
                }
                $value = sprintf('```%s```', $value);
                $result[] = [
                    'title' => $key,
                    'value' => $value,
                    'short' => false,
                ];
                continue;
            }

            if (strlen($value) > 1950) {
                $value = substr($value, 0, 1900) . '... (truncated)';
            }
            $result[] = [
                'title' => $key,
                'value' => $value,
                'short' => false,
            ];
        }

        return $result;
    }
}
