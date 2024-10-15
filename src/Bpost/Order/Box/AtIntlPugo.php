<?php
namespace Bpost\BpostApiClient\Bpost\Order\Box;

use Bpost\BpostApiClient\Bpost;
use Bpost\BpostApiClient\Bpost\Order\Box\National\ShopHandlingInstruction;
use Bpost\BpostApiClient\Bpost\Order\Box\Option\Messaging;
use Bpost\BpostApiClient\Bpost\Order\PugoAddress;
use Bpost\BpostApiClient\Bpost\Order\Receiver;
use Bpost\BpostApiClient\Bpost\ProductConfiguration\Product;
use Bpost\BpostApiClient\Exception\BpostLogicException\BpostInvalidValueException;
use Bpost\BpostApiClient\Exception\BpostNotImplementedException;

class AtIntlPugo extends International
{
    /** @var string */
    protected $product = Product::PRODUCT_NAME_BPACK_AT_BPOST_INTERNATIONAL;

    /** @var string */
    private $pugoId;

    /** @var string */
    private $pugoName;

    /** @var PugoAddress */
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
            Product::PRODUCT_NAME_BPACK_AT_BPOST_INTERNATIONAL,
        );
    }

    /**
     * @param PugoAddress $pugoAddress
     */
    public function setPugoAddress($pugoAddress)
    {
        $this->pugoAddress = $pugoAddress;
    }

    /**
     * @return PugoAddress
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
     * @param  \DomDocument $document
     * @param  string       $prefix
     * @param  string       $type
     * @return \DomElement
     */
    public function toXML(\DOMDocument $document, $prefix = null, $type = null)
    {
        $internationalBox = $document->createElement($this->getPrefixedTagName('internationalBox', 'tns'));
        $boxElement = parent::toPugoXML($document, 'international', 'atIntlPugo');

        $internationalBox->appendChild($boxElement);

        if ($this->getPugoId() !== null) {
            $boxElement->appendChild(
                $document->createElement('international:pugoId', $this->getPugoId())
            );
        }
        if ($this->getPugoName() !== null) {
            $boxElement->appendChild(
                $document->createElement('international:pugoName', $this->getPugoName())
            );
        }

        if ($this->getPugoAddress() !== null) {
            $boxElement->appendChild(
                $this->getPugoAddress()->toXML($document, 'common', true)
            );
        }

        $this->addToXmlRequestedDeliveryDate($document, $boxElement, $prefix);
        $this->addToXmlShopHandlingInstruction($document, $boxElement, $prefix);
        return $internationalBox;
    }

    /**
     * @param \DOMDocument $document
     * @param \DOMElement  $typeElement
     * @param string       $prefix
     */
    protected function addToXmlRequestedDeliveryDate(\DOMDocument $document, \DOMElement $typeElement, $prefix)
    {
        if ($this->getRequestedDeliveryDate() !== null) {
            $typeElement->appendChild(
                $document->createElement('requestedDeliveryDate', $this->getRequestedDeliveryDate())
            );
        }
    }

    private function addToXmlShopHandlingInstruction(\DOMDocument $document, \DOMElement $typeElement, $prefix)
    {
        if ($this->getShopHandlingInstruction() !== null) {
            $typeElement->appendChild(
                $document->createElement('shopHandlingInstruction', $this->getShopHandlingInstruction())
            );
        }
    }

    /**
     * @param  \SimpleXMLElement $xml
     *
     * @return AtIntlPugo
     * @throws BpostInvalidValueException
     * @throws BpostNotImplementedException
     */
    public static function createFromXML(\SimpleXMLElement $xml)
    {
        $self = new AtIntlPugo();

        if (isset($xml->atIntlPugo->product) && $xml->atIntlPugo->product != '') {
            $self->setProduct(
                (string)$xml->atIntlPugo->product
            );
        }
        if (isset($xml->atIntlPugo->options)) {
            /** @var \SimpleXMLElement $optionData */
            foreach ($xml->atIntlPugo->options as $optionData) {
                $optionData = $optionData->children(Bpost::NS_V3_COMMON);

                if (in_array(
                    $optionData->getName(),
                    array(
                        Messaging::MESSAGING_TYPE_INFO_DISTRIBUTED,
                        Messaging::MESSAGING_TYPE_INFO_NEXT_DAY,
                        Messaging::MESSAGING_TYPE_INFO_REMINDER,
                        Messaging::MESSAGING_TYPE_KEEP_ME_INFORMED,
                    )
                )
                ) {
                    $option = Messaging::createFromXML($optionData);
                } else {
                    $className = '\\Bpost\\BpostApiClient\\Bpost\\Order\\Box\\Option\\'
                        . ucfirst($optionData->getName());
                    if (!method_exists($className, 'createFromXML')) {
                        throw new BpostNotImplementedException();
                    }
                    $option = call_user_func(
                        array($className, 'createFromXML'),
                        $optionData
                    );
                }

                $self->addOption($option);
            }
        }
        if (isset($xml->atIntlPugo->parcelWeight) && $xml->atIntlPugo->parcelWeight != '') {
            $self->setParcelWeight(
                (int)$xml->atIntlPugo->parcelWeight
            );
        }
        if (isset($xml->atIntlPugo->receiver) && $xml->atIntlPugo->receiver != '') {
            $self->setReceiver(
                Receiver::createFromXML(
                    $xml->atIntlPugo->receiver->children(Bpost::NS_V3_COMMON)
                )
            );
        }
        if (isset($xml->atIntlPugo->pugoId) && $xml->atIntlPugo->pugoId != '') {
            $self->setPugoId(
                (string)$xml->atIntlPugo->pugoId
            );
        }
        if (isset($xml->atIntlPugo->pugoName) && $xml->atIntlPugo->pugoName != '') {
            $self->setPugoName(
                (string)$xml->atIntlPugo->pugoName
            );
        }
        if (isset($xml->atIntlPugo->pugoAddress)) {
            /** @var \SimpleXMLElement $pugoAddressData */
            $pugoAddressData = $xml->atIntlPugo->pugoAddress->children(Bpost::NS_V3_COMMON);
            $self->setPugoAddress(
                PugoAddress::createFromXML($pugoAddressData)
            );
        }
        if (isset($xml->atIntlPugo->requestedDeliveryDate) && $xml->atIntlPugo->requestedDeliveryDate != '') {
            $self->setRequestedDeliveryDate(
                (string)$xml->atIntlPugo->requestedDeliveryDate
            );
        }
        if (isset($xml->atIntlPugo->shopHandlingInstruction) && $xml->atIntlPugo->shopHandlingInstruction != '') {
            $self->setShopHandlingInstruction(
                (string)$xml->atIntlPugo->shopHandlingInstruction
            );
        }

        return $self;
    }
}
