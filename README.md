# JSON-RPC Endpoint Validator Extension

This is a validation extension for the [php-json-rpc](https://github.com/datto/php-json-rpc) library. It depends on [symfony/Validator](https://github.com/symfony/Validator), [doctrine/annotations](https://github.com/doctrine/annotations) as well as on [php-json-rpc-simple](https://github.com/datto/php-json-rpc-simple). 

Examples
--------
Annotate your API endpoint classes like this:

```php
namespace Datto\API;

use Datto\JsonRpc\Validator\Validate;
use Symfony\Component\Validator\Constraints as Assert;

class Math
{
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
```

Once you have that, just use it like this. This example uses the `Simple\Evaluator` (see [php-json-rpc-simple](https://github.com/datto/php-json-rpc-simple)) as underlying mapping mechanism:

```php
$server = new Server(new Validator\Evaluator(new Simple\Evaluator()));
$result = $server->reply('{"jsonrpc": "2.0", "method": "math/divide", "params": { "a": 1, "b": 0 }, "id": 1}');

// Because 'b' cannot be 0, this will return
// {"jsonrpc":"2.0","id":1,"error":{"code":-32602,"message":"Invalid params"}}
```

Requirements
------------
* PHP >= 5.3

Installation
------------
```javascript
"require": {
  "datto/json-rpc-validator": "~2.0"
}
```   

License
-------
This package is released under an open-source license: [LGPL-3.0](https://www.gnu.org/licenses/lgpl-3.0.html).

Author
------
Written by [Philipp C. Heckel](https://github.com/binwiederhier).
