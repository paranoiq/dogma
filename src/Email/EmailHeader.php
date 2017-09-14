<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Email;

class EmailHeader extends \Dogma\Enum\PartialStringEnum
{

    // IETF
    public const ACCEPT_LANGUAGE = 'Accept-Language';
    public const ALTERNATE_RECIPIENT = 'Alternate-Recipient';
    public const ARCHIVED_AT = 'Archived-At';
    public const AUTHENTICATION_RESULTS = 'Authentication-Results';
    public const AUTO_SUBMITTED = 'Auto-Submitted';
    public const AUTOFORWARDED = 'Autoforwarded';
    public const AUTOSUBMITTED = 'Autosubmitted';
    public const BCC = 'Bcc';
    public const CC = 'Cc';
    public const COMMENTS = 'Comments';
    public const CONTENT_IDENTIFIER = 'Content-Identifier';
    public const CONTENT_RETURN = 'Content-Return';
    public const CONVERSION = 'Conversion';
    public const CONVERSION_WITH_LOSS = 'Conversion-With-Loss';
    public const DL_EXPANSION_HISTORY = 'DL-Expansion-History';
    public const DATE = 'Date';
    public const DEFERRED_DELIVERY = 'Deferred-Delivery';
    public const DELIVERY_DATE = 'Delivery-Date';
    public const DISCARDED_X400_IPMS_EXTENSIONS = 'Discarded-X400-IPMS-Extensions';
    public const DISCARDED_X400_MTS_EXTENSIONS = 'Discarded-X400-MTS-Extensions';
    public const DISCLOSE_RECIPIENTS = 'Disclose-Recipients';
    public const DISPOSITION_NOTIFICATION_OPTIONS = 'Disposition-Notification-Options';
    public const DISPOSITION_NOTIFICATION_TO = 'Disposition-Notification-To';
    public const DKIM_SIGNATURE = 'DKIM-Signature';
    public const DOWNGRADED_FINAL_RECIPIENT = 'Downgraded-Final-Recipient';
    public const DOWNGRADED_IN_REPLY_TO = 'Downgraded-In-Reply-To';
    public const DOWNGRADED_MESSAGE_ID = 'Downgraded-Message-Id';
    public const DOWNGRADED_ORIGINAL_RECIPIENT = 'Downgraded-Original-Recipient';
    public const DOWNGRADED_REFERENCES = 'Downgraded-References';
    public const ENCODING = 'Encoding';
    public const ENCRYPTED = 'Encrypted';
    public const EXPIRES = 'Expires';
    public const EXPIRY_DATE = 'Expiry-Date';
    public const FROM = 'From';
    public const GENERATE_DELIVERY_REPORT = 'Generate-Delivery-Report';
    public const IMPORTANCE = 'Importance';
    public const IN_REPLY_TO = 'In-Reply-To';
    public const INCOMPLETE_COPY = 'Incomplete-Copy';
    public const KEYWORDS = 'Keywords';
    public const LANGUAGE = 'Language';
    public const LATEST_DELIVERY_TIME = 'Latest-Delivery-Time';
    public const LIST_ARCHIVE = 'List-Archive';
    public const LIST_HELP = 'List-Help';
    public const LIST_ID = 'List-ID';
    public const LIST_OWNER = 'List-Owner';
    public const LIST_POST = 'List-Post';
    public const LIST_SUBSCRIBE = 'List-Subscribe';
    public const LIST_UNSUBSCRIBE = 'List-Unsubscribe';
    public const LIST_UNSUBSCRIBE_POST = 'List-Unsubscribe-Post';
    public const MESSAGE_CONTEXT = 'Message-Context';
    public const MESSAGE_ID = 'Message-ID';
    public const MESSAGE_TYPE = 'Message-Type';
    public const MT_PRIORITY = 'MT-Priority';
    public const OBSOLETES = 'Obsoletes';
    public const ORGANIZATION = 'Organization';
    public const ORIGINAL_ENCODED_INFORMATION_TYPES = 'Original-Encoded-Information-Types';
    public const ORIGINAL_FROM = 'Original-From';
    public const ORIGINAL_MESSAGE_ID = 'Original-Message-ID';
    public const ORIGINAL_RECIPIENT = 'Original-Recipient';
    public const ORIGINATOR_RETURN_ADDRESS = 'Originator-Return-Address';
    public const ORIGINAL_SUBJECT = 'Original-Subject';
    public const PICS_LABEL = 'PICS-Label';
    public const PREVENT_NONDELIVERY_REPORT = 'Prevent-NonDelivery-Report';
    public const PRIORITY = 'Priority';
    public const RECEIVED = 'Received';
    public const RECEIVED_SPF = 'Received-SPF';
    public const REFERENCES = 'References';
    public const REPLY_BY = 'Reply-By';
    public const REPLY_TO = 'Reply-To';
    public const REQUIRE_RECIPIENT_VALID_SINCE = 'Require-Recipient-Valid-Since';
    public const RESENT_BCC = 'Resent-Bcc';
    public const RESENT_CC = 'Resent-Cc';
    public const RESENT_DATE = 'Resent-Date';
    public const RESENT_FROM = 'Resent-From';
    public const RESENT_MESSAGE_ID = 'Resent-Message-ID';
    public const RESENT_SENDER = 'Resent-Sender';
    public const RESENT_TO = 'Resent-To';
    public const RETURN_PATH = 'Return-Path';
    public const SENDER = 'Sender';
    public const SENSITIVITY = 'Sensitivity';
    public const SOLICITATION = 'Solicitation';
    public const SUBJECT = 'Subject';
    public const SUPERSEDES = 'Supersedes';
    public const TO = 'To';
    public const VBR_INFO = 'VBR-Info';
    public const X400_CONTENT_IDENTIFIER = 'X400-Content-Identifier';
    public const X400_CONTENT_RETURN = 'X400-Content-Return';
    public const X400_CONTENT_TYPE = 'X400-Content-Type';
    public const X400_MTS_IDENTIFIER = 'X400-MTS-Identifier';
    public const X400_ORIGINATOR = 'X400-Originator';
    public const X400_RECEIVED = 'X400-Received';
    public const X400_RECIPIENTS = 'X400-Recipients';
    public const X400_TRACE = 'X400-Trace';

