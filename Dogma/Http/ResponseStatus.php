<?php

namespace Dogma\Http;


/**
 * HTTP 1.1 response status codes and CURL error codes
 */
class ResponseStatus extends \Dogma\Enum {

    
    const
        S100_CONTINUE = 100,
        S101_SWITCHING_PROTOCOLS = 101,
        S102_PROCESSING = 102, // (WebDAV) (RFC 2518)
        S103_CHECKPOINT = 103,
        
        S200_OK = 200,
        S201_CREATED = 201,
        S202_ACCEPTED = 202,
        S203_NON_AUTHORITATIVE_INFORMATION = 203,
        S204_NO_CONTENT = 204,
        S205_RESET_CONTENT = 205,
        S206_PARTIAL_CONTENT = 206,
        S207_MULTI_STATUS = 207, // (WebDAV) (RFC 4918)
        S208_ALREADY_REPORTED = 208, // (WebDAV) (RFC 5842)
        S226_IM_USER = 226, // (RFC 3229)
        
        S300_MULTIPLE_CHOICES = 300,
        S301_MOVED_PERMANENTLY = 301,
        S302_FOUND = 302,
        S303_SEE_OTHER = 303,
        S304_NOT_MODIFIED = 304,
        S305_USE_PROXY = 305,
        S306_SWITCH_PROXY = 306,
        S307_TEMPORARY_REDIRECT= 307,
        S308_RESUME_INCOMPLETE = 308,
        
        S400_BAD_REQUEST = 400,
        S401_UNAUTHORIZED = 401,
        S402_PAYMENT_REQUIRED = 402,
        S403_FORBIDDEN = 403,
        S404_NOT_FOUND = 404,
        S405_METHOD_NOT_ALLOWED = 405,
        S406_NOT_ACCEPTABLE = 406,
        S407_PROXY_AUTHENTICATION_REQUIRED = 407,
        S408_REQUEST_TIMEOUT = 408,
        S409_CONFLICT = 409,
        S410_GONE = 410,
        S411_LENGTH_REQUIRED = 411,
        S412_PRECONDITION_FAILED = 412,
        S413_REQUESTED_ENTITY_TOO_LARGE = 413,
        S414_REQUEST_URI_TOO_LONG = 414,
        S415_UNSUPPORTED_MEDIA_TYPE = 415,
        S416_REQUESTED_RANGE_NOT_SATISFIABLE = 416,
        S417_EXPECTATION_FAILED = 417,
        S418_IM_A_TEAPOT = 418, // joke
        S420_ENHANCE_YOUR_CALM = 420, // (Twitter) should be handled as 429
        S422_UNPROCESSABLE_ENTITY = 422, // (WEBDAV) (RFC 4918)
        S423_LOCKED = 423, // (WEBDAV) (RFC 4918)
        S424_FAILED_DEPENDENCY = 424, // (WEBDAV) (RFC 4918)
        S425_UNORDERED_COLLECTION = 425, // (RFC 3648)
        S426_UPGRADE_REQUIRED = 426, // (RFC 2817)
        S428_PRECONDITION_REQUIRED = 428,
        S429_TOO_MANY_REQUESTS = 429,
        S431_REQUEST_HEADER_FIELDS_TOO_LARGE = 431,
        S449_RETRY_WITH = 449,
        S450_BLOCKED_BY_WINDOWS_PARENTAL_CONTROLS = 450,
        S451_UNAVAILABLE_FOR_LEGAL_RESONS = 451, // draft

