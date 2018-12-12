<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Email;

use Dogma\Io\File;
use Dogma\Language\Inflector;
use Dogma\PowersOfTwo;
use Dogma\Str;
use Dogma\StrictBehaviorMixin;
use Dogma\Time\DateTime;
use function array_key_exists;
use function array_values;
use function base64_decode;
use function call_user_func;
use function explode;
use function iconv;
use function in_array;
use function is_array;
use function mailparse_msg_create;
use function mailparse_msg_free;
use function mailparse_msg_get_part;
use function mailparse_msg_get_part_data;
use function mailparse_msg_get_structure;
use function mailparse_msg_parse;
use function mailparse_msg_parse_file;
use function mailparse_rfc822_parse_addresses;
use function quoted_printable_decode;
use function sprintf;
use function strpos;
use function strtolower;
use function substr;

/**
 * Mime mail parser. Parses mail message from a File or string.
 * Use -> to read message headers.
 *
 * @property-read string $subject
 * @property-read \DateTime $date
 *
 * @property-read string $from
 * @property-read string $to
 * @property-read string $cc
 * @property-read string $replyTo
 *
 * @property-read string $messageId
 * @property-read string $inReplyTo
 */
class EmailMessage
{
    use StrictBehaviorMixin;

    public const TEXT = 'text/plain';
    public const HTML = 'text/html';

    /** @var int bigger attachments will be treated using temporary files */
    public static $bigFileThreshold = PowersOfTwo::_1M;

    /** @var string[] */
    private $parts = [];

    /** @var string[] */
    private $headers;

    /** @var \Dogma\Io\File */
    private $file;

    /** @var string */
    private $data;

    /** @var callable */
    private $addressFactory;

    /**
     * @param string|\Dogma\Io\File $message
     */
    public function __construct($message)
    {
        if ($message instanceof File) {
            $this->file = $message;
            ///
            $handler = mailparse_msg_parse_file($this->file->getPath());
            if (!$handler) {
                throw new EmailParsingException('Cannot parse email file.');
            }
        } else {
            ///
            $handler = mailparse_msg_create();
            $res = mailparse_msg_parse($handler, $message);
            if (!$handler || !$res) {
                throw new EmailParsingException('Cannot parse email message.');
            }
            $this->data = $message;
        }

        ///
        $structure = mailparse_msg_get_structure($handler);
        if (!$structure) {
            throw new EmailParsingException('Cannot parse email structure.');
        }

        $this->parts = [];
        foreach ($structure as $partId) {
            ///
            $partHandler = mailparse_msg_get_part($handler, $partId);
            $partData = mailparse_msg_get_part_data($partHandler);
            if (!$partHandler || !$partData) {
                throw new EmailParsingException('Cannot get email part data.');
            }
            $this->parts[$partId] = $partData;
        }

        mailparse_msg_free($handler);
    }

    public function setAddressFactory(callable $factory): void
    {
        $this->addressFactory = $factory;
    }

    /**
     * Returns all email headers.
     * @return string[]
     */
    public function getHeaders(): array
    {
        if (!$this->headers) {
            $this->headers = $this->parts[1]['headers'];
            $this->decodeHeaders($this->headers);
        }

        return $this->headers;
    }

    public function getHeader(string $name): ?string
    {
        if (!$this->headers) {
            $this->getHeaders();
        }

        if (isset($this->headers[$name])) {
            return $this->headers[$name];
        }

        return null;
    }

    /**
     * Return content types of body (usually text/plain and text/html).
     * @return string[]
     */
    public function getContentTypes(): array
    {
        $ct = [];
        foreach ($this->parts as $part) {
            if (isset($part['content-disposition'])) {
                continue;
            }
            if (substr($part['content-type'], 0, 9) === 'multipart') {
                continue;
            }
        }

        return $ct;
    }

    public function getBody(string $type = self::TEXT): ?string
    {
        if ($type !== 'text/plain' && $type !== 'text/html') {
            throw new EmailParsingException('Invalid content type specified. Type can either be "text/plain" or "text/html".');
        }

        foreach ($this->parts as $part) {
            if ($type !== $part['content-type']) {
                continue;
            }
            if (isset($part['content-disposition'])) {
                continue;
            }

            return $this->decode($this->getPartBody($part), @$part['headers']['content-transfer-encoding'] ?: '');
        }

        return null;
    }

    /**
     * Get the headers for the message body part.
     * @param string $type
     * @return string[]
     */
    public function getBodyHeaders(string $type = self::TEXT): array
    {
        if ($type !== 'text/plain' && $type !== 'text/html') {
            throw new EmailParsingException('Invalid content type specified. Type can either be "text/plain" or "text/html".');
        }

        foreach ($this->parts as $part) {
            if ($type !== $part['content-type']) {
                continue;
            }

            return @$part['headers'] ?: [];
        }

        return [];
    }

    /**
     * Returns attachments. May be filtered by mime type.
     * @param string|string[] $contentType
     * @param bool $inlined
     * @return \Dogma\Email\EmailAttachment[]
     */
    public function getAttachments($contentType = null, bool $inlined = true): array
    {
        $dispositions = $inlined ? ['attachment', 'inline'] : ['attachment'];
        if (isset($contentType) && !is_array($contentType)) {
            $contentType = [$contentType];
        }

        $attachments = [];
        foreach ($this->parts as $part) {
            if (!in_array(@$part['content-disposition'], $dispositions)) {
                continue; // only on attachments
            }
            if ($contentType && !in_array($part['content-type'], $contentType)) {
                continue;
            }

            $attachments[] = new EmailAttachment(
                $this->getAttachmentData($part),
                $this->getParsedPartHeaders($part)
            );
        }

        return $attachments;
    }

