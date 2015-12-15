<?php

namespace Datto\API;

use Datto\JsonRpc\Server;
use Datto\JsonRpc\Simple;
use Datto\JsonRpc\Validator;

class ServerTest extends \PHPUnit_Framework_TestCase
{
    public function testValidArguments()
    {
        $server = new Server(new Validator\Evaluator(new Simple\Evaluator()));
        $result = $server->reply('{"jsonrpc": "2.0", "method": "math/subtract", "params": { "a": 3, "b": 2 }, "id": 1}');

        $this->assertSame('{"jsonrpc":"2.0","id":1,"result":1}', $result);
    }

    public function testIllegalArgument()
    {
        $server = new Server(new Validator\Evaluator(new Simple\Evaluator()));
        $result = $server->reply('{"jsonrpc": "2.0", "method": "math/subtract", "params": { "a": "INVALID", "b": 2 }, "id": 1}');

        $this->assertSame('{"jsonrpc":"2.0","id":1,"error":{"code":-32602,"message":"Invalid params"}}', $result);
    }
}

