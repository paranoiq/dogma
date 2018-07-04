<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Io;

use Dogma\Language\Encoding;
use function error_clear_last;
use function error_get_last;
use function fgetcsv;
use function fgets;
use function fputcsv;
use function iconv;

/**
 * Text file reader/writer
 */
class TextFile extends File
{

    /** @var string */
    private $internalEncoding = Encoding::UTF_8;

    /** @var string */
    private $encoding = Encoding::UTF_8;

    /** @var string */
    private $nl = LineEndings::UNIX;

    /**
     * @param string|resource $file
     * @param string $mode
     * @param resource|null $streamContext
     * @param \Dogma\Language\Encoding $encoding
     * @param \Dogma\Io\LineEndings $lineEndings
     */
    public function __construct($file, string $mode = FileMode::OPEN_READ, $streamContext = null, ?Encoding $encoding = null, ?LineEndings $lineEndings = null)
    {
        parent::__construct($file, $mode, $streamContext);

        if ($encoding !== null) {
            $this->setEncoding($encoding);
        }
        if ($lineEndings !== null) {
            $this->setLineEndings($lineEndings);
        }
    }

    public function setEncoding(Encoding $encoding): void
    {
        $this->encoding = $encoding->getValue();
    }

    public function setInternalEncoding(Encoding $internalEncoding): void
    {
        $this->internalEncoding = $internalEncoding->getValue();
    }

    public function setLineEndings(LineEndings $nl): void
    {
        $this->nl = $nl->getValue();
    }

    public function readLine(): ?string
    {
        error_clear_last();
        $line = fgets($this->handle);

        if ($line === false) {
            if ($this->endOfFileReached()) {
                return null;
            } else {
                throw new FileException('Cannot read data from file.', error_get_last());
            }
        }
        if ($this->encoding !== $this->internalEncoding) {
            $line = $this->decode($line);
        }
        return $line;
    }

    public function writeLine(string $line): void
    {
        if ($this->encoding !== $this->internalEncoding) {
            $line = $this->encode($line);
        }
        $this->write($line . $this->nl);
    }

    /**
     * @param string $delimiter
     * @param string $quoteChar
     * @param string $escapeChar
     * @return string[]
     */
    public function readCsvRow(string $delimiter, string $quoteChar, string $escapeChar): array
    {
        error_clear_last();
        $row = fgetcsv($this->handle, 0, $delimiter, $quoteChar, $escapeChar);

        if ($row === false) {
            if ($this->endOfFileReached()) {
                return [];
            } else {
                throw new FileException('Cannot read data from file.', error_get_last());
            }
        }

        if ($this->encoding !== $this->internalEncoding) {
            foreach ($row as &$item) {
                $item = $this->decode($item);
            }
        }

        return $row;
    }

    /**
     * @param string[] $row
     * @param string $delimiter
     * @param string $quoteChar
     * @return int
     */
    public function writeCsvRow(array $row, string $delimiter, string $quoteChar): int
    {
        if ($this->encoding !== $this->internalEncoding) {
            foreach ($row as &$item) {
                $item = $this->encode($item);
            }
        }

        error_clear_last();
        $written = fputcsv($this->handle, $row, $delimiter, $quoteChar);

        if ($written === false) {
            throw new FileException('Cannot write CSV row', error_get_last());
        }

        return $written;
    }

    private function encode(string $string): string
    {
        error_clear_last();
        $result = iconv($this->encoding, $this->internalEncoding, $string);

        if ($result === false) {
            throw new FileException('Cannot convert file encoding.', error_get_last());
        }

        return $result;
    }

    private function decode(string $string): string
    {
        error_clear_last();
        $result = iconv($this->internalEncoding, $this->encoding, $string);

        if ($result === false) {
            throw new FileException('Cannot convert file encoding.', error_get_last());
        }

        return $result;
    }

}
