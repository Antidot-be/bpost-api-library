<?php

namespace Tests\Bpost\HttpRequestBuilder;

use Bpost\BpostApiClient\Bpost\HttpRequestBuilder\CreateLabelForBoxBuilder;
use Bpost\BpostApiClient\Common\ValidatedValue\LabelFormat;
use PHPUnit_Framework_TestCase;

class CreateLabelForBoxTest extends PHPUnit_Framework_TestCase
{
    /**
     * @param array  $input
     * @param string $url
     * @param array  $headers
     * @param string $xml
     * @param string $method
     * @param bool   $isExpectXml
     *
     * @return void
     *
     * @dataProvider dataResults
     */
    public function testResults(array $input, $url, $headers, $xml, $method, $isExpectXml)
    {
        $builder = new CreateLabelForBoxBuilder($input[0], $input[1], $input[2], $input[3]);
        $this->assertSame($url, $builder->getUrl());
        $this->assertSame($method, $builder->getMethod());
        $this->assertSame($xml, $builder->getXml());
        $this->assertSame($isExpectXml, $builder->isExpectXml());
        $this->assertSame($headers, $builder->getHeaders());
    }

    public function dataResults()
    {
        $labelA4 = new LabelFormat(LabelFormat::FORMAT_A4);
        $labelA6 = new LabelFormat(LabelFormat::FORMAT_A6);

        return array(
            array(
                'input' => array('123', $labelA4, false, false),
                'url' => '/boxes/123/labels/A4',
                'headers' => $this->getHeadersForImage(),
                'xml' => null,
                'method' => 'GET',
                'isExpectXml' => true,
            ),
            array(
                'input' => array('123', $labelA6, false, false),
                'url' => '/boxes/123/labels/A6',
                'headers' => $this->getHeadersForImage(),
                'xml' => null,
                'method' => 'GET',
                'isExpectXml' => true,
            ),
            array(
                'input' => array('123', $labelA4, true, false),
                'url' => '/boxes/123/labels/A4',
                'headers' => $this->getHeadersForPdf(),
                'xml' => null,
                'method' => 'GET',
                'isExpectXml' => true,
            ),
            array(
                'input' => array('123', $labelA6, true, false),
                'url' => '/boxes/123/labels/A6',
                'headers' => $this->getHeadersForPdf(),
                'xml' => null,
                'method' => 'GET',
                'isExpectXml' => true,
            ),
            array(
                'input' => array('123', $labelA4, false, true),
                'url' => '/boxes/123/labels/A4/withReturnLabels',
                'headers' => $this->getHeadersForImage(),
                'xml' => null,
                'method' => 'GET',
                'isExpectXml' => true,
            ),
            array(
                'input' => array('123', $labelA6, false, true),
                'url' => '/boxes/123/labels/A6/withReturnLabels',
                'headers' => $this->getHeadersForImage(),
                'xml' => null,
                'method' => 'GET',
                'isExpectXml' => true,
            ),
            array(
                'input' => array('123', $labelA4, true, true),
                'url' => '/boxes/123/labels/A4/withReturnLabels',
                'headers' => $this->getHeadersForPdf(),
                'xml' => null,
                'method' => 'GET',
                'isExpectXml' => true,
            ),
            array(
                'input' => array('123', $labelA6, true, true),
                'url' => '/boxes/123/labels/A6/withReturnLabels',
                'headers' => $this->getHeadersForPdf(),
                'xml' => null,
                'method' => 'GET',
                'isExpectXml' => true,
            ),
        );
    }

    private function getHeadersForPdf()
    {
        return array(
            'Accept: application/vnd.bpost.shm-label-pdf-v3.4+XML',
            'Content-Type: application/vnd.bpost.shm-labelRequest-v5+XML',
        );
    }

    private function getHeadersForImage()
    {
        return array(
            'Accept: application/vnd.bpost.shm-label-image-v3.4+XML',
            'Content-Type: application/vnd.bpost.shm-labelRequest-v5+XML',
        );
    }
}
