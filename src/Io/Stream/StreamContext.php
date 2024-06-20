<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

use Dogma\Check;
use Dogma\ExceptionValueFormatter;
use Dogma\Http\HttpHeaderParser;
use Dogma\Http\HttpMethod;
use Dogma\Io\IoException;
use Dogma\Io\Stream\FtpContext;
use Dogma\Io\Stream\HttpContext;
use Dogma\Io\Stream\PharContext;
use Dogma\Io\Stream\SocketContext;
use Dogma\Io\Stream\StreamEvent;
use Dogma\Io\Stream\ZipContext;
use Dogma\ResourceType;
use Dogma\StrictBehaviorMixin;

/**
 * Wrapper over stream_context_create() etc.
 */
class StreamContext
{
    use StrictBehaviorMixin;

    /** @var resource */
    private $context;

    /** @var callable[] */
    private $callbacks = [];

    /**
     * @param resource $context
     */
    private function __construct($context)
    {
        $this->context = $context;
    }

    /**
     * @internal
     * @return resource(stream-context)
     */
    public function getResource()
    {
        return $this->context;
    }

    /**
     * @param mixed[] $options
     * @return self
     */
    public function create(array $options): self
    {
        $context = stream_context_create($options);

        return new self($context);
    }

    /**
     * @param resource $context
     * @return self
     */
    public function createFromResource($context): self
    {
        Check::resource($context, ResourceType::STREAM_CONTEXT);

        return new self($context);
    }

    /**
     * @param string $method
     * @param mixed[] $headers
     * @param string|null $data
     * @return HttpContext
     */
    public static function createHttp(string $method = HttpMethod::GET, array $headers = [], ?string $data = null): HttpContext
    {
        $options = [
            'http' => [
                'method' => strtoupper($method),
            ],
        ];
        if ($headers !== []) {
            $options['http']['header'] = HttpHeaderParser::formatHeaders($headers);
        }
        if ($data !== null) {
            $options['http']['content'] = $data;
        }

        $context = stream_context_create($options);

        return new HttpContext($context);
    }

    public static function createFtp(bool $overwrite = false, ?int $resumeAt = null, ?string $proxy = null): FtpContext
    {
        $options = [
            'ftp' => [
                'overwrite' => $overwrite,
            ],
        ];
        if ((int) $resumeAt !== 0) {
            $options['ftp']['resume_pos'] = $resumeAt;
        }
        if ($proxy !== null) {
            $options['ftp']['proxy'] = $proxy;
        }

        $context = stream_context_create($options);

        return new FtpContext($context);
    }

    /**
     * @param int|null $compression
     * @param string|null $bootstrap
     * @param mixed[]|null $metadata
     * @return PharContext
     */
    public static function createPhar(?int $compression = null, ?string $bootstrap = null, ?array $metadata = null): PharContext
    {
        $options = [
            'phar' => [],
        ];

        if ($compression !== null) {
            $options['phar']['compress'] = $compression;
        }

        if ($metadata !== null && $bootstrap !== null) {
            $metadata['bootstrap'] = $bootstrap;
        } elseif ($bootstrap !== null) {
            $metadata = ['bootstrap' => $bootstrap];
        }

        if ($metadata !== null) {
            $options['metadata'] = $metadata;
        }

        return new PharContext(stream_context_create($options));
    }

    public static function createZip(string $password): ZipContext
    {
        $options = [
            'zip' => [
                'password' => $password,
            ],
        ];

        return new ZipContext(stream_context_create($options));
    }

    public static function createSocket(string $address, ?int $port = null, bool $noDelay = false): SocketContext
    {
        if (strpos($address, ':') !== false && $port !== null) {
            $address = "[$address]:$port";
        } elseif ($port !== null) {
            $address = "$address:$port";
        }

        $options = [
            'socket' => [
                'bindto' => $address,
                'tcp_nodelay' => $noDelay,
            ],
        ];

        return new SocketContext(stream_context_create($options));
    }

    // options ---------------------------------------------------------------------------------------------------------

    /**
     * @param string $wrapper
     * @param string $option
     * @param string|int $value
     * @return self
     */
    public function setOption(string $wrapper, string $option, $value): self
    {
        error_clear_last();
        $res = @stream_context_set_option($this->context, $wrapper, $option, $value);
        if ($res === false) {
            $value = ExceptionValueFormatter::format($value);
            throw new IoException("Cannot set stream option $option with value $value on wrapper $wrapper: " . error_get_last()['message']);
        }

        return $this;
    }

