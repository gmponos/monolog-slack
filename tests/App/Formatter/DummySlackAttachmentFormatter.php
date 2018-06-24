<?php

namespace Webthink\MonologSlack\Test\App\Formatter;

use Webthink\MonologSlack\Formatter\AbstractSlackAttachmentFormatter;

class DummySlackAttachmentFormatter extends AbstractSlackAttachmentFormatter
{
    /**
     * @param array $record
     * @return array
     */
    protected function formatFields(array $record): array
    {
        return [$record];
    }
}
