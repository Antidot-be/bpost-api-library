<?php

namespace Bpost\BpostApiClient\Bpost\Order\Box;

use Bpost\BpostApiClient\Bpost;
use Bpost\BpostApiClient\Bpost\Order\Box\National\Unregistered;
use Bpost\BpostApiClient\Bpost\Order\Box\Option\Messaging;
use Bpost\BpostApiClient\Bpost\Order\ParcelsDepotAddress;
use Bpost\BpostApiClient\Bpost\ProductConfiguration\Product;
use Bpost\BpostApiClient\Common\XmlHelper;
use Bpost\BpostApiClient\Exception\BpostLogicException\BpostInvalidValueException;
use Bpost\BpostApiClient\Exception\BpostNotImplementedException;
use Bpost\BpostApiClient\Exception\XmlException\BpostXmlInvalidItemException;
use DOMDocument;
use DOMElement;
use SimpleXMLElement;

/**
 * bPost At247 class
 *
 * @author    Tijs Verkoyen <php-bpost@verkoyen.eu>
 *
 * @version   3.0.0
 *
 * @copyright Copyright (c), Tijs Verkoyen. All rights reserved.
 * @license   BSD License
 */
class At247 extends National
{
    /** @var string */
    private $parcelsDepotId;

    /** @var string */
    private $parcelsDepotName;

    /** @var \Bpost\BpostApiClient\Bpost\Order\ParcelsDepotAddress */
    private $parcelsDepotAddress;

    /** @var string */
    protected $product = Product::PRODUCT_NAME_BPACK_24H_PRO;

    /** @var string */
    private $memberId;

    /** @var Unregistered */
    private $unregistered;

    /** @var string */
    private $receiverName;

    /** @var string */
    private $receiverCompany;

    /** @var string */
    protected $requestedDeliveryDate;

    /**
     * @param string $memberId
     */
    public function setMemberId($memberId)
    {
        $this->memberId = $memberId;
    }

    /**
     * @return string
     */
    public function getMemberId()
    {
        return $this->memberId;
    }

    /**
     * @param \Bpost\BpostApiClient\Bpost\Order\ParcelsDepotAddress $parcelsDepotAddress
     */
    public function setParcelsDepotAddress($parcelsDepotAddress)
    {
        $this->parcelsDepotAddress = $parcelsDepotAddress;
    }

    /**
     * @return \Bpost\BpostApiClient\Bpost\Order\ParcelsDepotAddress
     */
    public function getParcelsDepotAddress()
    {
        return $this->parcelsDepotAddress;
    }

    /**
     * @param string $parcelsDepotId
     */
    public function setParcelsDepotId($parcelsDepotId)
    {
        $this->parcelsDepotId = $parcelsDepotId;
    }

    /**
     * @return string
     */
    public function getParcelsDepotId()
    {
        return $this->parcelsDepotId;
    }

    /**
     * @param string $parcelsDepotName
     */
    public function setParcelsDepotName($parcelsDepotName)
    {
        $this->parcelsDepotName = $parcelsDepotName;
    }

    /**
     * @return string
     */
    public function getParcelsDepotName()
    {
        return $this->parcelsDepotName;
    }

    /**
     * @return Unregistered
     */
    public function getUnregistered()
    {
        return $this->unregistered;
    }

    /**
     * @param Unregistered $unregistered
     */
    public function setUnregistered(Unregistered $unregistered)
    {
        $this->unregistered = $unregistered;
    }

    /**
     * @param string $product Possible values are: bpack 24h Pro
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
            Product::PRODUCT_NAME_BPACK_24H_PRO,
            Product::PRODUCT_NAME_BPACK_24_7,
        );
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
        $this->requestedDeliveryDate = (string) $requestedDeliveryDate;
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
        $boxElement = parent::toXML($document, null, 'at24-7');
        $nationalElement->appendChild($boxElement);

        if ($this->getParcelsDepotId() !== null) {
            $boxElement->appendChild(
                $document->createElement('parcelsDepotId', $this->getParcelsDepotId())
            );
        }
        if ($this->getParcelsDepotName() !== null) {
            $boxElement->appendChild(
                $document->createElement(
                    'parcelsDepotName',
                    $this->getParcelsDepotName()
                )
            );
        }
        if ($this->getParcelsDepotAddress() !== null) {
            $boxElement->appendChild(
                $this->getParcelsDepotAddress()->toXML($document)
            );
        }
        if ($this->getMemberId() !== null) {
            $boxElement->appendChild(
                $document->createElement(
                    'memberId',
                    $this->getMemberId()
                )
            );
        }
        $this->addToXmlUnregistered($document, $boxElement, $prefix);
        if ($this->getReceiverName() !== null) {
            $boxElement->appendChild(
                $document->createElement(
                    'receiverName',
                    $this->getReceiverName()
                )
            );
        }
        if ($this->getReceiverCompany() !== null) {
            $boxElement->appendChild(
                $document->createElement(
                    'receiverCompany',
                    $this->getReceiverCompany()
                )
            );
        }
        $this->addToXmlRequestedDeliveryDate($document, $boxElement, $prefix);

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
                $document->createElement(
                    XmlHelper::getPrefixedTagName('requestedDeliveryDate', $prefix),
                    $this->getRequestedDeliveryDate()
                )
            );
        }
    }

    /**
     * @param DOMDocument $document
     * @param DOMElement  $typeElement
     * @param string      $prefix
     */
    protected function addToXmlUnregistered(DOMDocument $document, DOMElement $typeElement, $prefix)
    {
        if ($this->getUnregistered() !== null) {
            $typeElement->appendChild(
                $this->getUnregistered()->toXml($document)
            );
        }
    }

    /**
     * @param SimpleXMLElement $xml
     * @param National|null    $self
     *
     * @return At247
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

        if (!isset($xml->{'at24-7'})) {
            throw new BpostXmlInvalidItemException();
        }

        $at247Xml = $xml->{'at24-7'}[0];

        /** @var static $self */
        $self = parent::createFromXML($at247Xml, $self);

        if (!empty($at247Xml->memberId)) {
            $self->setMemberId(
                (string) $at247Xml->memberId
            );
        }
        if (!empty($at247Xml->receiverName)) {
            $self->setReceiverName(
                (string) $at247Xml->receiverName
            );
        }
        if (!empty($at247Xml->receiverCompany)) {
            $self->setReceiverCompany(
                (string) $at247Xml->receiverCompany
            );
        }
        if (!empty($at247Xml->parcelsDepotId)) {
            $self->setParcelsDepotId(
                (string) $at247Xml->parcelsDepotId
            );
        }
        if (!empty($at247Xml->parcelsDepotName)) {
            $self->setParcelsDepotName(
                (string) $at247Xml->parcelsDepotName
            );
        }
        if (isset($at247Xml->parcelsDepotAddress)) {
            /** @var SimpleXMLElement $parcelsDepotAddressData */
            $parcelsDepotAddressData = $at247Xml->parcelsDepotAddress->children(Bpost::NS_V3_COMMON);
            $self->setParcelsDepotAddress(
                ParcelsDepotAddress::createFromXML($parcelsDepotAddressData)
            );
        }
        if (!empty($at247Xml->requestedDeliveryDate)) {
            $self->setRequestedDeliveryDate(
                (string) $at247Xml->requestedDeliveryDate
            );
        }

        return $self;
    }
}
