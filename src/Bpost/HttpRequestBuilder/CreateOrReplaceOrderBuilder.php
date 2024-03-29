<?php

namespace Bpost\BpostApiClient\Bpost\HttpRequestBuilder;

use Bpost\BpostApiClient\Bpost\Order;
use DOMDocument;

class CreateOrReplaceOrderBuilder implements HttpRequestBuilderInterface
{
    /**
     * @var Order
     */
    private $order;
    /**
     * @var string
     */
    private $accountId;

    /**
     * @param Order  $order
     * @param string $accountId
     */
    public function __construct(Order $order, $accountId)
    {
        $this->order = $order;
        $this->accountId = $accountId;
    }

    /**
     * @return string
     */
    public function getXml()
    {
        $document = new DOMDocument('1.0', 'utf-8');
        $document->preserveWhiteSpace = false;
        $document->formatOutput = true;

        $document->appendChild(
            $this->order->toXML(
                $document,
                $this->accountId
            )
        );

        return $document->saveXML();
    }

    /**
     * @return string[]
     */
    public function getHeaders()
    {
        return array(
            'Content-type: application/vnd.bpost.shm-order-v5+XML',
        );
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return '/orders';
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
