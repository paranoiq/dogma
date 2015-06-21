<?php

namespace Dogma\Mapping;

use Dogma\Mapping\Type\Handler;
use Dogma\ReverseArrayIterator;
use Dogma\Type;

class Mapping
{
    use \Dogma\StrictBehaviorMixin;

    /** @var \Dogma\Type */
    private $type;

    /** @var \Dogma\Mapping\MappingStep[] */
    private $steps = [];

    /** @var \Dogma\Mapping\MappingStep[]|ReverseArrayIterator */
    private $reverseSteps;

    /**
     * @param \Dogma\Type $type
     * @param mixed[] $mapping
     */
    public function __construct(Type $type, array $steps)
    {
        $this->type = $type;
        $this->steps = $steps;

        $this->reverseSteps = new ReverseArrayIterator($steps);
    }

    /**
     * @return \Dogma\Mapping\MappingStep[]
     */
    public function getSteps(): array
    {
        return $this->steps;
    }

    /**
     * @param mixed[] $data
     * @param \Dogma\Mapping\Mapper $mapper
     * @return mixed
     */
    public function mapForward(array $data, Mapper $mapper)
    {
        foreach ($this->steps as $step) {
            $step->stepForward($data, $mapper);
        }
        return $data[Handler::SINGLE_PARAMETER];
    }

    /**
     * @param mixed $instance
     * @param \Dogma\Mapping\Mapper $mapper
     * @return mixed[]
     */
    public function mapBack($instance, Mapper $mapper)
    {
        $data = [Handler::SINGLE_PARAMETER => $instance];
        foreach ($this->reverseSteps as $step) {
            $step->stepBack($data, $mapper);
        }
        return $data;
    }

}
