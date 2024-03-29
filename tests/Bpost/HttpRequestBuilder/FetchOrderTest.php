<?php

namespace Tests\Bpost\HttpRequestBuilder;

use Bpost\BpostApiClient\Bpost\HttpRequestBuilder\FetchOrderBuilder;
use PHPUnit_Framework_TestCase;

class FetchOrderTest extends PHPUnit_Framework_TestCase
{
    /**
     * @param array  $input
     * @param string $url
     * @param string $xml
     * @param string $method
     * @param bool   $isExpectXml
     * @param array  $headers
     *
     * @return void
     *
     * @dataProvider dataResults
     */
    public function testResults(array $input, $url, $xml, $headers, $method, $isExpectXml)
    {
        $builder = new FetchOrderBuilder($input[0]);

        $this->assertSame($url, $builder->getUrl());
        $this->assertSame($method, $builder->getMethod());
        $this->assertSame($xml, $builder->getXml());
        $this->assertSame($isExpectXml, $builder->isExpectXml());
        $this->assertSame($headers, $builder->getHeaders());
    }

    public function dataResults()
    {
        return array(
            array(
                'input' => array('123'),
                'url' => '/orders/123',
                'xml' => null,
                'headers' => array('Accept: application/vnd.bpost.shm-order-v3.5+XML'),
                'method' => 'GET',
                'isExpectXml' => true,
            ),
        );
    }
}
