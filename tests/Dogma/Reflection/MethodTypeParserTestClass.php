<?php

namespace Dogma\Tests\Reflection;

class MethodTypeParserTestClass
{

    public function testNoType($one)
    {
        //
    }

    public function testNullable($one = null)
    {
        //
    }

    public function testTwoParams($one, $two)
    {
        //
    }

    public function testArray(array $one)
    {
        //
    }

    public function testCallable(callable $one)
    {
        //
    }

    public function testClass(\Exception $one)
    {
        //
    }

    public function testSelf(self $one)
    {
        //
    }

    public function testReference(&$one)
    {
        //
    }

    public function testVariadic(...$one)
    {
        //
    }

    /**
     * @param int $one
     */
    public function testAnnotation($one)
    {
        //
    }

    /**
     * @param int $one
     */
    public function testAnnotationNullable($one = null)
    {
        //
    }

    /**
     * @param int|null $one
     */
    public function testAnnotationWithNull($one)
    {
        //
    }

    /**
     * @param int|null $one
     */
    public function testAnnotationWithNullNullable($one = null)
    {
        //
    }

    /**
     * @param \Exception $one
     */
    public function testAnnotationClass($one)
    {
        //
    }

    /**
     * @param self $one
     */
    public function testAnnotationSelf($one)
    {
        //
    }

    /**
     * @param static $one
     */
    public function testAnnotationStatic($one)
    {
        //
    }

    /**
     * @param \Exception $one
     */
    public function testAnnotationClassClass(\Exception $one)
    {
        //
    }

    /**
     * @param Exception $one
     */
    public function testAnnotationIncompleteClass($one)
    {
        //
    }

    /**
     * @param \NonExistingClass $one
     */
    public function testAnnotationNonExistingClass($one)
    {
        //
    }

    /**
     * @param int $one
     */
    public function testAnnotationNameMissmatch($two)
    {
        //
    }

    /**
     * @param int
     * @param string
     */
    public function testAnnotationWithoutName($one, $two)
    {
        //
    }

    /**
     * @param int
     */
    public function testAnnotationCountMissmatch($one, $two)
    {
        //
    }

    /**
     * @param int
     * @param string
     * @param bool
     */
    public function testAnnotationCountMissmatch2($one, $two)
    {
        //
    }

    /**
     * @param int|string $one
     */
    public function testAnnotationMoreTypes($one)
    {
        //
    }

    /**
     * @param \Exception|int[] $one
     */
    public function testAnnotationDimmensionMissmatch($one)
    {
        //
    }

    /**
     * @param int[]
     */
    public function testAnnotationArrayBrackets($one)
    {
        //
    }

    /**
     * @param int[] $one
     */
    public function testAnnotationArrayOfType(array $one)
    {
        //
    }

    /**
     * @param int[]|string[] $one
     */
    public function testAnnotationArrayOfTypes(array $one)
    {
        //
    }

    /**
     * @param \SplFixedArray|int[] $one
     */
    public function testAnnotationCollectionOfType($one)
    {
        //
    }

    /**
     * @param \SplFixedArray|int[]|string[] $one
     */
    public function testAnnotationCollectionOfTypes($one)
    {
        //
    }

    /**
     * @return int
     */
    public function testReturn()
    {
        //
    }

}
