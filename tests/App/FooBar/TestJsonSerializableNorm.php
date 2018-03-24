<?php

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
