<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Mail;

use Nette\Diagnostics\Debugger;
use Nette\Utils\Strings;
use Dogma\Io\File;
use Dogma\Language\Inflector;


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
class Message extends \Dogma\Object {

    const
        TEXT = 'text/plain',
        HTML = 'text/html';

    /** @var int bigger attachements will be treated using temporary files */
    public static $bigFileTreshold = 0x100000; // 1MB

    /** @var array */
    private $parts = array();

    /** @var array */
    private $headers;

    /** @var File */
    private $file;

    /** @var string */
    private $data;


    /** @var callback(@param string $address, @param string $name, @return Address) */
    private $addressFactory;


    /**
     * @param string|File
     */
    public function __construct($message) {
        if ($message instanceof File) {
            $this->file = $message;
            Debugger::tryError();
            $handler = mailparse_msg_parse_file($this->file->getName());
            if (Debugger::catchError($error)) {
                throw new ParsingException("Cannot parse email file: $error->message.", 0, $error);
            } elseif (!$handler) {
                throw new ParsingException("Cannot parse email file.");
            }

        } else {
            Debugger::tryError();
            $handler = mailparse_msg_create();
            $res = mailparse_msg_parse($handler, $message);
            if (Debugger::catchError($error)) {
                throw new ParsingException("Cannot parse email message: $error->message.", 0, $error);
            } elseif (!$handler || !$res) {
                throw new ParsingException("Cannot parse email message.");
            }
            $this->data = $message;
        }

        Debugger::tryError();
        $structure = mailparse_msg_get_structure($handler);
        if (Debugger::catchError($error)) {
            throw new ParsingException("Cannot parse email structure: $error->message.", 0, $error);
        } elseif (!$structure) {
            throw new ParsingException("Cannot parse email structure.");
        }

        $this->parts = array();
        foreach ($structure as $partId) {
            Debugger::tryError();
            $partHandler = mailparse_msg_get_part($handler, $partId);
            $partData = mailparse_msg_get_part_data($partHandler);
            if (Debugger::catchError($error)) {
                throw new ParsingException("Cannot get email part data: $error->message.", 0, $error);
            } elseif (!$partHandler || !$partData) {
                throw new ParsingException("Cannot get email part data.");
            }
            $this->parts[$partId] = $partData;
        }

        mailparse_msg_free($handler);
    }


    /**
     * @param callable
     */
    public function setAddressFactory($factory) {
        if (!is_callable($factory))
            throw new \InvalidArgumentException("Message factory must be callable.");

        $this->addressFactory = $factory;
    }


    /**
     * Returns all email headers.
     * @return array
     */
    public function getHeaders() {
        if (!$this->headers) {
            $this->headers = $this->parts[1]['headers'];
            $this->decodeHeaders($this->headers);
        }

        return $this->headers;
    }


    /**
     * Returns an email header.
     * @param string
     * @return string|NULL
     */
    public function getHeader($name) {
        if (!$this->headers) $this->getHeaders();

        if (isset($this->headers[$name])) {
            return $this->headers[$name];
        }

        return NULL;
    }


    /**
     * Return content types of body (usualy text/plain and text/html).
     * @return string[]
     */
    public function getContentTypes() {
        $ct = array();
        foreach ($this->parts as $part) {
            if (isset($part['content-disposition'])) continue;
            if (substr($part['content-type'], 0, 9) === 'multipart') continue;
        }

        return $ct;
    }


    /**
     * Returns message body of given type.
     * @param string
     * @return string|NULL
     */
    public function getBody($type = self::TEXT) {
        if ($type !== 'text/plain' && $type !== 'text/html')
            throw new ParsingException('Invalid content type specified. Type can either be "text/plain" or "text/html".');

        foreach ($this->parts as $part) {
            if ($type !== $part['content-type']) continue;
            if (isset($part['content-disposition'])) continue;

            return $this->decode($this->getPartBody($part), @$part['headers']['content-transfer-encoding'] ?: '');
            break;
        }

        return NULL;
    }


    /**
     * Get the headers for the message body part.
     * @param string
     * @return string[]
     */
    public function getBodyHeaders($type = self::TEXT) {
        if ($type !== 'text/plain' && $type !== 'text/html')
            throw new ParsingException('Invalid content type specified. Type can either be "text/plain" or "text/html".');

        foreach ($this->parts as $part) {
            if ($type !== $part['content-type']) continue;

            return @$part['headers'] ?: array();
        }

        return array();
    }


