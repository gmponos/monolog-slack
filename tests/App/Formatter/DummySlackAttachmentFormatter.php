<?php

namespace Webthink\MonologSlack\Test\App\Formatter;

use Webthink\MonologSlack\Formatter\AbstractSlackAttachmentFormatter;

class DummySlackAttachmentFormatter extends AbstractSlackAttachmentFormatter
{
    /**
     * @param array $record
     * @return mixed
     */
    protected function formatFields(array $record)
    {
        return [$record];
    }
}
