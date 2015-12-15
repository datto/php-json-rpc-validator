<?php

namespace Datto\JsonRpc\Validator;

use Symfony\Component\Validator\Constraints\Collection;

/**
 * Annotation to be used to mark API endpoint classes for
 * validation.
 *
 * While not exactly semantically correct, Symfony's Constrains\Collection class
 * is used as a basis, because it allows specifying constraints for map-type
 * arguments, and can hence be used for methods.
 *
 * @Annotation
 * @author Philipp Heckel <ph@datto.com>
 */
class Validate extends Collection
{
    // Marker class
}
