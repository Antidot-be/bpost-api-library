<?php

namespace Bpost\BpostApiClient\Bpost\HttpRequestBuilder;

use Bpost\BpostApiClient\Bpost\Order\Box;
use Bpost\BpostApiClient\Exception\BpostLogicException\BpostInvalidValueException;
use DOMDocument;

class ModifyOrderBuilder implements HttpRequestBuilderInterface
{
    /**
     * @var string
     */
    private $reference;
    /**
     * @var string
     */
    private $status;

    /**
     * @param string $reference
     * @param string $status
     */
    public function __construct($reference, $status)
    {
        $this->reference = (string) $reference;
        $this->status = strtoupper($status);
        if (!in_array($this->status, Box::getPossibleStatusValues())) {
            throw new BpostInvalidValueException('status', $this->status, Box::getPossibleStatusValues());
        }
    }

    /**
     * @return string|null
     */
    public function getXml()
    {
        $document = new DOMDocument('1.0', 'utf-8');
        $document->preserveWhiteSpace = false;
        $document->formatOutput = true;

        $orderUpdate = $document->createElement('orderUpdate');
        $orderUpdate->setAttribute('xmlns', 'http://schema.post.be/shm/deepintegration/v5/');
        $orderUpdate->setAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
        $orderUpdate->appendChild(
            $document->createElement('status', $this->status)
        );
        $document->appendChild($orderUpdate);

        return $document->saveXML();
    }

    /**
     * @return string[]
     */
    public function getHeaders()
    {
        return array(
            'Content-type: application/vnd.bpost.shm-orderUpdate-v3+XML',
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
        return false;
    }

    public function getMethod()
    {
        return self::METHOD_POST;
    }
}
