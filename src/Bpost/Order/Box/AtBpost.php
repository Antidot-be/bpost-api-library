<?php

namespace Bpost\BpostApiClient\Bpost\Order\Box;

use Bpost\BpostApiClient\Bpost;
use Bpost\BpostApiClient\Bpost\Order\Box\National\ShopHandlingInstruction;
use Bpost\BpostApiClient\Bpost\Order\Box\Option\Messaging;
use Bpost\BpostApiClient\Bpost\Order\PugoAddress;
use Bpost\BpostApiClient\Bpost\ProductConfiguration\Product;
use Bpost\BpostApiClient\Common\XmlHelper;
use Bpost\BpostApiClient\Exception\BpostLogicException\BpostInvalidValueException;
use Bpost\BpostApiClient\Exception\BpostNotImplementedException;
use Bpost\BpostApiClient\Exception\XmlException\BpostXmlInvalidItemException;
use DOMDocument;
use DOMElement;
use SimpleXMLElement;

/**
 * bPost AtBpost class
 *
 * @author    Tijs Verkoyen <php-bpost@verkoyen.eu>
 *
 * @version   3.0.0
 *
 * @copyright Copyright (c), Tijs Verkoyen. All rights reserved.
 * @license   BSD License
 */
class AtBpost extends National
{
    /** @var string */
    protected $product = Product::PRODUCT_NAME_BPACK_AT_BPOST;

    /** @var string */
    private $pugoId;

    /** @var string */
    private $pugoName;

    /** @var \Bpost\BpostApiClient\Bpost\Order\PugoAddress */
    private $pugoAddress;

    /** @var string */
    private $receiverName;

    /** @var string */
    private $receiverCompany;

    /** @var string */
    protected $requestedDeliveryDate;

    /** @var ShopHandlingInstruction */
    private $shopHandlingInstruction;

    /**
     * @param string $product Possible values are: bpack@bpost
     *
     * @throws BpostInvalidValueException
     */
    public function setProduct($product)
    {
        if (!in_array($product, self::getPossibleProductValues())) {
            throw new BpostInvalidValueException('product', $product, self::getPossibleProductValues());
        }

        parent::setProduct($product);
    }

    /**
     * @return array
     */
    public static function getPossibleProductValues()
    {
        return array(
            Product::PRODUCT_NAME_BPACK_AT_BPOST,
        );
    }

    /**
     * @param \Bpost\BpostApiClient\Bpost\Order\PugoAddress $pugoAddress
     */
    public function setPugoAddress($pugoAddress)
    {
        $this->pugoAddress = $pugoAddress;
    }

    /**
     * @return \Bpost\BpostApiClient\Bpost\Order\PugoAddress
     */
    public function getPugoAddress()
    {
        return $this->pugoAddress;
    }

    /**
     * @param string $pugoId
     */
    public function setPugoId($pugoId)
    {
        $this->pugoId = $pugoId;
    }

    /**
     * @return string
     */
    public function getPugoId()
    {
        return $this->pugoId;
    }

    /**
     * @param string $pugoName
     */
    public function setPugoName($pugoName)
    {
        $this->pugoName = $pugoName;
    }

    /**
     * @return string
     */
    public function getPugoName()
    {
        return $this->pugoName;
    }

    /**
     * @param string $receiverCompany
     */
    public function setReceiverCompany($receiverCompany)
    {
        $this->receiverCompany = $receiverCompany;
    }

    /**
     * @return string
     */
    public function getReceiverCompany()
    {
        return $this->receiverCompany;
    }

    /**
     * @param string $receiverName
     */
    public function setReceiverName($receiverName)
    {
        $this->receiverName = $receiverName;
    }

    /**
     * @return string
     */
    public function getReceiverName()
    {
        return $this->receiverName;
    }

    /**
     * @return string
     */
    public function getRequestedDeliveryDate()
    {
        return $this->requestedDeliveryDate;
    }

    /**
     * @param string $requestedDeliveryDate
     */
    public function setRequestedDeliveryDate($requestedDeliveryDate)
    {
        $this->requestedDeliveryDate = $requestedDeliveryDate;
    }

    /**
     * @return string
     */
    public function getShopHandlingInstruction()
    {
        if ($this->shopHandlingInstruction !== null) {
            return $this->shopHandlingInstruction->getValue();
        }

        return null;
    }

    /**
     * @param string $shopHandlingInstruction
     */
    public function setShopHandlingInstruction($shopHandlingInstruction)
    {
        $this->shopHandlingInstruction = new ShopHandlingInstruction($shopHandlingInstruction);
    }