    // events ----------------------------------------------------------------------------------------------------------

    /**
     * Callback params: (StreamContext $this, int $event, int $severity, string $message, int $messageCode, int $bytesTransferred, int $bytesMax): void
     *
     * @param callable $callback
     * @return self
     */
    public function setCallback(callable $callback): self
    {
        if ($this->callbacks === []) {
            $this->initCallbacks();
        }

        $this->callbacks[null] = $callback;

        return $this;
    }

    public function onResolved(callable $callback): self
    {
        if ($this->callbacks === []) {
            $this->initCallbacks();
        }

        $this->callbacks[StreamEvent::RESOLVED] = $callback;

        return $this;
    }

    public function onConnected(callable $callback): self
    {
        if ($this->callbacks === []) {
            $this->initCallbacks();
        }

        $this->callbacks[StreamEvent::CONNECTED] = $callback;

        return $this;
    }

    public function onAuthRequired(callable $callback): self
    {
        if ($this->callbacks === []) {
            $this->initCallbacks();
        }

        $this->callbacks[StreamEvent::AUTH_REQUIRED] = $callback;

        return $this;
    }

    public function onAuthResult(callable $callback): self
    {
        if ($this->callbacks === []) {
            $this->initCallbacks();
        }

        $this->callbacks[StreamEvent::AUTH_RESULT] = $callback;

        return $this;
    }

    public function onRedirect(callable $callback): self
    {
        if ($this->callbacks === []) {
            $this->initCallbacks();
        }

        $this->callbacks[StreamEvent::REDIRECTED] = $callback;

        return $this;
    }

    public function onMimeType(callable $callback): self
    {
        if ($this->callbacks === []) {
            $this->initCallbacks();
        }

        $this->callbacks[StreamEvent::MIME_TYPE] = $callback;

        return $this;
    }

    public function onFileSize(callable $callback): self
    {
        if ($this->callbacks === []) {
            $this->initCallbacks();
        }

        $this->callbacks[StreamEvent::FILE_SIZE] = $callback;

        return $this;
    }

    public function onProgress(callable $callback): self
    {
        if ($this->callbacks === []) {
            $this->initCallbacks();
        }

        $this->callbacks[StreamEvent::PROGRESS] = $callback;

        return $this;
    }

    public function onCompleted(callable $callback): self
    {
        if ($this->callbacks === []) {
            $this->initCallbacks();
        }

        $this->callbacks[StreamEvent::COMPLETED] = $callback;

        return $this;
    }

    public function onFailure(callable $callback): self
    {
        if ($this->callbacks === []) {
            $this->initCallbacks();
        }

        $this->callbacks[StreamEvent::FAILURE] = $callback;

        return $this;
    }

    private function initCallbacks(): void
    {
        stream_context_set_params($this->context, [
            'notification' => function (int $event, int $severity, ?string $message, int $messageCode, int $bytesTransferred, int $bytesMax): void {
                $callback = $this->callbacks[null] ?? null;
                if ($callback !== null) {
                    $callback($this, $event, $severity, $message, $messageCode, $bytesTransferred, $bytesMax);
                }

                $callback = $this->callbacks[$event] ?? null;
                if ($callback === null) {
                    return;
                }
                switch ($event) {
                    case StreamEvent::RESOLVED:
                        $callback($this, $message, $messageCode);
                        break;
                    case StreamEvent::CONNECTED:
                        $callback($this, $message, $messageCode);
                        break;
                    case StreamEvent::AUTH_REQUIRED:
                        $callback($this, $message, $messageCode);
                        break;
                    case StreamEvent::AUTH_RESULT:
                        $callback($this, $message, $messageCode);
                        break;
                    case StreamEvent::REDIRECTED:
                        $callback($this, $message, $messageCode);
                        break;
                    case StreamEvent::MIME_TYPE:
                        $callback($this, $message, $messageCode);
                        break;
                    case StreamEvent::FAILURE:
                        $callback($this, $message, $messageCode);
                        break;
                    case StreamEvent::FILE_SIZE:
                        $callback($this, $bytesTransferred, $bytesMax);
                        break;
                    case StreamEvent::PROGRESS:
                        $callback($this, $bytesTransferred, $bytesMax);
                        break;
                    case StreamEvent::COMPLETED:
                        $callback($this, $bytesTransferred, $bytesMax);
                        break;
                }
            },
        ]);
    }

}
