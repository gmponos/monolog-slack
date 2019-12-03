<?php

declare(strict_types=1);

namespace Webthink\MonologSlack\Test\App\FooBar;

class TestJsonSerializableNorm implements \JsonSerializable
{
    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return [
            'foo' => 'bar',
        ];
    }
}
