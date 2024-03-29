<?php

namespace Bpost\BpostApiClient\Bpost\HttpRequestBuilder;

class FetchProductConfigBuilder implements HttpRequestBuilderInterface
{
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
            'Accept: application/vnd.bpost.shm-productConfiguration-v3.1+XML',
        );
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return '/productconfig';
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
