<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma;

use Dogma\Enum\StringEnum;

class ResourceType extends StringEnum
{

    public const ASPELL = 'aspell';
    public const BZIP2 = 'bzip2';
    public const COM = 'COM';
    public const VARIANT = 'VARIANT';
    public const CPDF = 'cpdf';
    public const CPDF_OUTLINE = 'cpdf outline';
    public const CUBRID_CONNECTION = 'cubrid connection';
    public const PERSISTENT_CUBRID_CONNECTION = 'persistent cubrid connection';
    public const CUBRID_REQUEST = 'cubrid request';
    public const CUBRID_LOB = 'cubrid lob';
    public const CUBRID_LOB2 = 'cubrid lob2';
    /** @deprecated as of PHP 8.0, class CurlHandle */
    public const CURL = 'curl';
    /** @deprecated as of PHP 8.0, class CurlMultiHandle */
    public const CURL_MULTI = 'curl_multi';
    /** @deprecated as of PHP 8.0, class CurlShareHandle */
    public const CURL_SHARE = 'curl_share';
    public const DBM = 'dbm';
    public const DBA = 'dba';
    public const DBA_PERSISTENT = 'dba persistent';
    public const DBASE = 'dbase';
    public const DBX_LINK_OBJECT = 'dbx_link_object';
    public const DBX_RESULT_OBJECT = 'dbx_result_object';
    public const XPATH_CONTEXT = 'xpath context';
    public const XPATH_OBJECT = 'xpath object';
    public const FBSQL_LINK = 'fbsql link';
    public const FBSQL_PLINK = 'fbsql plink';
    public const FBSQL_RESULT = 'fbsql result';
    public const FDF = 'fdf';
    /** @deprecated as of PHP 8.1, class FTP\Connection */
    public const FTP = 'ftp';
    /** @deprecated as of PHP 8.0, class GdImage */
    public const GD = 'gd';
    /** @deprecated as of PHP 8.1, class GdFont */
    public const GD_FONT = 'gd font';
    public const GD_PS_ENCODING = 'gd PS encoding';
    public const GD_PS_FONT = 'gd PS font';
    /** @deprecated as of PHP 5.6, class GMP */
    public const GMP_INTEGER = 'GMP integer';
    public const HYPERWAVE_DOCUMENT = 'hyperwave document';
    public const HYPERWAVE_LINK = 'hyperwave link';
    public const HYPERWAVE_LINK_PERSISTENT = 'hyperwave link persistent';
    public const ICAP = 'icap';
    /** @deprecated as of PHP 8.1, class IMAP\Connection */
    public const IMAP = 'imap';
    public const IMAP_CHAIN_PERSISTENT = 'imap chain persistent';
    public const IMAP_PERSISTENT = 'imap persistent';
    public const INGRES = 'ingres';
    public const INGRES_PERSISTENT = 'ingres persistent';
    public const INTERBASE_BLOB = 'interbase blob';
    public const INTERBASE_LINK = 'interbase link';
    public const INTERBASE_LINK_PERSISTENT = 'interbase link persistent';
    public const INTERBASE_QUERY = 'interbase query';
    public const INTERBASE_RESULT = 'interbase result';
    public const INTERBASE_TRANSACTION = 'interbase transaction';
    public const JAVA = 'java';
    /** @deprecated as of PHP 8.1, class Ldap\Connection */
    public const LDAP_LINK = 'ldap link';
    /** @deprecated as of PHP 8.1, class Ldap\Result */
    public const LDAP_RESULT = 'ldap result';
    /** @deprecated as of PHP 8.1, class Ldap\ResultEntry */
    public const LDAP_RESULT_ENTRY = 'ldap result entry';
    public const MCAL = 'mcal';
    public const SWF_ACTION = 'SWFAction';
    public const SWF_BITMAP = 'SWFBitmap';
    public const SWF_BUTTON = 'SWFButton';
    public const SWF_DISPLAY_ITEM = 'SWFDisplayItem';
    public const SWF_FILL = 'SWFFill';
    public const SWF_FONT = 'SWFFont';
    public const SWF_GRADIENT = 'SWFGradient';
    public const SWF_MORPH = 'SWFMorph';
    public const SWF_MOVIE = 'SWFMovie';
    public const SWF_SHAPE = 'SWFShape';
    public const SWF_SPRITE = 'SWFSprite';
    public const SWF_TEXT = 'SWFText';
    public const SWF_TEXT_FIELD = 'SWFTextField';
    public const MNOGOSEARCH_AGENT = 'mnogosearch agent';
    public const MNOGOSEARCH_RESULT = 'mnogosearch result';
    public const MSQL_LINK = 'msql link';
    public const MSQL_LINK_PERSISTENT = 'msql link persistent';
    public const MSQL_QUERY = 'msql query';
    public const MSSQL_LINK = 'mssql link';
    public const MSSQL_LINK_PERSISTENT = 'mssql link persistent';
    public const MSSQL_RESULT = 'mssql result';
    public const MYSQL_LINK = 'mysql link';
    public const MYSQL_LINK_PERSISTENT = 'mysql link persistent';
    public const MYSQL_RESULT = 'mysql result';
    public const OCI8_COLLECTION = 'oci8 collection';
    public const OCI8_CONNECTION = 'oci8 connection';
    public const OCI8_LOB = 'oci8 lob';
    public const OCI8_STATEMENT = 'oci8 statement';
    public const ODBC_LINK = 'odbc link';
    public const ODBC_LINK_PERSISTENT = 'odbc link persistent';
    public const ODBC_RESULT = 'odbc result';
    public const BIRDSTEP_LINK = 'birdstep link';
    public const BIRDSTEP_RESULT = 'birdstep result';
    /** @deprecated as of PHP 8.0, class OpenSSLAsymmetricKey */
    public const OPENSSL_KEY = 'OpenSSL key';
    /** @deprecated as of PHP 8.0, class OpenSSLCertificate */
    public const OPENSSL_X509 = 'OpenSSL X.509';
    /** @deprecated as of PHP 8.0, class OpenSSLCertificateSigningRequest */
    public const OPENSSL_CSR = 'OpenSSL CSR';
    public const PDF_DOCUMENT = 'pdf document';
    public const PDF_IMAGE = 'pdf image';
    public const PDF_OBJECT = 'pdf object';
    public const PDF_OUTLINE = 'pdf outline';
    /** @deprecated as of PHP 8.1, class PgSql\Lob */
    public const PGSQL_LARGE_OBJECT = 'pgsql large object';
    /** @deprecated as of PHP 8.1, class PgSql\Connection */
    public const PGSQL_LINK = 'pgsql link';
    /** @deprecated as of PHP 8.1, class PgSql\Connection */
    public const PGSQL_LINK_PERSISTENT = 'pgsql link persistent';
    /** @deprecated as of PHP 8.1, class PgSql\Result */
    public const PGSQL_RESULT = 'pgsql result';
    /** @deprecated as of PHP 8.1, class PgSql\Lob */
    public const PGSQL_STRING = 'pgsql string';
    public const PRINTER = 'printer';
    public const PRINTER_BRUSH = 'printer brush';
    public const PRINTER_FONT = 'printer font';
    public const PRINTER_PEN = 'printer pen';
    /** @deprecated as of PHP 8.1, class PSpell\Dictionary */
    public const PSPELL = 'pspell';
    /** @deprecated as of PHP 8.1, class PSpell\Config */
    public const PSPELL_CONFIG = 'pspell config';
    public const SABLOTRON_XSLT = 'Sablotron XSLT';
    /** @deprecated as of PHP 8.0, class Shmop */
    public const SHMOP = 'shmop';
    public const SOCKETS_FILE_DESCRIPTOR_SET = 'sockets file descriptor set';
    public const SOCKETS_IO_VECTOR = 'sockets i/o vector';
    public const STREAM = 'stream';
    public const STREAM_CONTEXT = 'stream-context';
    /** @deprecated as of PHP 8.0, class Socket */
    public const SOCKET = 'socket';
    /** @deprecated as of PHP 8.0, class AddressInfo */
    public const SOCKET_FILE_DESCRIPTOR_SET = 'socket file descriptor set';
    public const SYBASE_DB_LINK = 'sybase-db link';
    public const SYBASE_DB_LINK_PERSISTENT = 'sybase-db link persistent';
    public const SYBASE_DB_RESULT = 'sybase-db result';
    public const SYBASE_CT_LINK = 'sybase-ct link';
    public const SYBASE_CT_LINK_PERSISTENT = 'sybase-ct link persistent';
    public const SYBASE_CT_RESULT = 'sybase-ct result';
    public const SYSVSEM = 'sysvsem';
    public const SYSVSHM = 'sysvshm';
    public const WDDX = 'wddx';
    /** @deprecated as of PHP 8.0, class XMLParser */
    public const XML = 'xml';
    /** @deprecated as of PHP 8.0, class InflateContext and DeflateContext */
    public const ZLIB = 'zlib';

}
