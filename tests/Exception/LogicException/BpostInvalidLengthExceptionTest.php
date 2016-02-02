<?php

namespace TijsVerkoyen\Bpost\test\Exception\LogicException;

use TijsVerkoyen\Bpost\Exception\LogicException\BpostInvalidLengthException;

class BpostInvalidLengthExceptionTest extends \PHPUnit_Framework_TestCase
{
    public function testGetMessage()
    {
        $ex = new BpostInvalidLengthException('streetName', 41, 40);
        $this->assertSame('Invalid length for entry "streetName" (41 characters), maximum is 40.', $ex->getMessage());
    }
}
