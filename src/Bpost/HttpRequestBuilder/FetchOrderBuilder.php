<?php

namespace Bpost\BpostApiClient\Bpost\HttpRequestBuilder;

class FetchOrderBuilder implements HttpRequestBuilderInterface
{
    /**
     * @var string
     */
    private $reference;

    /**
     * @param string $reference
     */
    public function __construct($reference)
    {
        $this->reference = (string) $reference;
    }

    /**
     * @return string|null
     */
    public function getXml()
    {
        return null;
    }

    /**
     * @return string[]
     */
    public function getHeaders()
    {
        return array(
            'Accept: application/vnd.bpost.shm-order-v3.5+XML',
        );
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return '/orders/' . $this->reference;
    }

    public function isExpectXml()
    {
        return true;
    }

    public function getMethod()
    {
        return self::METHOD_GET;
    }
}
