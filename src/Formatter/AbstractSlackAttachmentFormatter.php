<?php

namespace Webthink\MonologSlack\Formatter;

use Exception;
use Monolog\Formatter\NormalizerFormatter;
use Monolog\Logger;
use Throwable;

abstract class AbstractSlackAttachmentFormatter extends NormalizerFormatter implements SlackFormatterInterface
{
    /**
     * Name of the bot
     *
     * @var string|null
     */
    private $username;

    /**
     * User icon e.g. 'ghost', 'http://example.com/user.png'
     *
     * @var string
     */
    private $emoji;

    /**
     * Whether the attachment should include context and extra data
     *
     * @var bool
     */
    private $includeContextAndExtra;

    /**
     * @param string|null $username The username of the bot.
     * @param string $emoji
     * @param bool $includeContextAndExtra
     */
    public function __construct(string $username = null, string $emoji = null, bool $includeContextAndExtra = true)
    {
        parent::__construct();
        $this->username = $username;
        $this->emoji = $emoji !== null ? trim($emoji, ':') : null;
        $this->includeContextAndExtra = $includeContextAndExtra;
    }

    /**
     * @param array $record
     * @return array
     */
    public function format(array $record)
    {
        $data = [];

        if ($this->username) {
            $data['username'] = $this->username;
        }

        if ($this->emoji !== null) {
            $data['icon_emoji'] = sprintf(':%s:', $this->emoji);
        }

        $attachment = [
            'fallback' => $record['message'],
            'text' => $record['message'],
            'color' => $this->getAttachmentColor($record['level']),
            'fields' => [],
            'mrkdwn_in' => ['fields'],
            'title' => $record['channel'] . '.' . $record['level_name'],
            'ts' => $record['datetime']->getTimestamp(),
        ];

        if ($this->includeContextAndExtra) {
            $attachment['fields'] = $this->createAttachmentFields($record);
        }

        $data['attachments'] = [$attachment];
        return $data;
    }

    /**
     * @param mixed $data
     * @return mixed
     */
    protected function normalize($data)
    {
        if (null === $data || is_scalar($data)) {
            return $this->normalizeScalar($data);
        }

        if (is_array($data) || $data instanceof \Traversable) {
            return $this->normalizeArray($data);
        }

        if (is_object($data)) {
            return $this->normalizeObject($data);
        }

        if (is_resource($data)) {
            return ['resource' => get_resource_type($data)];
        }

        return $data;
    }

    /**
     * @param mixed $e
     * @return array
     * @throws \InvalidArgumentException
     */
    protected function normalizeException($e)
    {
        if (!$e instanceof Exception && !$e instanceof Throwable) {
            throw new \InvalidArgumentException(
                sprintf('Exception/Throwable expected, got %s / %s', gettype($e), get_class($e))
            );
        }

        $data = [
            'class' => get_class($e),
            'message' => $e->getMessage(),
            'code' => $e->getCode(),
            'file' => $e->getFile() . ':' . $e->getLine(),
        ];

        return $data;
    }

    /**
     * Returned a Slack message attachment color associated with
     * provided level.
     *
     * @param int $level
     * @return string
     */
    protected function getAttachmentColor(int $level): string
    {
        $logLevels = [
            Logger::DEBUG => '#cccccc',
            Logger::INFO => '#468847',
            Logger::NOTICE => '#3a87ad',
            Logger::WARNING => '#c09853',
            Logger::ERROR => '#f0ad4e',
            Logger::CRITICAL => '#FF7708',
            Logger::ALERT => '#C12A19',
            Logger::EMERGENCY => '#000000',
        ];

        return $logLevels[$level];
    }

    /**
     * @param array $record
     * @return mixed
     */
    abstract protected function formatFields(array $record);

    /**
     * @param mixed $data
     * @return mixed
     */
    private function normalizeObject($data)
    {
        if ($data instanceof \DateTimeInterface) {
            return $data->format($this->dateFormat);
        }

        if ($data instanceof Exception || (PHP_VERSION_ID > 70000 && $data instanceof Throwable)) {
            return $this->normalizeException($data);
        }

        $class = get_class($data);

        if (method_exists($data, '__toString')) {
            return [$class => $data->__toString()];
        }

        if ($data instanceof \JsonSerializable) {
            return [$class => $data->jsonSerialize()];
        }

        // the rest is json-serialized in some way
        $value = json_decode($this->toJson($data, true), true);
        return [$class => $value];
    }

    /**
     * @param mixed $data
     * @return array
     */
    private function normalizeArray($data)
    {
        $normalized = [];
        $count = 1;
        foreach ($data as $key => $value) {
            if ($count++ >= 1000) {
                $normalized['...'] = 'Over 1000 items, aborting normalization';
                break;
            }
            $normalized[$key] = $this->normalize($value);
        }

        return $normalized;
    }

    /**
     * @param array $record
     * @return array
     */
    private function createAttachmentFields(array $record)
    {
        $fields = [];
        foreach (['context', 'extra'] as $key) {
            if (empty($record[$key])) {
                continue;
            }

            $normalized = $this->normalize($record[$key]);
            $fields = array_merge(
                $fields,
                $this->formatFields($normalized)
            );
        }

        return $fields;
    }

    /**
     * @param mixed $data
     * @return string
     */
    private function normalizeScalar($data)
    {
        if (is_float($data)) {
            if (is_infinite($data)) {
                return ($data > 0 ? '' : '-') . 'INF';
            }
            if (is_nan($data)) {
                return 'NaN';
            }
        }

        return $data;
    }
}
