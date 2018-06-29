<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Time\Format;

use Dogma\StrictBehaviorMixin;

class BranchNode implements FormatNode
{
    use StrictBehaviorMixin;

    public const LIST = 1;
    public const PARENTHESES = 2;
    public const SQUARE_BRACKETS = 3;
    public const CURLY_BRACKETS = 4;

    /** @var int */
    private $type;

    /** @var \Dogma\Time\Format\BranchNode|\Dogma\Time\Format\VariableNode|string */
    private $nodes;

    /** @var int count of variable leaf nodes */
    private $cardinality;

    /** @var string[] */
    ///private $groups;

    /**
     * @param int $type
     * @param \Dogma\Time\Format\FormatNode[]|string[] $nodes
     */
    public function __construct(int $type, array $nodes)
    {
        $this->type = $type;
        $this->nodes = $nodes;
        $this->cardinality = 0;
        foreach ($nodes as $node) {
            if ($node instanceof self) {
                $this->cardinality += $node->getCardinality();
            } elseif ($node instanceof VariableNode) {
                $this->cardinality++;
            }
        }
    }

    public function getCardinality(): int
    {
        return $this->cardinality;
    }

}
