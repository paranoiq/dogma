<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Io;

use Dogma\InvalidArgumentException;
use Dogma\Language\Cp437;
use Dogma\Language\Encoding;
use Dogma\LineEnding;
use Dogma\LogicException;
use Dogma\ResourceType;
use Dogma\Str;
use StreamContext;
use function rd;
use function tmpfile;
use const PHP_INT_MAX;
use function error_clear_last;
use function error_get_last;
use function fgetcsv;
use function fputcsv;
use function fread;
use function ftruncate;
use function fwrite;
use function get_resource_type;
use function implode;
use function is_resource;
use function is_string;
use function mb_strlen;
use function mb_substr;
use function str_replace;
use function stream_get_line;
use function strlen;

/**
 * An open file in "text mode"
 * All length arguments are in characters or lines
 * All methods translate strings between internal/application encoding and external/file encoding
 */
class TextFile extends File
{

    /** @var string */
    private $encoding = Encoding::UTF_8;

    /** @var string in ASCII/UTF-8 encoding */
    private $eol = LineEnding::LF;

    /** @var string in file encoding */
    private $eolEncoded = LineEnding::LF;

    /** @var string */
    private $buffer = '';

    /**
     * @param string|resource $file
     * @param resource|null $context
     */
    public function __construct(
        $file,
        string $mode = FileMode::OPEN_READ,
        ?StreamContext $context = null,
        ?string $encoding = null,
        ?string $lineEndings = null
    ) {
        if (is_resource($file) && get_resource_type($file) === ResourceType::STREAM) {
            $this->handle = $file;
            $this->mode = $mode;
            return;
        } elseif (is_string($file)) {
            $this->path = Io::normalizePath($file);
        } elseif ($file instanceof Path) {
            $this->path = $file->getPath();
        } else {
            throw new InvalidArgumentException('Argument $file must be a file path or a stream resource.');
        }

        $this->mode = $mode;
        $this->context = $context;

        if ($this->handle === null) {
            $this->reopen();
        }

        if ($encoding !== null) {
            Encoding::checkValue($encoding);

            $this->encoding = $encoding;
        }

        if ($lineEndings !== null) {
            $this->setLineEndings($lineEndings);
        }
    }

    /**
     * @return static
     */
    public static function createTemporaryFile(): self
    {
        error_clear_last();
        /** @var resource|false $handle */
        $handle = tmpfile();

        if ($handle === false) {
            throw FilesystemException::create("Cannot create a temporary file", null, null, error_get_last());
        }

        return new static($handle, FileMode::CREATE_OR_TRUNCATE_READ_WRITE);
    }

    public static function createMemoryFile(?int $maxSize = null): self
    {
        if ($maxSize === null) {
            return new static('php://memory', FileMode::CREATE_OR_TRUNCATE_READ_WRITE);
        } else {
            return new static("php://temp/maxmemory:$maxSize", FileMode::CREATE_OR_TRUNCATE_READ_WRITE);
        }
    }

    public function toBinaryFile(): BinaryFile
    {
        return new BinaryFile($this->getHandle(), $this->mode, $this->context);
    }

    // encoding and format ---------------------------------------------------------------------------------------------

    public function setEncoding(string $encoding): void
    {
        Encoding::checkValue($encoding);

        $this->encoding = $encoding;
    }

    public function getEncoding(): string
    {
        return $this->encoding;
    }

    public function convertEncoding(string $encoding): void
    {
        Encoding::checkValue($encoding);

        $text = $this->getContents();

        $text = Str::convertEncoding($text, $this->encoding, $encoding);

        $this->truncate();
        $this->writeBinary($text);
        $this->encoding = $encoding;
    }

    public function setLineEndings(string $lineEndings): void
    {
        LineEnding::checkValue($lineEndings);

        $this->eol = $lineEndings;
        $this->eolEncoded = Encoding::isSupersetOfAscii($this->encoding)
            ? $lineEndings
            : $this->encode($lineEndings);
    }

    public function getLineEndings(): string
    {
        return $this->eol;
    }

