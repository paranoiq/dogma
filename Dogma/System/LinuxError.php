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
 * Linux system errors
 */
class LinuxError extends \Dogma\Enum implements \Dogma\System\Error
{

    // common with Unix:
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

    // differs from Unix;
    const FILE_NAME_TOO_LONG = 36;
    const NO_RECORD_LOCKS_AVAILABLE = 37;
    const FUNCTION_NOT_IMPLEMENTED = 38;
    const DIRECTORY_NOT_EMPTY = 39;
    const TOO_MANY_SYMBOLIC_LINKS_ENCOUNTERED = 40;
    const NO_MESSAGE_OF_DESIRED_TYPE = 42;
    const IDENTIFIER_REMOVED = 43;
    const CHANNEL_NUMBER_OUT_OF_RANGE = 44;
    const LEVEL_2_NOT_SYNCHRONIZED = 45;
    const LEVEL_3_HALTED = 46;
    const LEVEL_3_RESET = 47;
    const LINK_NUMBER_OUT_OF_RANGE = 48;
    const PROTOCOL_DRIVER_NOT_ATTACHED = 49;
    const NO_CSI_STRUCTURE_AVAILABLE = 50;
    const LEVEL_2_HALTED = 51;
    const INVALID_EXCHANGE = 52;
    const INVALID_REQUEST_DESCRIPTOR = 53;
    const EXCHANGE_FULL = 54;
    const NO_ANODE = 55;
    const INVALID_REQUEST_CODE = 56;
    const INVALID_SLOT = 57;
    const BAD_FONT_FILE_FORMAT = 59;
    const DEVICE_NOT_A_STREAM = 60;
    const NO_DATA_AVAILABLE = 61;
    const TIMER_EXPIRED = 62;
    const OUT_OF_STREAMS_RESOURCES = 63;
    const MACHINE_IS_NOT_ON_THE_NETWORK = 64;
    const PACKAGE_NOT_INSTALLED = 65;
    const OBJECT_IS_REMOTE = 66;
    const LINK_HAS_BEEN_SEVERED = 67;
    const ADVERTISE_ERROR = 68;
    const SRMOUNT_ERROR = 69;
    const COMMUNICATION_ERROR_ON_SEND = 70;
    const PROTOCOL_ERROR = 71;
    const MULTIHOP_ATTEMPTED = 72;
    const RFS_SPECIFIC_ERROR = 73;
    const NOT_A_DATA_MESSAGE = 74;
    const VALUE_TOO_LARGE_FOR_DEFINED_DATA_TYPE = 75;
    const NAME_NOT_UNIQUE_ON_NETWORK = 76;
    const FILE_DESCRIPTOR_IN_BAD_STATE = 77;
    const REMOTE_ADDRESS_CHANGED = 78;
    const CAN_NOT_ACCESS_A_NEEDED_SHARED_LIBRARY = 79;
    const ACCESSING_A_CORRUPTED_SHARED_LIBRARY = 80;
    const DOT_LIB_SECTION_IN_A_OUT_CORRUPTED = 81;
    const ATTEMPTING_TO_LINK_IN_TOO_MANY_SHARED_LIBRARIES = 82;
    const CANNOT_EXEC_A_SHARED_LIBRARY_DIRECTLY = 83;
    const ILLEGAL_BYTE_SEQUENCE = 84;
    const INTERRUPTED_SYSTEM_CALL_SHOULD_BE_RESTARTED = 85;
    const STREAMS_PIPE_ERROR = 86;
    const TOO_MANY_USERS = 87;
    const SOCKET_OPERATION_ON_NON_SOCKET = 88;
    const DESTINATION_ADDRESS_REQUIRED = 89;
    const MESSAGE_TOO_LONG = 90;
    const PROTOCOL_WRONG_TYPE_FOR_SOCKET = 91;
    const PROTOCOL_NOT_AVAILABLE = 92;
    const PROTOCOL_NOT_SUPPORTED = 93;
    const SOCKET_TYPE_NOT_SUPPORTED = 94;
    const OPERATION_NOT_SUPPORTED_ON_TRANSPORT_ENDPOINT = 95;
    const PROTOCOL_FAMILY_NOT_SUPPORTED = 96;
    const ADDRESS_FAMILY_NOT_SUPPORTED_BY_PROTOCOL = 97;
    const ADDRESS_ALREADY_IN_USE = 98;
    const CANNOT_ASSIGN_REQUESTED_ADDRESS = 99;
    const NETWORK_IS_DOWN = 100;
    const NETWORK_IS_UNREACHABLE = 101;
    const NETWORK_DROPPED_CONNECTION_BECAUSE_OF_RESET = 102;
    const SOFTWARE_CAUSED_CONNECTION_ABORT = 103;
    const CONNECTION_RESET_BY_PEER = 104;
    const NO_BUFFER_SPACE_AVAILABLE = 105;
    const TRANSPORT_ENDPOINT_IS_ALREADY_CONNECTED = 106;
    const TRANSPORT_ENDPOINT_IS_NOT_CONNECTED = 107;
    const CANNOT_SEND_AFTER_TRANSPORT_ENDPOINT_SHUTDOWN = 108;
    const TOO_MANY_REFERENCES_CANNOT_SPLICE = 109;
    const CONNECTION_TIMED_OUT = 110;
    const CONNECTION_REFUSED = 111;
    const HOST_IS_DOWN = 112;
    const NO_ROUTE_TO_HOST = 113;
    const OPERATION_ALREADY_IN_PROGRESS = 114;
    const OPERATION_NOW_IN_PROGRESS = 115;
    const STALE_NFS_FILE_HANDLE = 116;
    const STRUCTURE_NEEDS_CLEANING = 117;
    const NOT_A_XENIX_NAMED_TYPE_FILE = 118;
    const NO_XENIX_SEMAPHORES_AVAILABLE = 119;
    const IS_A_NAMED_TYPE_FILE = 120;
    const REMOTE_IO_ERROR = 121;
    const QUOTA_EXCEEDED = 122;
    const NO_MEDIUM_FOUND = 123;
    const WRONG_MEDIUM_TYPE = 124;
    const OPERATION_CANCELED = 125;
    const REQUIRED_KEY_NOT_AVAILABLE = 126;
    const KEY_HAS_EXPIRED = 127;
    const KEY_HAS_BEEN_REVOKED = 128;
    const KEY_WAS_REJECTED_BY_SERVICE = 129;
    const OWNER_DIED = 130;
    const STATE_NOT_RECOVERABLE = 131;

    /**
     * Get formated error description
     */
    public function getDescription(): string
    {
        return ucfirst(str_replace(
            ['dot_lib', 'a_out', 'io', 'cross_device', 'readonly', 'non_socket', 'references', 'csi', 'rfs', 'nfs', 'xenix', '_'],
            ['.lib', 'a.out', 'I/O', 'cross-device', 'read-only', 'non-socket', 'references:', 'CSI', 'RFS', 'NFS', 'XENIX', ' '],
            strtolower($this->getConstantName())
        ));
    }

}