        S500_INTERNAL_SERVER_ERROR = 500,
        S501_NOT_IMPLEMENTED = 501,
        S502_BAD_GATEWAY = 502,
        S503_SERVICE_UNAVAILABLE = 503,
        S504_GATEWAY_TIMEOUT = 504,
        S505_HTTP_VERSION_NOT_SUPPORTED = 505,
        S506_VARIANT_ALSO_NEGOTIATES = 506, // (RFC 2295)
        S507_INSUFFICIENT_STORAGE = 507, // (WEBDAV) (RFC 4918)[4]
        S508_LOOP_DETECTED = 508, // (WebDAV) (RFC 5842)
        S509_BANDWIDTH_LIMIT_EXCEEDED = 509, // (APACHE BW/LIMITED EXTENSION)
        S510_NOT_EXTENDED = 510, // (RFC 2774)
        S511_NETWORK_AUTHENTICATION_REQUIRED = 511,

        
        // system & CURL internals
        FAILED_INIT           =  2, // Very early initialization code failed. This is likely to be an internal error or problem, or a resource problem where something fundamental couldn't get done at init time.
        NOT_BUILT_IN          =  4, // (CURLE_URL_MALFORMAT_USER) A requested feature, protocol or option was not found built-in in this libcurl due to a build-time decision. This means that a feature or option was not enabled or explicitly disabled when libcurl was built and in order to get it to function you have to get a rebuilt libcurl.
        OUT_OF_MEMORY         = 27, // A memory allocation request failed. This is serious badness and things are severely screwed up if this ever occurs.
        HTTP_POST_ERROR       = 34, // This is an odd error that mainly occurs due to internal confusion.
        FUNCTION_NOT_FOUND    = 41, // Function not found. A required zlib function was not found.
        BAD_FUNCTION_ARGUMENT = 43, // Internal error. A function was called with a bad parameter.
        SEND_FAIL_REWIND      = 65, // When doing a send operation curl had to rewind the data to retransmit, but the rewinding operation failed.
        CONV_FAILED           = 75, // Character conversion failed.
        CONV_REQD             = 76, // Caller must register conversion callbacks.
        
        // file system
        READ_ERROR            = 26, // There was a problem reading a local file or an error returned by the read callback.
        WRITE_ERROR           = 23, // An error occurred when writing received data to a local file, or an error was returned to libcurl from a write callback.
        FILE_COULDNT_READ_FILE = 37,// A file given with FILE:// couldn't be opened. Most likely because the file path doesn't identify an existing file. Did you check file permissions?
        FILESIZE_EXCEEDED     = 63, // Maximum file size exceeded.
        
        // user error
        UNSUPPORTED_PROTOCOL  =  1, // The URL you passed to libcurl used a protocol that this libcurl does not support. The support might be a compile-time option that you didn't use, it can be a misspelled protocol string or just a protocol libcurl has no code for.
        URL_MALFORMAT         =  3, // The URL was not properly formatted.
        HTTP_RETURNED_ERROR   = 22, // (CURLE_HTTP_NOT_FOUND) This is returned if CURLOPT_FAILONERROR is set TRUE and the HTTP server returns an error code that is >= 400.
        BAD_DOWNLOAD_RESUME   = 36, // (CURLE_FTP_BAD_DOWNLOAD_RESUME) The download could not be resumed because the specified offset was out of the file boundary.
        UNKNOWN_OPTION        = 48, // (CURLE_UNKNOWN_TELNET_OPTION) An option passed to libcurl is not recognized/known. Refer to the appropriate documentation. This is most likely a problem in the program that uses libcurl. The error buffer might contain more specific information about which exact option it concerns.
        BAD_CONTENT_ENCODING  = 61, // Unrecognized transfer encoding.
        LOGIN_DENIED          = 67, // The remote server denied curl to login
        REMOTE_FILE_NOT_FOUND = 78, // The resource referenced in the URL does not exist.
        
        // network/socket
        COULDNT_RESOLVE_PROXY =  5, // Couldn't resolve proxy. The given proxy host could not be resolved.
        COULDNT_RESOLVE_HOST  =  6, // Couldn't resolve host. The given remote host was not resolved.
        COULDNT_CONNECT       =  7, // Failed to connect() to host or proxy.
        INTERFACE_FAILED      = 45, // (CURLE_HTTP_PORT_FAILED) Interface error. A specified outgoing interface could not be used. Set which interface to use for outgoing connections' source IP address with CURLOPT_INTERFACE.
        SEND_ERROR            = 55, // Failed sending network data.
        RECV_ERROR            = 56, // Failure with receiving network data.
        TRY_AGAIN             = 81, // [CURL_AGAIN] Socket is not ready for send/recv wait till it's ready and try again. This return code is only returned from curl_easy_recv(3) and curl_easy_send(3)
        
        // server
        UNKNOWN_RESPONSE_CODE = -1, // An unknown (not listed above) HTTP response code was received
        RANGE_ERROR           = 33, // (CURLE_HTTP_RANGE_ERROR) The server does not support or accept range requests.
        GOT_NOTHING           = 52, // Nothing was returned from the server, and under the circumstances, getting nothing is considered an error.
        