    public function convertLineEndings(string $lineEndings): void
    {
        LineEnding::checkValue($lineEndings);

        $text = $this->getContents();

        // to cope with multibyte encodings and endian
        if ($this->encoding !== Encoding::UTF_8) {
            $text = Str::convertEncoding($text, $this->encoding, Encoding::UTF_8);
        }

        $text = str_replace($this->eol, $lineEndings, $text);

        if ($this->encoding !== Encoding::UTF_8) {
            $text = Str::convertEncoding($text, Encoding::UTF_8, $this->encoding);
        }

        $this->truncate();
        $this->writeBinary($text);
        $this->setLineEndings($lineEndings);
    }

    // read/write ------------------------------------------------------------------------------------------------------

    public function getContents(): string
    {
        if ($this->getPosition()) {
            $this->setPosition(0);
        }

        $results = [];
        while (!$this->endOfFileReached()) {
            $results[] = $this->readBinary();
        }

        return implode('', $results);
    }

    public function read(?int $characters = null): string
    {
        $characters = $characters ?? self::$defaultChunkSize;
        $multiplier = Encoding::minLength($this->encoding);

        $bufferLength = mb_strlen($this->buffer, $this->encoding);
        $readChars = $characters - $bufferLength;
        if ($readChars > 0) {
            $readBytes = $readChars * $multiplier * 2;
            do {
                $chunk = $this->readBinary($readBytes);
                $this->buffer .= $chunk;
                $bufferLength = mb_strlen($this->buffer);
                if (strlen($chunk) < $readBytes) {
                    // eof
                    break;
                }
                $readChars = $characters - $bufferLength;
                $readBytes = $readChars * $multiplier * 2;
            } while ($bufferLength < $characters);
        }

        if ($bufferLength < $characters) {
            $this->buffer = '';

            return $this->buffer;
        } else {
            $return = mb_substr($this->buffer, 0, $characters, $this->encoding);
            $this->buffer = mb_substr($this->buffer, $characters, PHP_INT_MAX, $this->encoding);

            return $return;
        }
    }

    public function write(string $data, ?int $characters = null): void
    {
        if ($characters !== null) {
            $data = mb_substr($data, 0, $characters, Encoding::UTF_8);
        }

        if ($this->encoding !== Encoding::UTF_8) {
            $data = $this->encode($data);
        }

        $this->writeBinary($data);
    }

    public function readLine(): ?string
    {
        if (!FileMode::isReadable($this->mode)) {
            throw new LogicException('Cannot read - file opened in write only mode.');
        }

        error_clear_last();
        // todo: stream_get_line()
        $line = @stream_get_line($this->getHandle(), 0, $this->eolEncoded);
        //$line = @fgets($this->getHandle());

        if ($line === false) {
            if ($this->endOfFileReached()) {
                return null;
            } else {
                throw FilesystemException::create('Cannot read data from file', $this->path, $this->context, error_get_last());
            }
        }

        if ($this->encoding !== Encoding::UTF_8) {
            $line = $this->decode($line);
        }

        return $line;
    }

    public function writeLine(string $line): void
    {
        if ($this->encoding !== Encoding::UTF_8) {
            $line = $this->encode($line . $this->eol);
        } else {
            $line .= $this->eolEncoded;
        }

        $this->writeBinary($line);
    }

    /**
     * @return string[]
     */
    public function readLines(?int $count = null): array
    {
        if (!FileMode::isReadable($this->mode)) {
            throw new LogicException('Cannot read - file opened in write only mode.');
        }

        $count = $count ?? PHP_INT_MAX;
        $lines = [];
        $n = 0;
        do {
            $line = $this->readLine();
            if ($line === null) {
                // eof
                break;
            }
            $lines[] = $line;
            $n++;
        } while ($n < $count);

        return $lines;
    }

    /**
     * @param string[] $lines
     */
    public function writeLines(array $lines): void
    {
        foreach ($lines as $line) {
            $this->writeLine($line);
        }
    }

    /**
     * Truncate file and move pointer at the end
     * @param int $characters
     */
    public function truncate(int $characters = 0): void
    {
        if ($characters === 0) {
            $this->truncateBinary(0);
        }

        $text = $this->read($characters);
        $this->truncateBinary(strlen($text));
    }

