<?php

namespace Tests\Bpost\HttpRequestBuilder;

use Bpost\BpostApiClient\Bpost\HttpRequestBuilder\ModifyOrderBuilder;
use Bpost\BpostApiClient\Exception\BpostLogicException\BpostInvalidValueException;
use PHPUnit_Framework_TestCase;

class ModifyOrderTest extends PHPUnit_Framework_TestCase
{
    /**
     * @param array  $input
     * @param string $url
     * @param string $xml
     * @param array  $headers
     * @param string $method
     * @param bool   $isExpectXml
     *
     * @throws BpostInvalidValueException
     *
     * @dataProvider dataResults
     */
    public function testResults(array $input, $url, $xml, $headers, $method, $isExpectXml)
    {
        $builder = new ModifyOrderBuilder($input[0], $input[1]);

        $this->assertSame($url, $builder->getUrl());
        $this->assertSame($method, $builder->getMethod());
        $this->assertSame($xml, $builder->getXml());
        $this->assertSame($isExpectXml, $builder->isExpectXml());
        $this->assertSame($headers, $builder->getHeaders());
    }

    public function testInvalidValue()
    {
        $this->expectException('Bpost\BpostApiClient\Exception\BpostLogicException\BpostInvalidValueException');
        new ModifyOrderBuilder('123', 'maybe');
    }

    public function dataResults()
    {
        return array(
            array(
                'input' => array('123', 'pending'),
                'url' => '/orders/123',
                'xml' => '<?xml version="1.0" encoding="utf-8"?>
<orderUpdate xmlns="http://schema.post.be/shm/deepintegration/v5/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
  <status>PENDING</status>
</orderUpdate>
',
                'headers' => array('Content-type: application/vnd.bpost.shm-orderUpdate-v3+XML'),
                'method' => 'POST',
                'isExpectXml' => false,
            ),
        );
    }
}