        // other
        PARTIAL_FILE          = 18, // A file transfer was shorter or larger than expected. This happens when the server first reports an expected transfer size, and then delivers data that doesn't match the previously given size.
        OPERATION_TIMEDOUT    = 28, // (CURLE_OPERATION_TIMEOUTED) Operation timeout. The specified time-out period was reached according to the conditions.
        ABORTED_BY_CALLBACK   = 42, // Aborted by callback. A callback returned "abort" to libcurl.
        TOO_MANY_REDIRECTS    = 47, // Too many redirects. When following redirects, libcurl hit the maximum amount. Set your limit with CURLOPT_MAXREDIRS.
        
        // SSL
        SSL_CONNECT_ERROR     = 35, // A problem occurred somewhere in the SSL/TLS handshake. You really want the error buffer and read the message there as it pinpoints the problem slightly more. Could be certificates (file formats, paths, permissions), passwords, and others.
        PEER_FAILED_VERIFICATION = 51, // (CURLE_SSL_PEER_CERTIFICATE) The remote server's SSL certificate or SSH md5 fingerprint was deemed not OK.
        SSL_ENGINE_NOTFOUND   = 53, // The specified crypto engine wasn't found.
        SSL_ENGINE_SETFAILED  = 54, // Failed setting the selected SSL crypto engine as default!
        SSL_CERTPROBLEM       = 58, // problem with the local client certificate.
        SSL_CIPHER            = 59, // Couldn't use specified cipher.
        SSL_CACERT            = 60, // Peer certificate cannot be authenticated with known CA certificates.
        SSL_ENGINE_INITFAILED = 66, // Initiating the SSL Engine failed.
        SSL_CACERT_BADFILE    = 77, // Problem with reading the SSL CA cert (path? access rights?)
        SSL_SHUTDOWN_FAILED   = 80, // Failed to shut down the SSL connection.
        SSL_CRL_BADFILE       = 82, // Failed to load CRL file
        SSL_ISSUER_ERROR      = 83, // Issuer check failed
        
        
        // following errors should not occure in HTTP transfer
        
        // FTP
        FTP_WEIRD_SERVER_REPLY = 8, // After connecting to a FTP server, libcurl expects to get a certain reply back. This error code implies that it got a strange or bad reply. The given remote server is probably not an OK FTP server.
        FTP_ACCESS_DENIED     =  9, // We were denied access to the resource given in the URL. For FTP, this occurs while trying to change to the remote directory.
        FTP_ACCEPT_FAILED     = 10, // (CURLE_FTP_USER_PASSWORD_INCORRECT) While waiting for the server to connect back when an active FTP session is used, an error code was sent over the control connection or similar.
        FTP_WEIRD_PASS_REPLY  = 11, // After having sent the FTP password to the server, libcurl expects a proper reply. This error code indicates that an unexpected code was returned.
        FTP_ACCEPT_TIMEOUT    = 12, // (CURLE_FTP_WEIRD_USER_REPLY) During an active FTP session while waiting for the server to connect, the CURLOPT_ACCEPTTIMOUT_MS (or the internal default) timeout expired.
        FTP_WEIRD_PASV_REPLY  = 13, // libcurl failed to get a sensible result back from the server as a response to either a PASV or a EPSV command. The server is flawed.
        FTP_WEIRD_227_FORMAT  = 14, // FTP servers return a 227-line as a response to a PASV command. If libcurl fails to parse that line, this return code is passed back.
        FTP_CANT_GET_HOST     = 15, // An internal failure to lookup the host used for the new connection.
        FTP_COULDNT_SET_TYPE  = 17, // (CURLE_FTP_COULDNT_SET_BINARY) Received an error when trying to set the transfer mode to binary or ASCII.
        FTP_COULDNT_RETR_FILE = 19, // This was either a weird reply to a 'RETR' command or a zero byte transfer complete.
        FTP_QUOTE_ERROR       = 21, // When sending custom "QUOTE" commands to the remote server, one of the commands returned an error code that was 400 or higher (for FTP) or otherwise indicated unsuccessful completion of the command.
        UPLOAD_FAILED         = 25, // (CURLE_FTP_COULDNT_STOR_FILE) Failed starting the upload. For FTP, the server typically denied the STOR command. The error buffer usually contains the server's explanation for this.
        FTP_PORT_FAILED       = 30, // The FTP PORT command returned error. This mostly happens when you haven't specified a good enough address for libcurl to use. See CURLOPT_FTPPORT.
        FTP_COULDNT_USE_REST  = 31, // The FTP REST command returned error. This should never happen if the server is sane.
        USE_SSL_FAILED        = 64, // (CURLE_FTP_SSL_FAILED) Requested FTP SSL level failed.
        FTP_PRET_FAILED       = 84, // The FTP server does not understand the PRET command at all or does not support the given argument. Be careful when using CURLOPT_CUSTOMREQUEST, a custom LIST command will be sent with PRET CMD before PASV as well.
        FTP_BAD_FILE_LIST     = 87, // Unable to parse FTP file list (during FTP wildcard downloading).
        CHUNK_FAILED          = 88, // Chunk callback reported error.

