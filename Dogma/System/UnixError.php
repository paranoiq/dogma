<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\System;

/**
 * UNIX system errors (from FreeBSD)
 * @property-read string $description
 */
class UnixError extends \Dogma\Enum implements Error {

    const
        // common with Linux:
        SUCCESS = 0,
        OPERATION_NOT_PERMITTED = 1,
        NO_SUCH_FILE_OR_DIRECTORY = 2,
        NO_SUCH_PROCESS = 3,
        INTERRUPTED_SYSTEM_CALL = 4,
        IO_ERROR = 5,
        NO_SUCH_DEVICE_OR_ADDRESS = 6,
        ARGUMENT_LIST_TOO_LONG = 7,
        EXEC_FORMAT_ERROR = 8,
        BAD_FILE_NUMBER = 9,
        NO_CHILD_PROCESSES = 10,
        TRY_AGAIN = 11,
        OUT_OF_MEMORY = 12,
        PERMISSION_DENIED = 13,
        BAD_ADDRESS = 14,
        BLOCK_DEVICE_REQUIRED = 15,
        DEVICE_OR_RESOURCE_BUSY = 16,
        FILE_EXISTS = 17,
        CROSS_DEVICE_LINK = 18,
        NO_SUCH_DEVICE = 19,
        NOT_A_DIRECTORY = 20,
        IS_A_DIRECTORY = 21,
        INVALID_ARGUMENT = 22,
        FILE_TABLE_OVERFLOW = 23,
        TOO_MANY_OPEN_FILES = 24,
        NOT_A_TYPEWRITER = 25,
        TEXT_FILE_BUSY = 26,
        FILE_TOO_LARGE = 27,
        NO_SPACE_LEFT_ON_DEVICE = 28,
        ILLEGAL_SEEK = 29,
        READONLY_FILE_SYSTEM = 30,
        TOO_MANY_LINKS = 31,
        BROKEN_PIPE = 32,
        NUMERICAL_ARGUMENT_OUT_OF_DOMAIN = 33,
        RESULT_TOO_LARGE = 34,
        RESOURCE_TEMPORARILY_UNAVAILABLE = 35,

        // differs from Linux:
        OPERATION_NOW_IN_PROGRESS = 36,
        OPERATION_ALREADY_IN_PROGRESS = 37,
        SOCKET_OPERATION_ON_NON_SOCKET = 38,
        DESTINATION_ADDRESS_REQUIRED = 39,
        MESSAGE_TOO_LONG = 40,
        PROTOCOL_WRONG_TYPE_FOR_SOCKET = 41,
        PROTOCOL_NOT_AVAILABLE = 42,
        PROTOCOL_NOT_SUPPORTED = 43,
        SOCKET_TYPE_NOT_SUPPORTED = 44,
        OPERATION_NOT_SUPPORTED = 45,
        PROTOCOL_FAMILY_NOT_SUPPORTED = 46,
        ADDRESS_FAMILY_NOT_SUPPORTED_BY_PROTOCOL_FAMILY = 47,
        ADDRESS_ALREADY_IN_USE = 48,
        CANT_ASSIGN_REQUESTED_ADDRESS = 49,
        NETWORK_IS_DOWN = 50,
        NETWORK_IS_UNREACHABLE = 51,
        NETWORK_DROPPED_CONNECTION_ON_RESET = 52,
        SOFTWARE_CAUSED_CONNECTION_ABORT = 53,
        CONNECTION_RESET_BY_PEER = 54,
        NO_BUFFER_SPACE_AVAILABLE = 55,
        SOCKET_IS_ALREADY_CONNECTED = 56,
        SOCKET_IS_NOT_CONNECTED = 57,
        CANT_SEND_AFTER_SOCKET_SHUTDOWN = 58,
        TOO_MANY_REFERENCES_CANT_SPLICE = 59,
        OPERATION_TIMED_OUT = 60,
        CONNECTION_REFUSED = 61,
        TOO_MANY_LEVELS_OF_SYMBOLIC_LINKS = 62,
        FILE_NAME_TOO_LONG = 63,
        HOST_IS_DOWN = 64,
        NO_ROUTE_TO_HOST = 65,
        DIRECTORY_NOT_EMPTY = 66,
        TOO_MANY_PROCESSES = 67,
        TOO_MANY_USERS = 68,
        DISC_QUOTA_EXCEEDED = 69,
        STALE_NFS_FILE_HANDLE = 70,
        TOO_MANY_LEVELS_OF_REMOTE_IN_PATH = 71,
        RPC_STRUCT_IS_BAD = 72,
        RPC_VERSION_WRONG = 73,
        RPC_PROG_NOT_AVAIL = 74,
        PROGRAM_VERSION_WRONG = 75,
        BAD_PROCEDURE_FOR_PROGRAM = 76,
        NO_LOCKS_AVAILABLE = 77,
        FUNCTION_NOT_IMPLEMENTED = 78,
        INAPPROPRIATE_FILE_TYPE_OR_FORMAT = 79,
        AUTHENTICATION_ERROR = 80,
        NEED_AUTHENTICATOR = 81,
        IDENTIFIER_REMOVED = 82,
        NO_MESSAGE_OF_DESIRED_TYPE = 83,
        VALUE_TOO_LARGE_TO_BE_STORED_IN_DATA_TYPE = 84,
        OPERATION_CANCELED = 85,
        ILLEGAL_BYTE_SEQUENCE = 86,
        ATTRIBUTE_NOT_FOUND = 87,
        PROGRAMMING_ERROR = 88,
        BAD_MESSAGE = 89,
        MULTIHOP_ATTEMPTED = 90,
        LINK_HAS_BEEN_SEVERED = 91,
        PROTOCOL_ERROR = 92,
        CAPABILITIES_INSUFFICIENT = 93,
        NOT_PERMITTED_IN_CAPABILITY_MODE = 94,
        MUST_BE_EQUAL_LARGEST_ERRNO = 94;



    /**
     * Get formated error description
     * @return string
     */
    public function getDescription() {
        return ucfirst(str_replace(
            array('non_socket', 'cant', 'references', 'rpc', 'nfs', 'prog_', '_'),
            array('non-socket', 'can\'t', 'references:', 'RPC', 'NFS', 'prog. ', ' '),
            strtolower($this->getIdentifier())));
    }

}
