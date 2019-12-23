<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma;

class Char
{
    use StaticClassMixin;

    public const NUL = "\x00"; // null
    public const SOH = "\x01"; // start of header
    public const STX = "\x02"; // start of text
    public const ETX = "\x03"; // end of text
    public const EOT = "\x04"; // end of transmission
    public const ENQ = "\x05"; // enquiry
    public const ACK = "\x06"; // acknowledge
    public const BEL = "\x07"; // bell
    public const BS  = "\x08"; // backspace
    public const HT  = "\x09"; // horizontal tab
    public const LF  = "\x0A"; // line feed
    public const VT  = "\x0B"; // vertical tab
    public const FF  = "\x0C"; // form feed
    public const CR  = "\x0D"; // enter / carriage return
    public const SO  = "\x0E"; // shift out
    public const SI  = "\x0F"; // shift in
    public const DLE = "\x10"; // data link escape
    public const DC1 = "\x11"; // device control 1
    public const DC2 = "\x12"; // device control 2
    public const DC3 = "\x13"; // device control 3
    public const DC4 = "\x14"; // device control 4
    public const NAK = "\x15"; // negative acknowledge
    public const SYN = "\x16"; // synchronize
    public const ETB = "\x17"; // end of trans. block
    public const CAN = "\x18"; // cancel
    public const EM  = "\x19"; // end of medium
    public const SUB = "\x1A"; // substitute
    public const ESC = "\x1B"; // escape
    public const FS  = "\x1C"; // file separator
    public const GS  = "\x1D"; // group separator
    public const RS  = "\x1E"; // record separator
    public const US  = "\x1F"; // unit separator
    public const DEL = "\x7F"; // delete

    public const NULL = "\x00";
    public const START_OF_HEADER = "\x01";
    public const START_OF_TEXT = "\x02";
    public const END_OF_TEXT = "\x03";
    public const END_OF_TRANSMISSION = "\x04";
    public const ENQUIRY = "\x05";
    public const ACKNOWLEDGE = "\x06";
    public const BELL = "\x07";
    public const BACKSPACE = "\x08";
    public const HORIZONTAL_TAB  = "\x09";
    public const LINE_FEED = "\x0A";
    public const VERTICAL_TAB = "\x0B";
    public const FORM_FEED = "\x0C";
    public const CARRIAGE_RETURN = "\x0D";
    public const SHIFT_OUT = "\x0E";
    public const SHIFT_IN = "\x0F";
    public const DATA_LINK_ESCAPE = "\x10";
    public const DEVICE_CONTROL_1 = "\x11";
    public const DEVICE_CONTROL_2 = "\x12";
    public const DEVICE_CONTROL_3 = "\x13";
    public const DEVICE_CONTROL_4 = "\x14";
    public const NEGATIVE_ACKNOWLEDGE = "\x15";
    public const SYNCHRONIZE = "\x16";
    public const END_OF_TRANSMISSION_BLOCK = "\x17";
    public const CANCEL = "\x18";
    public const END_OF_MEDIUM = "\x19";
    public const SUBSTITUTE = "\x1A";
    public const ESCAPE = "\x1B";
    public const FILE_SEPARATOR = "\x1C";
    public const GROUP_SEPARATOR = "\x1D";
    public const RECORD_SEPARATOR = "\x1E";
    public const UNIT_SEPARATOR = "\x1F";
    public const DELETE = "\x7F";

}