        // TFTP
        TFTP_NOTFOUND         = 68, // File not found on TFTP server.
        TFTP_PERM             = 69, // Permission problem on TFTP server.
        REMOTE_DISK_FULL      = 70, // Out of disk space on the server.
        TFTP_ILLEGAL          = 71, // Illegal TFTP operation.
        TFTP_UNKNOWNID        = 72, // Unknown TFTP transfer ID.
        REMOTE_FILE_EXISTS    = 73, // File already exists and will not be overwritten.
        TFTP_NOSUCHUSER       = 74, // This error should never be returned by a properly functioning TFTP server.

        // SSH
        SSH_ERROR             = 79, // [CURL_SSH] An unspecified error occurred during the SSH session.

        // LDAP
        LDAP_CANNOT_BIND      = 38, // LDAP cannot bind. LDAP bind operation failed.
        LDAP_SEARCH_FAILED    = 39, // LDAP search failed.
        LDAP_INVALID_URL      = 62, // Invalid LDAP URL.
        
        // Telnet
        TELNET_OPTION_SYNTAX  = 49, // A telnet option string was Illegally formatted.

        // RTPS
        RTSP_CSEQ_ERROR       = 85, // Mismatch of RTSP CSeq numbers.
        RTSP_SESSION_ERROR    = 86; // Mismatch of RTSP Session Identifiers.
    


    /**
     * Is an information/handshaking HTTP response code (1xx)
     * @return bool
     */
    public function isInfo() {
        return $this->value >= 100 && $this->value < 200;
    }
    
    
    /**
     * Is a positive HTTP response code (2xx)
     * @return bool
     */
    public function isOk() {
        return $this->value >= 200 && $this->value < 300;
    }


    /**
     * Is a HTTP redirection code (3xx)
     * @return bool
     */
    public function isRedirect() {
        return ($this->value >= 300 && $this->value < 400) || $this->value == self::TOO_MANY_REDIRECTS;
    }
    
    
    /**
     * Is an HTTP error response code (4xx or 5xx)
     * @return bool
     */
    public function isHttpError() {
        return $this->value >= 400 && $this->value < 600;
    }
    
    
    /**
     * Is a CURL error code
     * @return bool
     */
    public function isCurlError() {
        return $this->value < 100;
    }


    /**
     * Is an HTTP or CURL error code
     * @return bool
     */
    public function isError() {
        return $this->isCurlError() || $this->isHttpError();
    }
    
    
    /**
     * Is a network connection error. Possibility of succesful retry
     * @return bool
     */
    public function isNetworkError() {
        return in_array($this->value, array(
            self::COULDNT_RESOLVE_PROXY,
            self::COULDNT_RESOLVE_HOST,
            self::COULDNT_CONNECT,
            self::SEND_ERROR, // is this network or system?
            self::RECV_ERROR, // is this network or system?
            self::TRY_AGAIN,
        ));
    }
    
    
    /**
     * CURL errors which should throw an exception immediately. Something is very wrong
     * @return bool
     */
    public function isFatalError() {
        return in_array($this->value, array(
            self::FAILED_INIT,
            self::OUT_OF_MEMORY,
            self::UNKNOWN_OPTION,
            self::SSL_ENGINE_NOTFOUND,
            self::SSL_ENGINE_SETFAILED,
            self::SSL_CERTPROBLEM,
            self::SSL_ENGINE_INITFAILED,
            self::INTERFACE_FAILED,
            //self::SEND_ERROR,
            //self::RECV_ERROR,
            self::CONV_REQD,
        ));
    }
    
}
