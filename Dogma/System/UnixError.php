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
 */
class UnixError extends \Dogma\Enum implements \Dogma\System\Error
{

    // common with Linux:
    const SUCCESS = 0;
    const OPERATION_NOT_PERMITTED = 1;
    const NO_SUCH_FILE_OR_DIRECTORY = 2;
    const NO_SUCH_PROCESS = 3;
    const INTERRUPTED_SYSTEM_CALL = 4;
    const IO_ERROR = 5;
    const NO_SUCH_DEVICE_OR_ADDRESS = 6;
    const ARGUMENT_LIST_TOO_LONG = 7;
    const EXEC_FORMAT_ERROR = 8;
    const BAD_FILE_NUMBER = 9;
    const NO_CHILD_PROCESSES = 10;
    const TRY_AGAIN = 11;
    const OUT_OF_MEMORY = 12;
    const PERMISSION_DENIED = 13;
    const BAD_ADDRESS = 14;
    const BLOCK_DEVICE_REQUIRED = 15;
    const DEVICE_OR_RESOURCE_BUSY = 16;
    const FILE_EXISTS = 17;
    const CROSS_DEVICE_LINK = 18;
    const NO_SUCH_DEVICE = 19;
    const NOT_A_DIRECTORY = 20;
    const IS_A_DIRECTORY = 21;
    const INVALID_ARGUMENT = 22;
    const FILE_TABLE_OVERFLOW = 23;
    const TOO_MANY_OPEN_FILES = 24;
    const NOT_A_TYPEWRITER = 25;
    const TEXT_FILE_BUSY = 26;
    const FILE_TOO_LARGE = 27;
    const NO_SPACE_LEFT_ON_DEVICE = 28;
    const ILLEGAL_SEEK = 29;
    const READONLY_FILE_SYSTEM = 30;
    const TOO_MANY_LINKS = 31;
    const BROKEN_PIPE = 32;
    const NUMERICAL_ARGUMENT_OUT_OF_DOMAIN = 33;
    const RESULT_TOO_LARGE = 34;
    const RESOURCE_TEMPORARILY_UNAVAILABLE = 35;

    // differs from Linux;
    const OPERATION_NOW_IN_PROGRESS = 36;
    const OPERATION_ALREADY_IN_PROGRESS = 37;
    const SOCKET_OPERATION_ON_NON_SOCKET = 38;
    const DESTINATION_ADDRESS_REQUIRED = 39;
    const MESSAGE_TOO_LONG = 40;
    const PROTOCOL_WRONG_TYPE_FOR_SOCKET = 41;
    const PROTOCOL_NOT_AVAILABLE = 42;
    const PROTOCOL_NOT_SUPPORTED = 43;
    const SOCKET_TYPE_NOT_SUPPORTED = 44;
    const OPERATION_NOT_SUPPORTED = 45;
    const PROTOCOL_FAMILY_NOT_SUPPORTED = 46;
    const ADDRESS_FAMILY_NOT_SUPPORTED_BY_PROTOCOL_FAMILY = 47;
    const ADDRESS_ALREADY_IN_USE = 48;
    const CANT_ASSIGN_REQUESTED_ADDRESS = 49;
    const NETWORK_IS_DOWN = 50;
    const NETWORK_IS_UNREACHABLE = 51;
    const NETWORK_DROPPED_CONNECTION_ON_RESET = 52;
    const SOFTWARE_CAUSED_CONNECTION_ABORT = 53;
    const CONNECTION_RESET_BY_PEER = 54;
    const NO_BUFFER_SPACE_AVAILABLE = 55;
    const SOCKET_IS_ALREADY_CONNECTED = 56;
    const SOCKET_IS_NOT_CONNECTED = 57;
    const CANT_SEND_AFTER_SOCKET_SHUTDOWN = 58;
    const TOO_MANY_REFERENCES_CANT_SPLICE = 59;
    const OPERATION_TIMED_OUT = 60;
    const CONNECTION_REFUSED = 61;
    const TOO_MANY_LEVELS_OF_SYMBOLIC_LINKS = 62;
    const FILE_NAME_TOO_LONG = 63;
    const HOST_IS_DOWN = 64;
    const NO_ROUTE_TO_HOST = 65;
    const DIRECTORY_NOT_EMPTY = 66;
    const TOO_MANY_PROCESSES = 67;
    const TOO_MANY_USERS = 68;
    const DISC_QUOTA_EXCEEDED = 69;
    const STALE_NFS_FILE_HANDLE = 70;
    const TOO_MANY_LEVELS_OF_REMOTE_IN_PATH = 71;
    const RPC_STRUCT_IS_BAD = 72;
    const RPC_VERSION_WRONG = 73;
    const RPC_PROG_NOT_AVAIL = 74;
    const PROGRAM_VERSION_WRONG = 75;
    const BAD_PROCEDURE_FOR_PROGRAM = 76;
    const NO_LOCKS_AVAILABLE = 77;
    const FUNCTION_NOT_IMPLEMENTED = 78;
    const INAPPROPRIATE_FILE_TYPE_OR_FORMAT = 79;
    const AUTHENTICATION_ERROR = 80;
    const NEED_AUTHENTICATOR = 81;
    const IDENTIFIER_REMOVED = 82;
    const NO_MESSAGE_OF_DESIRED_TYPE = 83;
    const VALUE_TOO_LARGE_TO_BE_STORED_IN_DATA_TYPE = 84;
    const OPERATION_CANCELED = 85;
    const ILLEGAL_BYTE_SEQUENCE = 86;
    const ATTRIBUTE_NOT_FOUND = 87;
    const PROGRAMMING_ERROR = 88;
    const BAD_MESSAGE = 89;
    const MULTIHOP_ATTEMPTED = 90;
    const LINK_HAS_BEEN_SEVERED = 91;
    const PROTOCOL_ERROR = 92;
    const CAPABILITIES_INSUFFICIENT = 93;
    const NOT_PERMITTED_IN_CAPABILITY_MODE = 94;
    const MUST_BE_EQUAL_LARGEST_ERRNO = 94;

    /**
     * Get formatted error description
     */
    public function getDescription(): string
    {
        return ucfirst(str_replace(
            ['non_socket', 'cant', 'references', 'rpc', 'nfs', 'prog_', '_'],
            ['non-socket', 'can\'t', 'references:', 'RPC', 'NFS', 'prog. ', ' '],
            strtolower($this->getConstantName())
        ));
    }

}
