<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Io;

use Dogma\ShouldNotHappenException;
use Dogma\StrictBehaviorMixin;
use Dogma\System\Os;
use function error_clear_last;
use function error_get_last;
use function exec;
use function implode;
use function proc_close;
use function proc_get_status;
use function proc_open;
use function proc_terminate;
use function stream_get_contents;
use function stream_set_blocking;

class Process
{
    use StrictBehaviorMixin;

    public const STDIN = 0;
    public const STDOUT = 1;
    public const STDERR = 2;

    /** @var string[][] */
    private $descriptors;

    /** @var resource|null */
    private $process;

    /** @var resource[] */
    private $pipes;

    /** @var File[] */
    private $handlers = [];

    private function __construct()
    {
        // use open()
    }

    public function __destruct()
    {
        $this->close();
    }

    /**
     * Run command and return result code and output
     *
     * @param string $command
     * @return int[]|string[] (int $code, string $output)
     */
    public static function run(string $command): array
    {
        $output = [];
        exec($command, $output, $resultCode);

        return [$resultCode, implode("\n", $output)];
    }

    /**
     * @param string $command
     * @param string[][]|resource[] $descriptors
     * @param string|Path|null $cwd
     * @param mixed[] $env
     * @param bool[] $options
     * @return self
     */
    public static function open(string $command, array $descriptors = [], $cwd = null, array $env = [], array $options = []): self
    {
        $descriptors += [
            self::STDIN => ['pipe', FileMode::CREATE_OR_TRUNCATE_WRITE],
            self::STDOUT => ['pipe', FileMode::OPEN_READ],
            self::STDERR => ['pipe', FileMode::OPEN_READ],
        ];
        if ($cwd instanceof Path) {
            $cwd = $cwd->getPath();
        }

        error_clear_last();
        $process = @proc_open($command, $descriptors, $pipes, $cwd, $env, $options);
        if ($process === false) {
            throw new IoException('Cannot open process: ' . error_get_last()['message']);
        }
        stream_set_blocking($pipes[self::STDERR], false);

        $self = new self();
        $self->descriptors = $descriptors;
        $self->process = $process;
        $self->pipes = $pipes;

        return $self;
    }

    public function isClosed(): bool
    {
        return $this->process === null;
    }

    public function close(): int
    {
        if ($this->isClosed()) {
            throw new ShouldNotHappenException('Process is already closed.');
        }

        foreach ($this->handlers as $handler) {
            $handler->close();
        }
        $result = @proc_close($this->process);
        $this->process = null;

        return $result;
    }

    public function terminate(int $signal = 15 /* SIGTERM */): bool
    {
        if (Os::isWindows()) {
            return !$this->close();
        }
        if ($this->isClosed()) {
            throw new ShouldNotHappenException('Process is already closed.');
        }

        foreach ($this->handlers as $handler) {
            $handler->close();
        }
        $result = @proc_terminate($this->process, $signal);
        $this->process = null;

        return $result;
    }

    /**
     * Call before calling isRunning() if you do not care about output data, because unread data in full buffers
     * may prevent the process from terminating until it writes its outputs.
     */
    public function cleanOutputBuffers(): void
    {
        if ($this->isClosed()) {
            throw new ShouldNotHappenException('Process is already closed.');
        }

        foreach ($this->descriptors as $descriptor => [$type, $mode]) {
            if ($type !== 'pipe' || $mode !== FileMode::OPEN_READ) {
                continue;
            }
            @stream_get_contents($this->pipes[$descriptor]);
        }
    }

    /**
     * @return string[]|int[]|bool[]
     */
    public function getInfo(): array
    {
        if ($this->isClosed()) {
            throw new ShouldNotHappenException('Process is already closed.');
        }
        error_clear_last();
        $info = @proc_get_status($this->process);
        if ($info === false) {
            throw new IoException('Cannot get process info: ' . error_get_last()['message']);
        }

        return $info;
    }

    public function getCommand(): string
    {
        return $this->getInfo()['command'];
    }

    public function getPid(): int
    {
        return $this->getInfo()['pid'];
    }

    public function isRunning(): bool
    {
        return $this->getInfo()['running'];
    }

    public function wasTerminated(): bool
    {
        return $this->getInfo()['signaled'];
    }

    public function getTerminateSignal(): int
    {
        return $this->getInfo()['termsig'];
    }

    public function wasStopped(): bool
    {
        return $this->getInfo()['stopped'];
    }

    public function getStopSignal(): int
    {
        return $this->getInfo()['stopsig'];
    }

    public function getExitCode(): int
    {
        return $this->getInfo()['exitcode'];
    }

    public function getInput(bool $textMode = false, ?string $encoding = null, ?string $lineEndings = null): File
    {
        return $this->getHandler(self::STDIN, $textMode, $encoding, $lineEndings);
    }

    public function getOutput(bool $textMode = false, ?string $encoding = null, ?string $lineEndings = null): File
    {
        return $this->getHandler(self::STDOUT, $textMode, $encoding, $lineEndings);
    }

    public function getErrorOutput(bool $textMode = false, ?string $encoding = null, ?string $lineEndings = null): File
    {
        return $this->getHandler(self::STDERR, $textMode, $encoding, $lineEndings);
    }

    public function getHandler(int $descriptor, bool $textMode = false, ?string $encoding = null, ?string $lineEndings = null): File
    {
        if (!isset($this->handlers[$descriptor])) {
            $this->handlers[$descriptor] = $textMode
                ? new TextFile($this->pipes[$descriptor], $this->descriptors[$descriptor][1], null, $encoding, $lineEndings)
                : new BinaryFile($this->pipes[$descriptor], $this->descriptors[$descriptor][1]);
        }

        return $this->handlers[$descriptor];
    }

}