    // MIME
    public const BASE = 'Base';
    public const CONTENT_ALTERNATIVE = 'Content-Alternative';
    public const CONTENT_BASE = 'Content-Base';
    public const CONTENT_DESCRIPTION = 'Content-Description';
    public const CONTENT_DISPOSITION = 'Content-Disposition';
    public const CONTENT_DURATION = 'Content-Duration';
    public const CONTENT_FEATURES = 'Content-features';
    public const CONTENT_ID = 'Content-ID';
    public const CONTENT_LANGUAGE = 'Content-Language';
    public const CONTENT_LOCATION = 'Content-Location';
    public const CONTENT_MD5 = 'Content-MD5';
    public const CONTENT_TRANSFER_ENCODING = 'Content-Transfer-Encoding';
    public const CONTENT_TYPE = 'Content-Type';
    public const MIME_VERSION = 'MIME-Version';

    // MMHS
    public const MMHS_EXEMPTED_ADDRESS = 'MMHS-Exempted-Address';
    public const MMHS_EXTENDED_AUTHORISATION_INFO = 'MMHS-Extended-Authorisation-Info';
    public const MMHS_SUBJECT_INDICATOR_CODES = 'MMHS-Subject-Indicator-Codes';
    public const MMHS_HANDLING_INSTRUCTIONS = 'MMHS-Handling-Instructions';
    public const MMHS_MESSAGE_INSTRUCTIONS = 'MMHS-Message-Instructions';
    public const MMHS_CODRESS_MESSAGE_INDICATOR = 'MMHS-Codress-Message-Indicator';
    public const MMHS_ORIGINATOR_REFERENCE = 'MMHS-Originator-Reference';
    public const MMHS_PRIMARY_PRECEDENCE = 'MMHS-Primary-Precedence';
    public const MMHS_COPY_PRECEDENCE = 'MMHS-Copy-Precedence';
    public const MMHS_MESSAGE_TYPE = 'MMHS-Message-Type';
    public const MMHS_OTHER_RECIPIENTS_INDICATOR_TO = 'MMHS-Other-Recipients-Indicator-To';
    public const MMHS_OTHER_RECIPIENTS_INDICATOR_CC = 'MMHS-Other-Recipients-Indicator-CC';
    public const MMHS_ACP127_MESSAGE_IDENTIFIER = 'MMHS-Acp127-Message-Identifier';
    public const MMHS_ORIGINATOR_PLAD = 'MMHS-Originator-PLAD';

    // non-standard
    public const APPARENTLY_TO = 'Apparently-To';
    public const DELIVERED_TO = 'Delivered-To';
    public const EDIINT_FEATURES = 'EDIINT-Features';
    public const EESST_VERSION = 'Eesst-Version';
    public const ERRORS_TO = 'Errors-To';
    public const JABBER_ID = 'Jabber-ID';
    public const MMHS_AUTHORIZING_USERS = 'MMHS-Authorizing-Users';
    public const PRIVICON = 'Privicon';
    public const SIO_LABEL = 'SIO-Label';
    public const SIO_LABEL_HISTORY = 'SIO-Label-History';
    public const X_ARCHIVED_AT = 'X-Archived-At';
    public const X_AUTO_RESPONSE_SUPPRESS = 'X-Auto-Response-Suppress';
    public const X_MAILER = 'X-Mailer';
    public const X_RECEIVED = 'X-Received';