    /**
     * Truncate file and move pointer at the end
     * @param int $lines
     */
    public function truncateLines(int $lines = 0): void
    {
        if ($lines === 0) {
            $this->truncateBinary(0);
        }

        $text = implode($this->eolEncoded, $this->readLines($lines));
        $this->truncateBinary(strlen($text));
    }

    private function readBinary(?int $bytes = null): ?string
    {
        $bytes = $bytes ?? self::$defaultChunkSize;

        if (!FileMode::isReadable($this->mode)) {
            throw new LogicException('Cannot read - file opened in write only mode.');
        }

        error_clear_last();
        $data = @fread($this->getHandle(), $bytes);

        if ($data === false) {
            if ($this->endOfFileReached()) {
                throw FilesystemException::create("Cannot read from file, end of file was reached", $this->path, $this->context, error_get_last());
            } else {
                throw FilesystemException::create("Cannot read from file", $this->path, $this->context, error_get_last());
            }
        } elseif ($data === '') {

        }

        return $data === '' ? null : $data;
    }

    private function writeBinary(string $data, ?int $bytes = null): void
    {
        error_clear_last();
        if ($bytes !== null) {
            $result = @fwrite($this->getHandle(), $data, $bytes);
        } else {
            $result = @fwrite($this->getHandle(), $data);
        }

        if ($result === false) {
            throw FilesystemException::create("Cannot write to file", $this->path, $this->context, error_get_last());
        }
    }

    /**
     * Truncate file and move pointer at the end
     * @param int $size
     */
    private function truncateBinary(int $size = 0): void
    {
        error_clear_last();
        $result = @ftruncate($this->getHandle(), $size);

        if ($result === false) {
            throw FilesystemException::create("Cannot truncate file", $this->path, $this->context, error_get_last());
        }

        $this->setPosition($size);
    }

    // CSV https://tools.ietf.org/html/rfc4180 -------------------------------------------------------------------------

    /**
     * @param string $delimiter
     * @param string $quoteChar
     * @param string $escapeChar
     * @return string[]|null[]
>>>>>>> 00d7609 (WIP)
     */
    public function readCsvRow(string $delimiter = ',', string $quoteChar = '"', string $escapeChar = '"'): array
    {
        if ($this->encoding !== Encoding::UTF_8) {
            $delimiter = $this->encode($delimiter);
            $quoteChar = $this->encode($quoteChar);
            $escapeChar = $this->encode($escapeChar);
        }

        error_clear_last();
        $row = @fgetcsv($this->getHandle(), 0, $delimiter, $quoteChar, $escapeChar);

        if ($row === false || $row === null) {
            if ($this->endOfFileReached()) {
                return [];
            } else {
                throw FilesystemException::create('Cannot read data from file.', $this->path, $this->context, error_get_last());
            }
        }

        if ($this->encoding !== Encoding::UTF_8) {
            foreach ($row as &$item) {
                $item = $this->decode($item);
            }
        }

        return $row;
    }

    /**
     * @param string[] $row
     */
    public function writeCsvRow(array $row, string $delimiter = ',', string $quoteChar = '"', string $escapeChar = '"'): int
    {
        if ($this->encoding !== Encoding::UTF_8) {
            $delimiter = $this->encode($delimiter);
            $quoteChar = $this->encode($quoteChar);
            $escapeChar = $this->encode($escapeChar);
        }

        if ($this->encoding !== Encoding::UTF_8) {
            foreach ($row as $i => $item) {
                $row[$i] = $this->encode((string) $item);
            }
        }

        error_clear_last();
        $written = @fputcsv($this->getHandle(), $row, $delimiter, $quoteChar, $escapeChar);

        if ($written === false) {
            throw FilesystemException::create('Cannot write CSV row', $this->path, $this->context, error_get_last());
        }

        return $written;
    }

    // helpers ---------------------------------------------------------------------------------------------------------

    private function encode(string $string): string
    {
        return Str::convertEncoding($string, Encoding::UTF_8, $this->encoding);
    }

    private function decode(string $string): string
    {
        return Str::convertEncoding($string, $this->encoding, Encoding::UTF_8);
    }

}
