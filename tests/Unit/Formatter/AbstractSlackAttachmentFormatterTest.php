<?php

declare(strict_types=1);

namespace Webthink\MonologSlack\Test\Unit\Formatter;

use Monolog\DateTimeImmutable;
use Monolog\Logger;
use Webthink\MonologSlack\Test\App\FooBar\TestBarNorm;
use Webthink\MonologSlack\Test\App\FooBar\TestFooNorm;
use Webthink\MonologSlack\Test\App\FooBar\TestJsonSerializableNorm;
use Webthink\MonologSlack\Test\App\Formatter\DummySlackAttachmentFormatter;
use Webthink\MonologSlack\Test\Unit\TestCase;

final class AbstractSlackAttachmentFormatterTest extends TestCase
{
    public function testFormat()
    {
        $formatter = new DummySlackAttachmentFormatter();
        $dateTime = new DateTimeImmutable(false);
        $formatted = $formatter->format([
            'level' => Logger::ERROR,
            'level_name' => 'ERROR',
            'channel' => 'meh',
            'message' => 'foo',
            'datetime' => $dateTime,
            'extra' => [
                'foo' => new TestFooNorm(),
                'bar' => new TestBarNorm(),
                'baz' => [],
                'res' => fopen('php://memory', 'rb'),
                'json' => new TestJsonSerializableNorm(),
                'date_object' => $dateTime,
            ],
            'context' => [
                'foo' => 'bar',
                'baz' => 'qux',
                'inf' => INF,
                '-inf' => - INF,
                'nan' => acos(4),
            ],
        ]);

        $this->assertEquals([
            'attachments' => [
                [
                    'fallback' => 'foo',
                    'text' => 'foo',
                    'color' => '#f0ad4e',
                    'fields' => [
                        [
                            'foo' => 'bar',
                            'baz' => 'qux',
                            'inf' => 'INF',
                            '-inf' => '-INF',
                            'nan' => 'NaN',
                        ],
                        [
                            'foo' => ['Webthink\MonologSlack\Test\App\FooBar\TestFooNorm' => ['foo' => 'foo']],
                            'bar' => ['Webthink\MonologSlack\Test\App\FooBar\TestBarNorm' => 'bar'],
                            'baz' => [],
                            'res' => ['resource' => 'stream'],
                            'json' => ['Webthink\MonologSlack\Test\App\FooBar\TestJsonSerializableNorm' => ['foo' => 'bar']],
                            'date_object' => $dateTime->format('Y-m-d\TH:i:sP'),
                        ],
                    ],
                    'mrkdwn_in' => ['fields'],
                    'title' => 'meh.ERROR',
                    'ts' => $dateTime->getTimestamp(),
                ],
            ],
        ], $formatted);
    }

    public function testFormatExceptions()
    {
        $formatter = new DummySlackAttachmentFormatter();

        $exception = new \RuntimeException('foo', 0, new \LogicException('bar'));
        $formatted = $formatter->format($this->getRecord(Logger::WARNING, 'Message', ['exception' => $exception]));

        $this->assertArrayNotHasKey('trace', $formatted['attachments'][0]['fields'][0]['exception']);
        $this->assertArrayNotHasKey('previous', $formatted['attachments'][0]['fields'][0]['exception']);

        $this->assertSame([
            'exception' => [
                'class' => get_class($exception),
                'message' => $exception->getMessage(),
                'code' => $exception->getCode(),
                'file' => $exception->getFile() . ':' . $exception->getLine(),
            ],
        ], $formatted['attachments'][0]['fields'][0]);
    }

    /**
     * Test issue #137
     */
    public function testIgnoresRecursiveObjectReferences()
    {
        $this->markTestSkipped('Skipping for now');
        // set up the recursion
        $foo = new \stdClass();
        $bar = new \stdClass();

        $foo->bar = $bar;
        $bar->foo = $foo;

        // set an error handler to assert that the error is not raised anymore
        $that = $this;
        set_error_handler(function ($level, $message, $file, $line, $context) use ($that) {
            if (error_reporting() & $level) {
                restore_error_handler();
                $that->fail("$message should not be raised");
            }
        });

        $formatter = new DummySlackAttachmentFormatter();
        $reflMethod = new \ReflectionMethod($formatter, 'toJson');
        $reflMethod->setAccessible(true);
        $res = $reflMethod->invoke($formatter, [$foo, $bar], true);

        restore_error_handler();

        $this->assertEquals(json_encode([$foo, $bar]), $res);
    }

    public function testIgnoresInvalidTypes()
    {
        $this->markTestSkipped('Skipping for now');
        // set up the recursion
        $resource = fopen(__FILE__, 'r');

        // set an error handler to assert that the error is not raised anymore
        $that = $this;
        set_error_handler(function ($level, $message, $file, $line, $context) use ($that) {
            if (error_reporting() & $level) {
                restore_error_handler();
                $that->fail("$message should not be raised");
            }
        });

        $formatter = new DummySlackAttachmentFormatter();
        $reflMethod = new \ReflectionMethod($formatter, 'toJson');
        $reflMethod->setAccessible(true);
        $res = $reflMethod->invoke($formatter, [$resource], true);

        restore_error_handler();

        $this->assertEquals(json_encode([$resource]), $res);
    }

    public function testNormalizeHandleLargeArrays()
    {
        $formatter = new DummySlackAttachmentFormatter();
        $largeArray = range(1, 2000);

        $res = $formatter->format($this->getRecord(Logger::WARNING, 'Message', $largeArray));

        $this->assertCount(1000, $res['attachments'][0]['fields'][0]);
        $this->assertEquals('Over 1000 items, aborting normalization', $res['attachments'][0]['fields'][0]['...']);
    }

    public function testConvertsInvalidEncodingAsLatin9()
    {
        $formatter = new DummySlackAttachmentFormatter();
        $reflMethod = new \ReflectionMethod($formatter, 'toJson');
        $reflMethod->setAccessible(true);

        $res = $reflMethod->invoke($formatter, ['message' => "\xA4\xA6\xA8\xB4\xB8\xBC\xBD\xBE"]);

        $this->assertSame('{"message":"€ŠšŽžŒœŸ"}', $res);
    }

    /**
     * @param int $code
     * @param string $msg
     * @dataProvider providesHandleJsonErrorFailure
     */
    public function testHandleJsonErrorFailure($code, $msg)
    {
        $this->markTestSkipped('Skipping for now');
        $formatter = new DummySlackAttachmentFormatter();
        $reflMethod = new \ReflectionMethod($formatter, 'handleJsonError');
        $reflMethod->setAccessible(true);

        $this->expectException('RuntimeException');
        $this->expectExceptionMessage($msg);
        $reflMethod->invoke($formatter, $code, 'faked');
    }

    public function providesHandleJsonErrorFailure()
    {
        return [
            'depth' => [JSON_ERROR_DEPTH, 'Maximum stack depth exceeded'],
            'state' => [JSON_ERROR_STATE_MISMATCH, 'Underflow or the modes mismatch'],
            'ctrl' => [JSON_ERROR_CTRL_CHAR, 'Unexpected control character found'],
            'default' => [ - 1, 'Unknown error'],
        ];
    }
}
