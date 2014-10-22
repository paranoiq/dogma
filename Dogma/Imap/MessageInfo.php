<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Imap;

use Dogma\Mail\Message;


/**
 * IMAP message info.
 */
class MessageInfo extends \Dogma\Object {

    /** @var \Dogma\Imap\Connection */
    private $imap;

    /** @var integer */
    private $uid;

    /** @var \Dogma\Mail\Message */
    private $message;


    /**
     * @param \Dogma\Imap\Connection
     * @param integer
     */
    public function __construct(Connection $imap, $uid) {
        $this->imap = $imap;
        $this->uid = $uid;
    }


    /**
     * Get the Message
     * @return \Dogma\Mail\Message
     */
    public function getMessage() {
        if (!$this->message) $this->message = $this->imap->getMessage($this->uid);

        return $this->message;
    }


    /**
     * @return string
     */
    /*public function getBody() {
        return $this->imap->getMessageBody($this->uid);
    }*/


    /**
     * @return string
     */
    /*public function getRawHeader() {
        return $this->imap->getRawMessageHeader($this->uid);
    }*/

}
