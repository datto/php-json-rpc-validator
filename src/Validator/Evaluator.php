<?php

namespace Datto\JsonRpc\Validator;

use ReflectionMethod;
use Datto\JsonRpc;
use Datto\JsonRpc\Exception;
use Datto\JsonRpc\Simple;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\ValidatorInterface;

/**
 * Annotation-based validation decorator for the JSON-RPC Evaluator interface.
 *
 * This class pre-processes a JSON-RPC request by validating the method
 * arguments. If one or many arguments are invalid, the underlying evaluator
 * is never called. The implementation uses Symfony's validator library and
 * Doctrine's annotation library.
 *
 * @author Philipp Heckel <ph@datto.com>
 */
class Evaluator implements JsonRpc\Evaluator
{
    /** Fully qualified class name of validate annotation */
    const VALIDATE_CLASS_NAME = 'Datto\JsonRpc\Validator\Validate';

    /** Default allowed annotation namespace for constraints */
    const CONSTRAINT_ANNOTATIONS_NAMESPACE = 'Symfony\Component\Validator\Constraints';

    /** @var JsonRpc\Evaluator */
    private $evaluator;

    /** @var JsonRpc\Mapper */
    private $mapper;

    /** @var Loader */
    private static $loader;

    /**
     * Create a validating evaluator. The underlying evaluator is called
     * if all method arguments are validated successfully.
     *
     * The given mapper is used to find the validation method. If no mapper
     * is provided, a new instance of the Simple\Mapper is used.
     *
     * @param JsonRpc\Evaluator $evaluator Underlying evaluator
     * @param JsonRpc\Mapper $mapper Mapper to be used to find the validation method; Simple\Mapper is used if no mapper is provided.
     * @param string[] $namespaces Annotation namespaces (default is this namespace and the Symfony constraints namespace)
     */
    public function __construct(JsonRpc\Evaluator $evaluator, JsonRpc\Mapper $mapper = null, array $namespaces = null)
    {
        $this->evaluator = $evaluator;
        $this->mapper = ($mapper) ? $mapper : new Simple\Mapper();

        $this->registerAnnotations($namespaces);
    }

    /**
     * Validate method arguments using annotations and pass request to
     * underlying evaluator if successful.
     *
     * @param string $method Method name
     * @param array $arguments Positional or associative argument array
     * @return mixed Return value of the callable
     */
    public function evaluate($method, $arguments)
    {
        $this->validate($method, $arguments);
        return $this->evaluator->evaluate($method, $arguments);
    }

    /**
     * Uses the underlying evaluator to extract the endpoint class method
     * and its Validator\Validate annotation. If an annotation exists, the
     * validateArguments() method is called for the actual validation.
     *
     * @param string $method Method name
     * @param array $arguments Positional or associative argument array
     * @throws JsonRpc\Exception\Argument If the validation fails on any of the arguments
     */
    private function validate($method, $arguments)
    {
        /** @var Validate $validateAnnotation */

        $reader = new AnnotationReader();

        $callable = $this->mapper->getCallable($method);
        $filledArguments = $this->mapper->getArguments($callable, $arguments);

        $reflectMethod = new ReflectionMethod($callable[0], $callable[1]);
        $validateAnnotation = $reader->getMethodAnnotation($reflectMethod, self::VALIDATE_CLASS_NAME);

        if ($validateAnnotation) {
            $this->validateArguments($filledArguments, $validateAnnotation, $reflectMethod);
        }
    }

    /**
     * Validates each method arguments against the constraints specified
     * in the Validator\Validate annotation.
     *
     * @param array $filledArguments Positional array of all arguments (including not provided optional arguments)
     * @param Validate $validateAnnotation Annotation containing the argument constraints
     * @param ReflectionMethod $reflectMethod Reflection method to be used to retrieve parameters
     * @throws JsonRpc\Exception\Argument If the validation fails on any of the arguments
     */
    private function validateArguments(array $filledArguments, Validate $validateAnnotation, ReflectionMethod $reflectMethod)
    {
        $validator = Validation::createValidatorBuilder()->getValidator();

        foreach ($reflectMethod->getParameters() as $param) {
            $hasConstraints = isset($validateAnnotation->fields)
                && isset($validateAnnotation->fields[$param->getName()]);

            if ($hasConstraints) {
                $value = $filledArguments[$param->getPosition()];
                $constraints = $validateAnnotation->fields[$param->getName()]->constraints;

                $this->validateValue($value, $constraints, $validator);
            }
        }
    }

    /**
     * Validate a single value using the given constraints array and validator. If any
     * of the constraints are violated, an exception is thrown.
     *
     * @param mixed $value Argument value
     * @param Constraint[] $constraints List of constraints for the given argument
     * @param ValidatorInterface $validator Validator to be used for validation
     * @throws JsonRpc\Exception\Argument If the validation fails on the given argument
     */
    private function validateValue($value, array $constraints, ValidatorInterface $validator)
    {
        $violations = $validator->validate($value, $constraints);

        if (count($violations) > 0) {
            throw new JsonRpc\Exception\Argument();
        }
    }

    /**
     * Register annotation namespaces with Doctrine.
     *
     * This is necessary because Doctrine is autoloader-agnostic. This little hack
     * makes it use the regular Composer autoloader for the passed namespaces.
     *
     * To register annotations, a custom Loader is registered with Doctrine. Each
     * namespace is added to that loader.
     *
     * @param array $namespaces List of namespaces containing valid annotations
     */
    private function registerAnnotations($namespaces)
    {
        if (self::$loader === null) {
            self::$loader = new Loader();
            AnnotationRegistry::registerLoader(self::$loader);
        }

        if ($namespaces !== null) {
            self::$loader->addAll($namespaces);
        } else {
            self::$loader->add(self::CONSTRAINT_ANNOTATIONS_NAMESPACE);
            self::$loader->add(__NAMESPACE__);
        }
    }
}