    /**
     * Returns attachments. May be filtered by mime type.
     * @param string|string[]
     * @param bool
     * @return Attachement[]
     */
    public function getAttachments($contentType = NULL, $inlines = TRUE) {
        $dispositions = $inlines ? array('attachment', 'inline') : array('attachment');
        if (isset($contentType) && !is_array($contentType)) $contentType = array($contentType);

        $attachments = array();
        foreach ($this->parts as $part) {
            if (!in_array(@$part['content-disposition'], $dispositions)) continue; // only on attachments
            if ($contentType && !in_array($part['content-type'], $contentType)) continue;

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
    public function &__get($name) {
        if (!$this->headers) $this->getHeaders();

        $name = Inflector::dasherize(Inflector::underscore($name));
        $val = isset($this->headers[$name]) ? $this->headers[$name]
            : (isset($this->headers['x-' . $name]) ? $this->headers['x-' . $name] : NULL);

        return $val;
    }


    // internals -------------------------------------------------------------------------------------------------------


    /**
     * Decode message part from transfer encoding.
     * @internal
     * @param string
     * @param string
     * @return string
     */
    public function decode($data, $encoding) {
        if (strtolower($encoding) === 'base64') {
            return base64_decode($data);

        } elseif (strtolower($encoding) === 'quoted-printable') {
            return quoted_printable_decode($data);

        } elseif (!$encoding) {
            return $data;

        } else {
            throw new ParsingException("Unknown transfer encoding.");
        }
    }


    /**
     * Find and decode encoded headers (format: =?charset?te?header?=)
     * @param array
     */
    private function decodeHeaders(&$headers) {
        foreach ($headers as $name => &$value) {
            if (is_array($value)) {
                $this->decodeHeaders($value);
                continue;
            }

            //

            if (in_array($name, array('date', 'resent-date', 'delivery-date', 'expires'), TRUE)) {
                $value = new \Dogma\DateTime($value);
                $value->setDefaultTimezone();

            } elseif (in_array($name, array('from', 'to', 'cc', 'bcc', 'reply-to', 'return-path', 'sender'), TRUE)) {
                $value = self::parseAddressHeader($value);

            } elseif (strpos($value, '=?') !== FALSE) {
                $value = $this->decodeHeader($value);
            }
        }
    }


    /**
     * Parse addresses from mail header (from, to, cc, bcc, reply-to, return-path, delivered-to, sender…)
     * @param string
     * @return Address[]
     */
    private function parseAddressHeader($header) {
        $data = mailparse_rfc822_parse_addresses($header);

        $arr = array();
        foreach ($data as $item) {
            list($name, $address, $group) = array_values($item);

            $name = $address === $name ? NULL
                : (strpos($name, '=?') !== FALSE ? $this->decodeHeader($name) : $name);
            $arr[] = call_user_func($this->addressFactory, $address, $name);
        }

        return $arr;
    }


    /**
     * @param string
     * @param string
     */
    private static function createAddress($address, $name) {
        return new Address($address, $name);
    }


    /**
     * Decode email header.
     * @internal
     * @param string
     * @return string
     */
    private function decodeHeader($header) {
        // =?utf-8?q?Test=3a=20P=c5=99=c3=...?=
        $that = $this;
        $header = Strings::replace($header, '/(=\\?[^?]+\\?[^?]\\?[^?]+\\?=)/', function ($match) use ($that) {
            list($x, $charset, $encoding, $message, $y) = explode('?', $match[0]);

            if (strtolower($encoding) === 'b') {
                $message = base64_decode($message);

            } elseif (strtolower($encoding) === 'q') {
                $message = quoted_printable_decode($message);

            } else {
                throw new ParsingException("Unknown header encoding '$encoding'.");
            }

            return $that->convertCharset($message, strtolower($charset));
        });

        return $header;
    }


    /**
     * @internal
     * @param string
     * @return string
     */
    public static function convertCharset($string, $charset) {
        if ($charset === 'utf-8')
            return $string;

        return iconv('utf-8', $charset, $string);
    }


    /**
     * @param array
     * @return array
     */
    private function getParsedPartHeaders($part) {
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
     * @param array
     * @return string|NULL
     */
    private function getPartBody($part) {
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
     * @param array
     * @return string|File
     */
    private function getAttachmentData($part) {
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