    /**
     * @param string $name
     * @return string|\DateTime
     */
    public function &__get(string $name)
    {
        if (!$this->headers) {
            $this->getHeaders();
        }

        $name = Inflector::dasherize(Inflector::underscore($name));

        return $this->headers[$name] ?? $this->headers['x-' . $name] ?? null;
    }

    // internals -------------------------------------------------------------------------------------------------------

    private function decode(string $data, string $encoding): string
    {
        if (strtolower($encoding) === 'base64') {
            return base64_decode($data);
        } elseif (strtolower($encoding) === 'quoted-printable') {
            return quoted_printable_decode($data);
        } elseif (!$encoding) {
            return $data;
        } else {
            throw new EmailParsingException('Unknown transfer encoding.');
        }
    }

    /**
     * Find and decode encoded headers (format: =?charset?te?header?=)
     * @param string[]|string[][] $headers
     */
    private function decodeHeaders(array &$headers): void
    {
        foreach ($headers as $name => &$value) {
            if (is_array($value)) {
                $this->decodeHeaders($value);
                continue;
            }

            if (in_array($name, ['date', 'resent-date', 'delivery-date', 'expires'], true)) {
                $value = new DateTime($value);
            } elseif (in_array($name, ['from', 'to', 'cc', 'bcc', 'reply-to', 'return-path', 'sender'], true)) {
                $value = $this->parseAddressHeader($value);
            } elseif (strpos($value, '=?') !== false) {
                $value = $this->decodeHeader($value);
            }
        }
    }

    /**
     * Parse addresses from mail header (from, to, cc, bcc, reply-to, return-path, delivered-to, sender…)
     * @param string $header
     * @return \Dogma\Email\EmailAddress[]
     */
    private function parseAddressHeader(string $header): array
    {
        $data = mailparse_rfc822_parse_addresses($header);

        $arr = [];
        foreach ($data as $item) {
            [$name, $address] = array_values($item);

            $name = $address === $name ? null
                : (strpos($name, '=?') !== false ? $this->decodeHeader($name) : $name);
            $arr[] = call_user_func($this->addressFactory, $address, $name);
        }

        return $arr;
    }

    private function decodeHeader(string $header): string
    {
        // =?utf-8?q?Test=3a=20P=c5=99=c3=...?=
        $header = Str::replace($header, '/(=\\?[^?]+\\?[^?]\\?[^?]+\\?=)/', function ($match) {
            [, $charset, $encoding, $message] = explode('?', $match[0]);

            if (strtolower($encoding) === 'b') {
                $message = base64_decode($message);
            } elseif (strtolower($encoding) === 'q') {
                $message = quoted_printable_decode($message);
            } else {
                throw new EmailParsingException(sprintf('Unknown header encoding \'%s\'.', $encoding));
            }

            return $this->convertCharset($message, strtolower($charset));
        });

        return $header;
    }

    private static function convertCharset(string $string, string $charset): string
    {
        if ($charset === 'utf-8') {
            return $string;
        }

        return iconv('utf-8', $charset, $string);
    }

    /**
     * @param string[] $part
     * @return string[]
     */
    private function getParsedPartHeaders(array $part): array
    {
        $headers = $part;
        unset($headers['headers']);
        unset($headers['starting-pos']);
        unset($headers['starting-pos-body']);
        unset($headers['ending-pos']);
        unset($headers['ending-pos-body']);
        unset($headers['line-count']);
        unset($headers['body-line-count']);
        unset($headers['charset']);
        unset($headers['transfer-encoding']);
        unset($headers['content-base']);
        return $headers;
    }

    /**
     * @param string[] $part
     * @return string|null
     */
    private function getPartBody(array $part): ?string
    {
        $start = (int) $part['starting-pos-body'];
        $length = (int) $part['ending-pos-body'] - $start;

        if ($this->data) {
            return substr($this->data, $start, $length);
        } else {
            $this->file->setPosition($start);

            return $this->file->read($length);
        }
    }

    /**
     * Get attachment data as string or temporary File object.
     * @param string[] $part
     * @return string|\Dogma\Io\File
     */
    private function getAttachmentData(array $part)
    {
        $encoding = array_key_exists('content-transfer-encoding', $part['headers']) ? $part['headers']['content-transfer-encoding'] : '';

        if ($this->data) {
            return $this->decode($this->getPartBody($part), $encoding);
        } else {
            $start = (int) $part['starting-pos-body'];
            $length = (int) $part['ending-pos-body'] - $start;
            $this->file->setPosition($start);

            if ($length < self::$bigFileThreshold) {
                return $this->decode($this->file->read($length), $encoding);
            } else {
                $temporaryFile = File::createTemporaryFile();

                $this->file->copyData(function ($chunk) use ($temporaryFile, $encoding): void {
                    $temporaryFile->write($this->decode($chunk, $encoding));
                }, $start, $length);

                $temporaryFile->setPosition(0);

                return $temporaryFile;
            }
        }
    }

}
