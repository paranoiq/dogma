<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Database;


/** Database error. */
class DatabaseException extends \Exception {}



/** Server is temporarily unavailable. Connection refused or aborted. */
class ServiceUnavailableException extends DatabaseException {}

/** Connection failure. */
class ConnectionErrorException extends DatabaseException {}

/** Insufficient user privileges. */
class AccessDeniedException extends DatabaseException {}

/** Exception caused by user mistake. */
class QueryException extends DatabaseException {}

    /** Statement syntax error caused by user. */
    class SyntaxErrorException extends QueryException {}

        /** Invalid value given by user or returned by query. */
        class InvalidValueException extends SyntaxErrorException {}

        /** Text collation error or mismatch. */
        class CollationException extends SyntaxErrorException {}

    /** Statement logic error. You are doing something wrong. */
    class LogicErrorException extends QueryException {}

        /** Operation is not suported in this context, configuration or version. */
        class NotSupportedException extends LogicErrorException {}

        /** Entity (table, column, index...) already exists. */
        class NameConflictException extends LogicErrorException {}

    /** Integrity constraint error. */
    class IntegrityConstraintException extends QueryException {}

        /** Duplicate entry (integrity constraint error). */
        class DuplicateEntryException extends IntegrityConstraintException {}

    /** Entity (table, column, index...) was not found. */
    class NotFoundException extends QueryException {}

    /** Debuging and unhandled user errors. */
    class DebugException extends QueryException {}

/** Concurency issues. */
class ConcurencyException extends DatabaseException {}

    /** Cannot perform operation due to locks. */
    class LockException extends ConcurencyException {}

        /** Deadlock. Cannot be prevented. Run transaction again. */
        class DeadlockException extends LockException {}

    /** Operation was aborted by admin. */
    class AbortedException extends ConcurencyException {}

/** Exception caused by admin, configuration, runtime or system failure. */
class FailureException extends DatabaseException {}

    /** Wrong or unreadable configuration file. */
    class ConfigException extends FailureException {}

    /** Resource or configuration limit reached. */
    class ResourcesException extends FailureException {}

        /** System resources (RAM, disk, threads...) temporarily depleeted. */
        class InsufficientResourcesException extends ResourcesException {}

        /** Configured or native database limits exceeded. */
        class LimitsExceededException extends ResourcesException {}

    /** Miscelanous runtime exceptions (probably not caused by user). */
    class RuntimeException extends FailureException {}

        /** Database storage errors caused by the underlying filesystem. */
        class StorageException extends RuntimeException {}

    /** Corrupted or obsolete database structures. */
    class CorruptedException extends FailureException {}

/** Binary logging error. */
class BinlogException extends DatabaseException {}

/** Master/slave replication error. */
class ReplicationException extends DatabaseException {}

/** Events or event scheduler error. */
class EventException extends DatabaseException {}

/** User extensions or UDF error. */
class ExtensionException extends DatabaseException {}

/** Remote server or FEDERATED engine error. */
class FederatedException extends DatabaseException {}

/** Table partitioning error. */
class PartitioningException extends DatabaseException {}

/** Tablespace and file groups error. */
class FilesException extends DatabaseException {}

/** Distributed 'XA' transaction error. */
class XaException extends DatabaseException {}
