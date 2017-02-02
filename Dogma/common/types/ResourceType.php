<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma;

class ResourceType extends \Dogma\Enum
{

    const ASPELL = 'aspell';
    const BZIP2 = 'bzip2';
    const COM = 'COM';
    const VARIANT = 'VARIANT';
    const CPDF = 'cpdf';
    const CPDF_OUTLINE = 'cpdf outline';
    const CUBRID_CONNECTION = 'cubrid connection';
    const PERSISTENT_CUBRID_CONNECTION = 'persistent cubrid connection';
    const CUBRID_REQUEST = 'cubrid request';
    const CUBRID_LOB = 'cubrid lob';
    const CUBRID_LOB2 = 'cubrid lob2';
    const CURL = 'curl';
    const DBM = 'dbm';
    const DBA = 'dba';
    const DBA_PERSISTENT = 'dba persistent';
    const DBASE = 'dbase';
    const DBX_LINK_OBJECT = 'dbx_link_object';
    const DBX_RESULT_OBJECT = 'dbx_result_object';
    const XPATH_CONTEXT = 'xpath context';
    const XPATH_OBJECT = 'xpath object';
    const FBSQL_LINK = 'fbsql link';
    const FBSQL_PLINK = 'fbsql plink';
    const FBSQL_RESULT = 'fbsql result';
    const FDF = 'fdf';
    const FTP = 'ftp';
    const GD = 'gd';
    const GD_FONT = 'gd font';
    const GD_PS_ENCODING = 'gd PS encoding';
    const GD_PS_FONT = 'gd PS font';
    const GMP_INTEGER = 'GMP integer';
    const HYPERWAVE_DOCUMENT = 'hyperwave document';
    const HYPERWAVE_LINK = 'hyperwave link';
    const HYPERWAVE_LINK_PERSISTENT = 'hyperwave link persistent';
    const ICAP = 'icap';
    const IMAP = 'imap';
    const IMAP_CHAIN_PERSISTENT = 'imap chain persistent';
    const IMAP_PERSISTENT = 'imap persistent';
    const INGRES = 'ingres';
    const INGRES_PERSISTENT = 'ingres persistent';
    const INTERBASE_BLOB = 'interbase blob';
    const INTERBASE_LINK = 'interbase link';
    const INTERBASE_LINK_PERSISTENT = 'interbase link persistent';
    const INTERBASE_QUERY = 'interbase query';
    const INTERBASE_RESULT = 'interbase result';
    const INTERBASE_TRANSACTION = 'interbase transaction';
    const JAVA = 'java';
    const LDAP_LINK = 'ldap link';
    const LDAP_RESULT = 'ldap result';
    const LDAP_RESULT_ENTRY = 'ldap result entry';
    const MCAL = 'mcal';
    const SWF_ACTION = 'SWFAction';
    const SWF_BITMAP = 'SWFBitmap';
    const SWF_BUTTON = 'SWFButton';
    const SWF_DISPLAY_ITEM = 'SWFDisplayItem';
    const SWF_FILL = 'SWFFill';
    const SWF_FONT = 'SWFFont';
    const SWF_GRADIENT = 'SWFGradient';
    const SWF_MORPH = 'SWFMorph';
    const SWF_MOVIE = 'SWFMovie';
    const SWF_SHAPE = 'SWFShape';
    const SWF_SPRITE = 'SWFSprite';
    const SWF_TEXT = 'SWFText';
    const SWF_TEXT_FIELD = 'SWFTextField';
    const MNOGOSEARCH_AGENT = 'mnogosearch agent';
    const MNOGOSEARCH_RESULT = 'mnogosearch result';
    const MSQL_LINK = 'msql link';
    const MSQL_LINK_PERSISTENT = 'msql link persistent';
    const MSQL_QUERY = 'msql query';
    const MSSQL_LINK = 'mssql link';
    const MSSQL_LINK_PERSISTENT = 'mssql link persistent';
    const MSSQL_RESULT = 'mssql result';
    const MYSQL_LINK = 'mysql link';
    const MYSQL_LINK_PERSISTENT = 'mysql link persistent';
    const MYSQL_RESULT = 'mysql result';
    const OCI8_COLLECTION = 'oci8 collection';
    const OCI8_CONNECTION = 'oci8 connection';
    const OCI8_LOB = 'oci8 lob';
    const OCI8_STATEMENT = 'oci8 statement';
    const ODBC_LINK = 'odbc link';
    const ODBC_LINK_PERSISTENT = 'odbc link persistent';
    const ODBC_RESULT = 'odbc result';
    const BIRDSTEP_LINK = 'birdstep link';
    const BIRDSTEP_RESULT = 'birdstep result';
    const OPENSSL_KEY = 'OpenSSL key';
    const OPENSSL_X509 = 'OpenSSL X.509';
    const PDF_DOCUMENT = 'pdf document';
    const PDF_IMAGE = 'pdf image';
    const PDF_OBJECT = 'pdf object';
    const PDF_OUTLINE = 'pdf outline';
    const PGSQL_LARGE_OBJECT = 'pgsql large object';
    const PGSQL_LINK = 'pgsql link';
    const PGSQL_LINK_PERSISTENT = 'pgsql link persistent';
    const PGSQL_RESULT = 'pgsql result';
    const PGSQL_STRING = 'pgsql string';
    const PRINTER = 'printer';
    const PRINTER_BRUSH = 'printer brush';
    const PRINTER_FONT = 'printer font';
    const PRINTER_PEN = 'printer pen';
    const PSPELL = 'pspell';
    const PSPELL_CONFIG = 'pspell config';
    const SABLOTRON_XSLT = 'Sablotron XSLT';
    const SHMOP = 'shmop';
    const SOCKETS_FILE_DESCRIPTOR_SET = 'sockets file descriptor set';
    const SOCKETS_IO_VECTOR = 'sockets i/o vector';
    const STREAM = 'stream';
    const SOCKET = 'socket';
    const SYBASE_DB_LINK = 'sybase-db link';
    const SYBASE_DB_LINK_PERSISTENT = 'sybase-db link persistent';
    const SYBASE_DB_RESULT = 'sybase-db result';
    const SYBASE_CT_LINK = 'sybase-ct link';
    const SYBASE_CT_LINK_PERSISTENT = 'sybase-ct link persistent';
    const SYBASE_CT_RESULT = 'sybase-ct result';
    const SYSVSEM = 'sysvsem';
    const SYSVSHM = 'sysvshm';
    const WDDX = 'wddx';
    const XML = 'xml';
    const ZLIB = 'zlib';

}
