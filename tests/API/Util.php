<?php

namespace Datto\API;

use Datto\Annotation\Awesome;

class Util
{
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
    public function sqrt($a)
    {
        return sqrt($a);
    }
}
