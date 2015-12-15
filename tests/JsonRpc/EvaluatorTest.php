<?php

namespace Datto\API;

use Datto\JsonRpc\Server;
use Datto\JsonRpc\Simple;
use Datto\JsonRpc\Validator;

class EvaluatorTest extends \PHPUnit_Framework_TestCase
{
    public function testMultipleConstraintsIntegerAndNotZeroSuccess()
    {
        $evaluator = new Validator\Evaluator(new Simple\Evaluator());
        $result = $evaluator->evaluate('math/divide', array(10, 2));

        $this->assertSame(5, $result);
    }

    /**
     * @expectedException \Datto\JsonRpc\Exception\Argument
     */
    public function testMultipleConstraintsIntegerAndNotZeroFails()
    {
        $evaluator = new Validator\Evaluator(new Simple\Evaluator());
        $evaluator->evaluate('math/divide', array(1, 0)); // Division by zero
    }

    public function testNoValidation()
    {
        $evaluator = new Validator\Evaluator(new Simple\Evaluator());
        $result = $evaluator->evaluate('math/add', array('a' => 2, 'b' => 3));

        $this->assertSame(5, $result);
    }

    public function testNoValidationNoDocBlock()
    {
        $evaluator = new Validator\Evaluator(new Simple\Evaluator());
        $result = $evaluator->evaluate('math/multiply', array('a' => 2, 'b' => 3));

        $this->assertSame(6, $result);
    }

    public function testNoValidationOnLastValueSucceeds()
    {
        $evaluator = new Validator\Evaluator(new Simple\Evaluator());
        $result = $evaluator->evaluate('string/noValidationOnC', array('a' => 1, 'b' => 0, 'c' => 'string'));

        $this->assertSame('10string', $result);
    }

    /**
     * @expectedException \Datto\JsonRpc\Exception\Argument
     */
    public function testNoValidationOnLastValueFails()
    {
        $evaluator = new Validator\Evaluator(new Simple\Evaluator());
        $evaluator->evaluate('string/noValidationOnC', array('a' => 1, 'b' => 'INVALID', 'c' => 'string'));
    }

    public function testValidateHexSucceeds()
    {
        $evaluator = new Validator\Evaluator(new Simple\Evaluator());
        $result = $evaluator->evaluate('string/hexToLower', array('hex' => 'ABCDEF'));

        $this->assertSame('abcdef', $result);
    }

    /**
     * @expectedException \Datto\JsonRpc\Exception\Argument
     */
    public function testValidateHexFails()
    {
        $evaluator = new Validator\Evaluator(new Simple\Evaluator());
        $evaluator->evaluate('string/hexToLower', array('hex' => 'ZZZ'));
    }

    public function testValidateOptionalArgSucceeds()
    {
        $evaluator = new Validator\Evaluator(new Simple\Evaluator());
        $result = $evaluator->evaluate('string/concat', array('a' => 'abc'));

        $this->assertSame('abcnothing', $result);
    }

    /**
     * @expectedException \Doctrine\Common\Annotations\AnnotationException
     */
    public function testInvalidAnnotation()
    {
        $evaluator = new Validator\Evaluator(new Simple\Evaluator());
        $evaluator->evaluate('math/pow', array('a' => 1, 'b' => 2));
    }

    public function testCustomAnnotationNamespace()
    {
        $evaluator = new Validator\Evaluator(new Simple\Evaluator(), null, array(
            'Datto\Annotation'
        ));

        $evaluator->evaluate('util/sqrt', array(9));
    }

    /**
     * @expectedException \Doctrine\Common\Annotations\AnnotationException
     */
    public function testCustomAnnotationNamespaceNotImported()
    {
        $evaluator = new Validator\Evaluator(new Simple\Evaluator());
        $evaluator->evaluate('math/abs', array(9));
    }
}

