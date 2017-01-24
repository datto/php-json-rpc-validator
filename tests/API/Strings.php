<?php

namespace Datto\API;

use Datto\JsonRpc\Validator\Validate;
use Symfony\Component\Validator\Constraints as Assert;

class Strings
{
    /**
     * @Validate(fields={
     *   "a" = @Assert\Type(type="integer"),
     *   "b" = @Assert\Type(type="integer")
     * })
     */
    public function noValidationOnC($a, $b, $c)
    {
        return $a . $b . $c;
    }

    /**
     * @Validate(fields={
     *   "hex" = @Assert\Regex(pattern="/^[0-9a-f]+$/i")
     * })
     */
    public function hexToLower($hex)
    {
        return strtolower($hex);
    }

    /**
     * @Validate(fields={
     *   "a" = @Assert\Regex(pattern = "~^[[:alnum:]]+$~"),
     *   "b" = @Assert\Choice(choices = { "nothing", "something" })
     * })
     */
    public function concat($a, $b = 'nothing')
    {
        return $a . $b;
    }
}
