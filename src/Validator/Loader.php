<?php

namespace Datto\JsonRpc\Validator;

/**
 * Dummy class loader to trick Doctrine into believing every class in the
 * given namespaces exists.
 *
 * This is necessary to avoid having to manually add the loader path
 * with AnnotationRegistry::registerAutoloadNamespace().
 *
 * The implementation of this class is taken from
 *   http://blog.riff.org/2014_02_16_reducing_redundancy_in_doctrine_annotations_loading
 *
 * @author Philipp Heckel <ph@datto.com>
 */
class Loader
{
    /** @var array Namespaces with annotations */
    protected $namespaces;

    public function __construct(array $namespaces = array())
    {
        $this->namespaces = $namespaces;
    }

    public function addAll($namespaces)
    {
        foreach ($namespaces as $namespace) {
            $this->add($namespace);
        }
    }

    public function add($namespace)
    {
        if (!in_array($namespace, $this->namespaces)) {
            $this->namespaces[] = $namespace;
        }
    }

    public function __invoke($name)
    {
        foreach ($this->namespaces as $namespace) {
            if (strpos($name, $namespace) === 0) {
                return true;
            }
        }

        return false;
    }
}