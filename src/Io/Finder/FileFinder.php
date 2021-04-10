<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Io;

use DateTimeInterface;
use Dogma\Io\ContentType\ContentTypeDetector;
use Dogma\Pack;
use Dogma\StrictBehaviorMixin;
use function array_merge;
use function is_array;

/**
 * File and directories finder
 *
 * Alternative to:
 * - https://symfony.com/doc/current/components/finder.html
 * - https://doc.nette.org/cs/3.0/finder
 */
class FileFinder
{
    use StrictBehaviorMixin;

    private const FILES = 1;
    private const DIRECTORIES = 2;

    /** @var string */
    private $baseDir;

    /** @var ContentTypeDetector|null */
    private $contentTypeDetector;

    /** @var int */
    private $search;

    /** @var bool */
    private $followSymlinks = true;

    /** @var callable|null */
    private $filter;

    /** @var string[][]|null ($depth => $dirs) */
    private $directories;

    /** @var string[]|null */
    private $notDirectories;

    /** @var string[]|null */
    private $files;

    /** @var string[]|null */
    private $notFiles;

    /** @var array<string, bool>|null ($pattern, $include) */
    private $matching = [];

    /** @var array<string, bool>|null ($pattern, $include) */
    private $extensions = [];

    /** @var array<string, bool>|null ($pattern, $include) */
    private $contentTypes = [];

    /** @var array<string, bool>|null ($pattern, $include) */
    private $containing = [];

    /** @var array<string, int> ($operator, $timestamp) */
    private $modified = [];

    /** @var array<string, int> ($operator, $timestamp) */
    private $changed = [];

    /** @var array<string, int> ($operator, $bytes) */
    private $size = [];

    /** @var bool */
    private $skipNonreadable = false;

    /** @var bool */
    private $skipDotfiles = false;

    /** @var string|callable|null */
    private $orderBy;

    /** @var int|null */
    private $order;

    /** @var string[]|null */
    private $first;

    /**
     * @param string|Path|null $baseDir
     * @param ContentTypeDetector|null $contentTypeDetector
     */
    public function __construct(
        $baseDir = null,
        ?ContentTypeDetector $contentTypeDetector = null
    ) {
        if ($baseDir instanceof Path) {
            $baseDir = $baseDir->getPath();
        }

        $this->baseDir = $baseDir !== null
            ? Io::normalizePath($baseDir)
            : Io::normalizePath(Io::getWorkingDirectory());
        if ($this->baseDir !== '') {
            $this->baseDir .= '/';
        }

        $this->contentTypeDetector = $contentTypeDetector;
    }

    public function find(): self
    {
        $this->search = self::FILES | self::DIRECTORIES;

        return $this;
    }

    public function findFiles(): self
    {
        $this->search = self::FILES;

        return $this;
    }

    public function findDirectories(): self
    {
        $finder = new self();
        $finder->search = self::DIRECTORIES;

        return $finder;
    }

    public function followSymlinks(bool $follow = true): self
    {
        $this->followSymlinks = $follow;

        return $this;
    }

    public function filter(callable $filter): self
    {
        $this->filter = $filter;

        return $this;
    }

    // paths -----------------------------------------------------------------------------------------------------------

    /**
     * @param string|string[]|Path|Path[] $directories
     * @param int $maxDepth
     * @return self
     */
    public function in($directories, int $maxDepth = 0): self
    {
        if (!is_array($directories)) {
            $directories = [$directories];
        }

        $directories = self::pathsToStrings($directories);

        if (!isset($this->directories[$maxDepth])) {
            $this->directories[$maxDepth] = $directories;
        } else {
            $this->directories[$maxDepth] = array_merge($this->directories[$maxDepth], $directories);
        }

        return $this;
    }

    /**
     * @param string|string[]|Path|Path[] $directories
     * @return self
     */
    public function notIn($directories): self
    {
        if (!is_array($directories)) {
            $directories = [$directories];
        }

        $directories = self::pathsToStrings($directories);

        $this->notDirectories = $this->notDirectories !== null
            ? array_merge($this->notDirectories, $directories)
            : $directories;

        return $this;
    }

    /**
     * @param string[]|Path[] $files
     * @return self
     */
    public function inFiles(array $files): self
    {
        $files = self::pathsToStrings($files);

        $this->files = $this->files !== null
            ? array_merge($this->files, $files)
            : $files;

        return $this;
    }

