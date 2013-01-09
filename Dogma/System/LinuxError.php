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
 * @property-read string $description
 */
class LinuxError extends \Dogma\Enum implements Error {

    const
        // common with Unix:
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

        // differs from Unix:
        FILE_NAME_TOO_LONG = 36,
        NO_RECORD_LOCKS_AVAILABLE = 37,
        FUNCTION_NOT_IMPLEMENTED = 38,
        DIRECTORY_NOT_EMPTY = 39,
        TOO_MANY_SYMBOLIC_LINKS_ENCOUNTERED = 40,
        NO_MESSAGE_OF_DESIRED_TYPE = 42,
        IDENTIFIER_REMOVED = 43,
        CHANNEL_NUMBER_OUT_OF_RANGE = 44,
        LEVEL_2_NOT_SYNCHRONIZED = 45,
        LEVEL_3_HALTED = 46,
        LEVEL_3_RESET = 47,
        LINK_NUMBER_OUT_OF_RANGE = 48,
        PROTOCOL_DRIVER_NOT_ATTACHED = 49,
        NO_CSI_STRUCTURE_AVAILABLE = 50,
        LEVEL_2_HALTED = 51,
        INVALID_EXCHANGE = 52,
        INVALID_REQUEST_DESCRIPTOR = 53,
        EXCHANGE_FULL = 54,
        NO_ANODE = 55,
        INVALID_REQUEST_CODE = 56,
        INVALID_SLOT = 57,
        BAD_FONT_FILE_FORMAT = 59,
        DEVICE_NOT_A_STREAM = 60,
        NO_DATA_AVAILABLE = 61,
        TIMER_EXPIRED = 62,
        OUT_OF_STREAMS_RESOURCES = 63,
        MACHINE_IS_NOT_ON_THE_NETWORK = 64,
        PACKAGE_NOT_INSTALLED = 65,
        OBJECT_IS_REMOTE = 66,
        LINK_HAS_BEEN_SEVERED = 67,
        ADVERTISE_ERROR = 68,
        SRMOUNT_ERROR = 69,
        COMMUNICATION_ERROR_ON_SEND = 70,
        PROTOCOL_ERROR = 71,
        MULTIHOP_ATTEMPTED = 72,
        RFS_SPECIFIC_ERROR = 73,
        NOT_A_DATA_MESSAGE = 74,
        VALUE_TOO_LARGE_FOR_DEFINED_DATA_TYPE = 75,
        NAME_NOT_UNIQUE_ON_NETWORK = 76,
        FILE_DESCRIPTOR_IN_BAD_STATE = 77,
        REMOTE_ADDRESS_CHANGED = 78,
        CAN_NOT_ACCESS_A_NEEDED_SHARED_LIBRARY = 79,
        ACCESSING_A_CORRUPTED_SHARED_LIBRARY = 80,
        DOT_LIB_SECTION_IN_A_OUT_CORRUPTED = 81,
        ATTEMPTING_TO_LINK_IN_TOO_MANY_SHARED_LIBRARIES = 82,
        CANNOT_EXEC_A_SHARED_LIBRARY_DIRECTLY = 83,
        ILLEGAL_BYTE_SEQUENCE = 84,
        INTERRUPTED_SYSTEM_CALL_SHOULD_BE_RESTARTED = 85,
        STREAMS_PIPE_ERROR = 86,
        TOO_MANY_USERS = 87,
        SOCKET_OPERATION_ON_NON_SOCKET = 88,
        DESTINATION_ADDRESS_REQUIRED = 89,
        MESSAGE_TOO_LONG = 90,
        PROTOCOL_WRONG_TYPE_FOR_SOCKET = 91,
        PROTOCOL_NOT_AVAILABLE = 92,
        PROTOCOL_NOT_SUPPORTED = 93,
        SOCKET_TYPE_NOT_SUPPORTED = 94,
        OPERATION_NOT_SUPPORTED_ON_TRANSPORT_ENDPOINT = 95,
        PROTOCOL_FAMILY_NOT_SUPPORTED = 96,
        ADDRESS_FAMILY_NOT_SUPPORTED_BY_PROTOCOL = 97,
        ADDRESS_ALREADY_IN_USE = 98,
        CANNOT_ASSIGN_REQUESTED_ADDRESS = 99,
        NETWORK_IS_DOWN = 100,
        NETWORK_IS_UNREACHABLE = 101,
        NETWORK_DROPPED_CONNECTION_BECAUSE_OF_RESET = 102,
        SOFTWARE_CAUSED_CONNECTION_ABORT = 103,
        CONNECTION_RESET_BY_PEER = 104,
        NO_BUFFER_SPACE_AVAILABLE = 105,
        TRANSPORT_ENDPOINT_IS_ALREADY_CONNECTED = 106,
        TRANSPORT_ENDPOINT_IS_NOT_CONNECTED = 107,
        CANNOT_SEND_AFTER_TRANSPORT_ENDPOINT_SHUTDOWN = 108,
        TOO_MANY_REFERENCES_CANNOT_SPLICE = 109,
        CONNECTION_TIMED_OUT = 110,
        CONNECTION_REFUSED = 111,
        HOST_IS_DOWN = 112,
        NO_ROUTE_TO_HOST = 113,
        OPERATION_ALREADY_IN_PROGRESS = 114,
        OPERATION_NOW_IN_PROGRESS = 115,
        STALE_NFS_FILE_HANDLE = 116,
        STRUCTURE_NEEDS_CLEANING = 117,
        NOT_A_XENIX_NAMED_TYPE_FILE = 118,
        NO_XENIX_SEMAPHORES_AVAILABLE = 119,
        IS_A_NAMED_TYPE_FILE = 120,
        REMOTE_IO_ERROR = 121,
        QUOTA_EXCEEDED = 122,
        NO_MEDIUM_FOUND = 123,
        WRONG_MEDIUM_TYPE = 124,
        OPERATION_CANCELED = 125,
        REQUIRED_KEY_NOT_AVAILABLE = 126,
        KEY_HAS_EXPIRED = 127,
        KEY_HAS_BEEN_REVOKED = 128,
        KEY_WAS_REJECTED_BY_SERVICE = 129,
        OWNER_DIED = 130,
        STATE_NOT_RECOVERABLE = 131;



    /**
     * Get formated error description
     * @return string
     */
    public function getDescription() {
        return ucfirst(str_replace(
            array('dot_lib', 'a_out', 'io', 'cross_device', 'readonly', 'non_socket', 'references', 'csi', 'rfs', 'nfs', 'xenix', '_'),
            array('.lib', 'a.out', 'I/O', 'cross-device', 'read-only', 'non-socket', 'references:', 'CSI', 'RFS', 'NFS', 'XENIX', ' '),
            strtolower($this->getIdentifier())));
    }

}
