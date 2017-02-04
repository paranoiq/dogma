<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Mail;

use Dogma\Io\File;
use Dogma\Language\Inflector;
use Dogma\Str;

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
class Message
{
    use \Dogma\StrictBehaviorMixin;

    const TEXT = 'text/plain';
    const HTML = 'text/html';

    /** @var int bigger attachements will be treated using temporary files */
    public static $bigFileTreshold = 0x100000; // 1MB

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
     * @param string|\Dogma\Io\File
     */
    public function __construct($message)
    {
        if ($message instanceof File) {
            $this->file = $message;
            ///
            $handler = mailparse_msg_parse_file($this->file->getName());
            if (!$handler) {
                throw new ParsingException('Cannot parse email file.');
            }

        } else {
            ///
            $handler = mailparse_msg_create();
            $res = mailparse_msg_parse($handler, $message);
            if (!$handler || !$res) {
                throw new ParsingException('Cannot parse email message.');
            }
            $this->data = $message;
        }

        ///
        $structure = mailparse_msg_get_structure($handler);
        if (!$structure) {
            throw new ParsingException('Cannot parse email structure.');
        }

        $this->parts = [];
        foreach ($structure as $partId) {
            ///
            $partHandler = mailparse_msg_get_part($handler, $partId);
            $partData = mailparse_msg_get_part_data($partHandler);
            if (!$partHandler || !$partData) {
                throw new ParsingException('Cannot get email part data.');
            }
            $this->parts[$partId] = $partData;
        }

        mailparse_msg_free($handler);
    }

    public function setAddressFactory(callable $factory)
    {
        if (!is_callable($factory)) {
            throw new \InvalidArgumentException('Message factory must be callable.');
        }

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

    /**
     * Returns an email header.
     * @param string
     * @return string|null
     */
    public function getHeader(string $name)
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
     * Return content types of body (usualy text/plain and text/html).
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

    /**
     * Returns message body of given type.
     * @param string
     * @return string|null
     */
    public function getBody(string $type = self::TEXT)
    {
        if ($type !== 'text/plain' && $type !== 'text/html') {
            throw new ParsingException('Invalid content type specified. Type can either be "text/plain" or "text/html".');
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
     * @param string
     * @return string[]
     */
    public function getBodyHeaders(string $type = self::TEXT)
    {
        if ($type !== 'text/plain' && $type !== 'text/html') {
            throw new ParsingException('Invalid content type specified. Type can either be "text/plain" or "text/html".');
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
     * @param string|string[]
     * @param bool
     * @return \Dogma\Mail\Attachment[]
     */
    public function getAttachments($contentType = null, bool $inlines = true): array
    {
        $dispositions = $inlines ? ['attachment', 'inline'] : ['attachment'];
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

            $attachments[] = new Attachment(
                $this->getAttachmentData($part),
                $this->getParsedPartHeaders($part)
            );
        }

        return $attachments;
    }

    /**
     * @param string
     * @return string|\DateTime
     */
    public function &__get(string $name)
    {
        if (!$this->headers) {
            $this->getHeaders();
        }

        $name = Inflector::dasherize(Inflector::underscore($name));
        $val = isset($this->headers[$name]) ? $this->headers[$name]
            : (isset($this->headers['x-' . $name]) ? $this->headers['x-' . $name] : null);

        return $val;
    }

    // internals -------------------------------------------------------------------------------------------------------

    /**
     * Decode message part from transfer encoding.
     * @internal
     */
    public function decode(string $data, string $encoding): string
    {
        if (strtolower($encoding) === 'base64') {
            return base64_decode($data);

        } elseif (strtolower($encoding) === 'quoted-printable') {
            return quoted_printable_decode($data);

        } elseif (!$encoding) {
            return $data;

        } else {
            throw new ParsingException('Unknown transfer encoding.');
        }
    }

    /**
     * Find and decode encoded headers (format: =?charset?te?header?=)
     * @param string[]
     */
    private function decodeHeaders(array &$headers)
    {
        foreach ($headers as $name => &$value) {
            if (is_array($value)) {
                $this->decodeHeaders($value);
                continue;
            }

            //

            if (in_array($name, array('date', 'resent-date', 'delivery-date', 'expires'), true)) {
                $value = new \Dogma\Time\DateTime($value);

            } elseif (in_array($name, array('from', 'to', 'cc', 'bcc', 'reply-to', 'return-path', 'sender'), true)) {
                $value = $this->parseAddressHeader($value);

            } elseif (strpos($value, '=?') !== false) {
                $value = $this->decodeHeader($value);
            }
        }
    }

    /**
     * Parse addresses from mail header (from, to, cc, bcc, reply-to, return-path, delivered-to, sender…)
     * @param string
     * @return \Dogma\Mail\Address[]
     */
    private function parseAddressHeader(string $header): array
    {
        $data = mailparse_rfc822_parse_addresses($header);

        $arr = [];
        foreach ($data as $item) {
            list($name, $address, $group) = array_values($item);

            $name = $address === $name ? null
                : (strpos($name, '=?') !== false ? $this->decodeHeader($name) : $name);
            $arr[] = call_user_func($this->addressFactory, $address, $name);
        }

        return $arr;
    }

    private function createAddress(string $address, string $name): Address
    {
        return new Address($address, $name);
    }

    /**
     * Decode email header.
     */
    private function decodeHeader(string $header): string
    {
        // =?utf-8?q?Test=3a=20P=c5=99=c3=...?=
        $that = $this;
        $header = Str::replace($header, '/(=\\?[^?]+\\?[^?]\\?[^?]+\\?=)/', function ($match) use ($that) {
            list($x, $charset, $encoding, $message, $y) = explode('?', $match[0]);

            if (strtolower($encoding) === 'b') {
                $message = base64_decode($message);

            } elseif (strtolower($encoding) === 'q') {
                $message = quoted_printable_decode($message);

            } else {
                throw new ParsingException(sprintf('Unknown header encoding \'%s\'.', $encoding));
            }

            return $that->convertCharset($message, strtolower($charset));
        });

        return $header;
    }

    /**
     * @internal
     */
    public static function convertCharset(string $string, string $charset): string
    {
        if ($charset === 'utf-8') {
            return $string;
        }

        return iconv('utf-8', $charset, $string);
    }

    /**
     * @param string[]
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
     * @param string[]
     * @return string|null
     */
    private function getPartBody($part)
    {
        $start = $part['starting-pos-body'];
        $length = $part['ending-pos-body'] - $start;

        if ($this->data) {
            return substr($this->data, $start, $length);

        } else {
            $this->file->setPosition($start);
            return $this->file->read($length);
        }
    }

    /**
     * Get attachment data as string or temporary File object (see File::$bigFileTreshold).
     * @param string[]
     * @return string|\Dogma\Io\File
     */
    private function getAttachmentData($part)
    {
        $encoding = array_key_exists('content-transfer-encoding', $part['headers']) ? $part['headers']['content-transfer-encoding'] : '';

        if ($this->data) {
            return $this->decode($this->getPartBody($part), $encoding);

        } else {
            $start = $part['starting-pos-body'];
            $length = $part['ending-pos-body'] - $start;
            $this->file->setPosition($start);

            if ($length < self::$bigFileTreshold) {
                return $this->decode($this->file->read($length), $encoding);

            } else {
                $tmpFile = File::createTemporaryFile();
                $that = $this;

                $this->file->copyData(function ($chunk) use ($that, $tmpFile, $encoding) {
                    $tmpFile->write($that->decode($chunk, $encoding));
                }, $start, $length);

                $tmpFile->setPosition(0);

                return $tmpFile;
            }
        }
    }

}
