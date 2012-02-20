<?php

namespace Dogma\Data;


interface IFilter {
    
    /**
     * Apply condition on an object and decide if it passes.
     * @param object
     * @return integer (-1,0,1)
     */
    public function __invoke($object);
    
    /**
     * @return string
     */
    public function getOperator();
    
    /**
     * @return bool
     */
    public function isNegative();
    
    /**
     * @return string
     */
    public function getProperty();
    
    /**
     * @return mixed
     */
    public function getCounterpart();
    
    /**
     * @return Dogma\Collator
     */
    //public function getCollator();
    
}


interface IFilterList {
    
    /**
     * Apply condition on an object and decide if it passes.
     * @param object
     * @return integer (-1,0,1)
     */
    public function __invoke($object);
    
    /**
     * @return string
     */
    public function getOperator();
    
    /**
     * @return bool
     */
    public function isNegative();
    
    /**
     * @return array(ICondition|IConditionList)
     */
    public function getChildren();
    
}


interface ISorter {
    
    /**
     * Apply sort condition on two objects and decide the order.
     * @param  object|array
     * @param  object|array
     * @return integer (-1,0,1)
     */
    public function __invoke($object1, $object2);
    
    /**
     * @return string
     */
    public function getPropertyName();
    
    /**
     * @return int 1=ASC, -1=DESC
     */
    public function getDirection();
    
    /**
     * @return Dogma\Collator
     */
    //public function getCollator();
    
}


interface ISorterList {
    
    /**
     * Apply sort condition on two objects and decide the order.
     * @param  object|array
     * @param  object|array
     * @return integer (-1,0,1)
     */
    public function __invoke($object1, $object2);
    
    /**
     * @return array(ISortCondition)
     */
    public function getChildren();
    
}