    /**
     * @param string[]|Path[] $files
     * @return self
     */
    public function notFiles(array $files): self
    {
        $files = self::pathsToStrings($files);

        $this->notFiles = $this->notFiles !== null
            ? array_merge($this->notFiles, $files)
            : $files;

        return $this;
    }

    /**
     * @param string|string[] $patterns
     * @return self
     */
    public function matching($patterns): self
    {
        if (!is_array($patterns)) {
            $patterns = [$patterns];
        }

        foreach ($patterns as $pattern) {
            $this->matching[$pattern] = true;
        }

        return $this;
    }

    /**
     * @param string|string[] $patterns
     * @return self
     */
    public function notMatching($patterns): self
    {
        if (!is_array($patterns)) {
            $patterns = [$patterns];
        }

        foreach ($patterns as $pattern) {
            $this->matching[$pattern] = false;
        }

        return $this;
    }

    /**
     * @param string|string[] $extensions
     * @return self
     */
    public function extension($extensions): self
    {
        if (!is_array($extensions)) {
            $extensions = [$extensions];
        }

        foreach ($extensions as $extension) {
            $this->matching[$extension] = true;
        }

        return $this;
    }

    /**
     * @param string|string[] $extensions
     * @return self
     */
    public function notExtension($extensions): self
    {
        if (!is_array($extensions)) {
            $extensions = [$extensions];
        }

        foreach ($extensions as $extension) {
            $this->matching[$extension] = false;
        }

        return $this;
    }

    // contents --------------------------------------------------------------------------------------------------------

    /**
     * @param string|string[] $types
     * @return self $this
     */
    public function contentType($types): self
    {
        if (!is_array($types)) {
            $types = [$types];
        }

        foreach ($types as $type) {
            $this->contentTypes[$type] = true;
        }

        return $this;
    }

    /**
     * @param string|string[] $types
     * @return self $this
     */
    public function notContentType($types): self
    {
        if (!is_array($types)) {
            $types = [$types];
        }

        foreach ($types as $type) {
            $this->contentTypes[$type] = true;
        }

        return $this;
    }

    /**
     * @param string|string[] $patterns
     * @return self
     */
    public function containing($patterns): self
    {
        // todo
        return $this;
    }

    /**
     * @param string|string[] $patterns
     * @return self
     */
    public function notContaining($patterns): self
    {
        // todo
        return $this;
    }

    // meta data -------------------------------------------------------------------------------------------------------

    /**
     * @param string $operator
     * @param string|DateTimeInterface $time
     * @return self
     */
    public function modified(string $operator, $time): self
    {
        // todo
        return $this;
    }

    /**
     * @param string $operator
     * @param string|DateTimeInterface $time
     * @return self
     */
    public function changed(string $operator, $time): self
    {
        // todo
        return $this;
    }

    /**
     * @param string $operator
     * @param int|string $size
     * @return self
     */
    public function size(string $operator, $size): self
    {
        // todo
        return $this;
    }

    // skip ------------------------------------------------------------------------------------------------------------

    public function skipNonreadable(): self
    {
        // todo
        return $this;
    }

    public function skipDotFiles(): self
    {
        // todo
        return $this;
    }

    /*
    public function skipVcsFiles(): self
    {

    }

    public function skipVcsIgnored(): self
    {

    }
    */

    // ordering --------------------------------------------------------------------------------------------------------

    /**
     * List of files that should be always first
     *
     * @param string[]|Path[] $files
     * @return self
     */
    public function first(array $files): self
    {
        $this->first = self::pathsToStrings($files);

        return $this;
    }

    /**
     * @param string|callable $what Dogma\Io\Finder\OrderBy constants or callback
     * @param int $order Dogma\Order constants
     * @return self
     */
    public function orderBy($what, int $order): self
    {
        $this->orderBy = $what;
        $this->order = $order;

        return $this;
    }

    /**
     * @param string[]|Path[] $paths
     * @return string[]
     */
    private static function pathsToStrings(array $paths): array
    {
        $result = [];
        foreach ($paths as $i => $path) {
            $result[] = $path instanceof Path ? $path->getPath() : $path;
        }

        return $result;
    }

}
