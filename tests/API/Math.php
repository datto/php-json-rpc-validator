<?php

namespace Datto\API;

use Datto\JsonRpc\Validator\Validate;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @author Doctrine doesn't like this?
 */
class Math
{
    /**
     * @Validate(fields={
     *   "a" = @Assert\Type(type="integer"),
     *   "b" = @Assert\Type(type="integer")
     * })
     */
    public function subtract($a, $b)
    {
        return $a - $b;
    }

    /**
     * @Validate(fields={
     *   "a" = @Assert\Type(type="integer"),
     *   "b" = {
     *     @Assert\Type(type="integer"),
     *     @Assert\NotEqualTo(value="0"),
     *   }
     * })
     */
    public function divide($a, $b)
    {
        return $a / $b;
    }

    /**
     * No validation!
     */
    public function add($a, $b)
    {
        return $a + $b;
    }

    // No doc block!
    public function multiply($a, $b)
    {
        return $a * $b;
    }

    /**
     * @invalid
     */
    public function pow($a, $b)
    {
        return pow($a, $b);
    }

    /**
     * @Awesome
     */
    public function abs($a)
    {
        return abs($a);
    }
}