    /**
     * Return the object as an array for usage in the XML
     *
     * @param DomDocument $document
     * @param string      $prefix
     * @param string      $type
     *
     * @return DomElement
     */
    public function toXML(DOMDocument $document, $prefix = null, $type = null)
    {
        $nationalElement = $document->createElement(XmlHelper::getPrefixedTagName('nationalBox', $prefix));
        $boxElement = parent::toXML($document, null, 'atBpost');
        $nationalElement->appendChild($boxElement);

        if ($this->getPugoId() !== null) {
            $boxElement->appendChild(
                $document->createElement('pugoId', $this->getPugoId())
            );
        }
        if ($this->getPugoName() !== null) {
            $boxElement->appendChild(
                $document->createElement('pugoName', $this->getPugoName())
            );
        }
        if ($this->getPugoAddress() !== null) {
            $boxElement->appendChild(
                $this->getPugoAddress()->toXML($document, 'common')
            );
        }
        if ($this->getReceiverName() !== null) {
            $boxElement->appendChild(
                $document->createElement('receiverName', $this->getReceiverName())
            );
        }
        if ($this->getReceiverCompany() !== null) {
            $boxElement->appendChild(
                $document->createElement('receiverCompany', $this->getReceiverCompany())
            );
        }
        $this->addToXmlRequestedDeliveryDate($document, $boxElement, $prefix);
        $this->addToXmlShopHandlingInstruction($document, $boxElement, $prefix);

        return $nationalElement;
    }

    /**
     * @param DOMDocument $document
     * @param DOMElement  $typeElement
     * @param string      $prefix
     */
    protected function addToXmlRequestedDeliveryDate(DOMDocument $document, DOMElement $typeElement, $prefix)
    {
        if ($this->getRequestedDeliveryDate() !== null) {
            $typeElement->appendChild(
                $document->createElement('requestedDeliveryDate', $this->getRequestedDeliveryDate())
            );
        }
    }

    private function addToXmlShopHandlingInstruction(DOMDocument $document, DOMElement $typeElement, $prefix)
    {
        if ($this->getShopHandlingInstruction() !== null) {
            $typeElement->appendChild(
                $document->createElement('shopHandlingInstruction', $this->getShopHandlingInstruction())
            );
        }
    }

    /**
     * @param SimpleXMLElement $xml
     * @param National|null    $self
     *
     * @return AtBpost
     *
     * @throws BpostInvalidValueException
     * @throws BpostNotImplementedException
     * @throws \Bpost\BpostApiClient\Exception\BpostLogicException\BpostInvalidLengthException
     * @throws \Bpost\BpostApiClient\Exception\XmlException\BpostXmlInvalidItemException
     */
    public static function createFromXML(SimpleXMLElement $xml, National $self = null)
    {
        if ($self === null) {
            $self = new self();
        }

        if (!isset($xml->atBpost)) {
            throw new BpostXmlInvalidItemException();
        }

        $atBpostXml = $xml->atBpost[0];

        /** @var static $self */
        $self = parent::createFromXML($atBpostXml, $self);

        if (!empty($atBpostXml->receiverName)) {
            $self->setReceiverName(
                (string) $xml->atBpost->receiverName
            );
        }
        if (!empty($atBpostXml->receiverCompany)) {
            $self->setReceiverCompany(
                (string) $xml->atBpost->receiverCompany
            );
        }
        if (!empty($atBpostXml->pugoId)) {
            $self->setPugoId(
                (string) $xml->atBpost->pugoId
            );
        }
        if (!empty($atBpostXml->pugoName)) {
            $self->setPugoName(
                (string) $xml->atBpost->pugoName
            );
        }
        if (isset($xml->atBpost->pugoAddress)) {
            /** @var SimpleXMLElement $pugoAddressData */
            $pugoAddressData = $atBpostXml->pugoAddress->children(Bpost::NS_V3_COMMON);
            $self->setPugoAddress(
                PugoAddress::createFromXML($pugoAddressData)
            );
        }
        if (!empty($atBpostXml->requestedDeliveryDate)) {
            $self->setRequestedDeliveryDate(
                (string) $xml->atBpost->requestedDeliveryDate
            );
        }
        if (!empty($atBpostXml->shopHandlingInstruction)) {
            $self->setShopHandlingInstruction(
                (string) $xml->atBpost->shopHandlingInstruction
            );
        }

        return $self;
    }
}