    // obsolete
    /** @deprecated */
    public const DOWNGRADED_BCC = 'Downgraded-Bcc';
    /** @deprecated */
    public const DOWNGRADED_CC = 'Downgraded-Cc';
    /** @deprecated */
    public const DOWNGRADED_DISPOSITION_NOTIFICATION_TO = 'Downgraded-Disposition-Notification-To';
    /** @deprecated */
    public const DOWNGRADED_FROM = 'Downgraded-From';
    /** @deprecated */
    public const DOWNGRADED_MAIL_FROM = 'Downgraded-Email-From';
    /** @deprecated */
    public const DOWNGRADED_RCPT_TO = 'Downgraded-Rcpt-To';
    /** @deprecated */
    public const DOWNGRADED_REPLY_TO = 'Downgraded-Reply-To';
    /** @deprecated */
    public const DOWNGRADED_RESENT_BCC = 'Downgraded-Resent-Bcc';
    /** @deprecated */
    public const DOWNGRADED_RESENT_CC = 'Downgraded-Resent-Cc';
    /** @deprecated */
    public const DOWNGRADED_RESENT_FROM = 'Downgraded-Resent-From';
    /** @deprecated */
    public const DOWNGRADED_RESENT_REPLY_TO = 'Downgraded-Resent-Reply-To';
    /** @deprecated */
    public const DOWNGRADED_RESENT_SENDER = 'Downgraded-Resent-Sender';
    /** @deprecated */
    public const DOWNGRADED_RESENT_TO = 'Downgraded-Resent-To';
    /** @deprecated */
    public const DOWNGRADED_RETURN_PATH = 'Downgraded-Return-Path';
    /** @deprecated */
    public const DOWNGRADED_SENDER = 'Downgraded-Sender';
    /** @deprecated */
    public const DOWNGRADED_TO = 'Downgraded-To';
    /** @deprecated */
    public const RESENT_REPLY_TO = 'Resent-Reply-To';

    /** @var string[] */
    private static $exceptions = [
        'dl-expansion-history' => 'DL-Expansion-History',
        'discarded-x400-ipms-extensions' => 'Discarded-X400-IPMS-Extensions',
        'discarded-x400-mts-extensions' => 'Discarded-X400-MTS-Extensions',
        'dkim-signature' => 'DKIM-Signature',
        'list-id' => 'List-ID',
        'message-id' => 'Message-ID',
        'mt-priority' => 'MT-Priority',
        'original-message-id' => 'Original-Message-ID',
        'pics-label' => 'PICS-Label',
        'prevent-nondelivery-report' => 'Prevent-NonDelivery-Report',
        'received-spf' => 'Received-SPF',
        'resent-message-id' => 'Resent-Message-ID',
        'vbr-info' => 'VBR-Info',
        'x400-mts-identifier' => 'X400-MTS-Identifier',
        'mime-version' => 'MIME-Version',
        'mmhs-exempted-address' => 'MMHS-Exempted-Address',
        'mmhs-extended-authorisation-info' => 'MMHS-Extended-Authorisation-Info',
        'mmhs-subject-indicator-codes' => 'MMHS-Subject-Indicator-Codes',
        'mmhs-handling-instructions' => 'MMHS-Handling-Instructions',
        'mmhs-message-instructions' => 'MMHS-Message-Instructions',
        'mmhs-codress-message-indicator' => 'MMHS-Codress-Message-Indicator',
        'mmhs-originator-reference' => 'MMHS-Originator-Reference',
        'mmhs-primary-precedence' => 'MMHS-Primary-Precedence',
        'mmhs-copy-precedence' => 'MMHS-Copy-Precedence',
        'mmhs-message-type' => 'MMHS-Message-Type',
        'mmhs-other-recipients-indicator-to' => 'MMHS-Other-Recipients-Indicator-To',
        'mmhs-other-recipients-indicator-cc' => 'MMHS-Other-Recipients-Indicator-CC',
        'mmhs-acp127-message-identifier' => 'MMHS-Acp127-Message-Identifier',
        'mmhs-originator-plad' => 'MMHS-Originator-PLAD',
        'ediint-features' => 'EDIINT-Features',
        'jabber-id' => 'Jabber-ID',
        'mmhs-authorizing-users' => 'MMHS-Authorizing-Users',
        'sio-label' => 'SIO-Label',
        'sio-label-history' => 'SIO-Label-History',
    ];

    public static function normalizeName(string $name): string
    {
        $name = strtolower($name);

        if (isset(self::$exceptions[$name])) {
            return self::$exceptions[$name];
        }

        return implode('-', array_map('ucfirst', explode('-', $name)));
    }

    public static function validateValue(string &$value): bool
    {
        $value = self::normalizeName($value);

        return parent::validateValue($value);
    }

    public static function getValueRegexp(): string
    {
        return '(?:X-)?[A-Z][a-z]+(?:[A-Z][a-z]+)*|' . implode('|', self::$exceptions);
    }

}
