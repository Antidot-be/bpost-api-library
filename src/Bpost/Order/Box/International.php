<?php

namespace Bpost\BpostApiClient\Bpost\Order\Box;

use Bpost\BpostApiClient\Bpost;
use Bpost\BpostApiClient\Bpost\Order\Box\CustomsInfo\CustomsInfo;
use Bpost\BpostApiClient\Bpost\Order\Box\International\ParcelContent;
use Bpost\BpostApiClient\Bpost\Order\Box\Option\Messaging;
use Bpost\BpostApiClient\Bpost\Order\Box\Option\Option;
use Bpost\BpostApiClient\Bpost\Order\Receiver;
use Bpost\BpostApiClient\Bpost\ProductConfiguration\Product;
use Bpost\BpostApiClient\Common\XmlHelper;
use Bpost\BpostApiClient\Exception\BpostLogicException\BpostInvalidLengthException;
use Bpost\BpostApiClient\Exception\BpostLogicException\BpostInvalidValueException;
use Bpost\BpostApiClient\Exception\BpostNotImplementedException;
use DomDocument;
use DomElement;
use DOMException;
use SimpleXMLElement;

/**
 * bPost International class
 *
 * @author    Tijs Verkoyen <php-bpost@verkoyen.eu>
 *
 * @version   3.0.0
 *
 * @copyright Copyright (c), Tijs Verkoyen. All rights reserved.
 * @license   BSD License
 */
class International implements IBox
{
    /**
     * @var string
     */
    protected $product;

    /**
     * @var array
     */
    private $options = array();

    /**
     * @var Receiver
     */
    private $receiver;

    /**
     * @var int
     */
    private $parcelWeight;

    /**
     * @var CustomsInfo
     */
    private $customsInfo;

    /**
     * Only for shipments outside Europe.
     * Might include from 1 to 10 “parcelContent”.
     *
     * @var array|ParcelContent[]
     */
    private $parcelContents = array();

    /**
     * @param CustomsInfo $customsInfo
     */
    public function setCustomsInfo($customsInfo)
    {
        $this->customsInfo = $customsInfo;
    }

    /**
     * @return CustomsInfo
     */
    public function getCustomsInfo()
    {
        return $this->customsInfo;
    }

    /**
     * @param Option[] $options
     */
    public function setOptions($options)
    {
        $this->options = $options;
    }

    /**
     * @return Option[]
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param Option $option
     */
    public function addOption(Option $option)
    {
        $this->options[] = $option;
    }

    /**
     * @param int $parcelWeight
     */
    public function setParcelWeight($parcelWeight)
    {
        $this->parcelWeight = $parcelWeight;
    }

    /**
     * @return int
     */
    public function getParcelWeight()
    {
        return $this->parcelWeight;
    }

    /**
     * @param string $product
     *
     * @throws BpostInvalidValueException
     */
    public function setProduct($product)
    {
        if (!in_array($product, self::getPossibleProductValues())) {
            throw new BpostInvalidValueException('product', $product, self::getPossibleProductValues());
        }

        $this->product = $product;
    }

    /**
     * @return string
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * @return array
     */
    public static function getPossibleProductValues()
    {
        return array(
            Product::PRODUCT_NAME_BPACK_WORLD_BUSINESS,
            Product::PRODUCT_NAME_BPACK_WORLD_EASY_RETURN,
            Product::PRODUCT_NAME_BPACK_WORLD_EXPRESS_PRO,
            Product::PRODUCT_NAME_BPACK_EUROPE_BUSINESS,
            Product::PRODUCT_NAME_BPACK_AT_BPOST_INTERNATIONAL,
        );
    }

    /**
     * @param Receiver $receiver
     */
    public function setReceiver($receiver)
    {
        $this->receiver = $receiver;
    }

    /**
     * @return Receiver
     */
    public function getReceiver()
    {
        return $this->receiver;
    }

    /**
     * @return array|ParcelContent[]
     */
    public function getParcelContents()
    {
        return $this->parcelContents;
    }

    /**
     * @param array|ParcelContent[] $parcelContents
     *
     * @return self
     *
     * @throws BpostInvalidValueException
     */
    public function setParcelContents(array $parcelContents)
    {
        foreach ($parcelContents as $parcelContent) {
            if (!$parcelContent instanceof ParcelContent) {
                throw new BpostInvalidValueException(
                    'parcelContents',
                    get_class($parcelContent),
                    array('Bpost\BpostApiClient\Bpost\Order\Box\International\ParcelContent')
                );
            }

            $this->addParcelContent($parcelContent);
        }

        return $this;
    }

    public function addParcelContent(ParcelContent $parcelContent)
    {
        $this->parcelContents[] = $parcelContent;
    }

    /**
     * Return the object as an array for usage in the XML
     *
     * @param DomDocument $document
     * @param string      $prefix
     *
     * @return DOMElement
     *
     * @throws DOMException
     */
    public function toXML(DOMDocument $document, $prefix = null)
    {
        $internationalBox = $document->createElement(XmlHelper::getPrefixedTagName('internationalBox', $prefix));
        $prefix = 'international';
        $international = $document->createElement(XmlHelper::getPrefixedTagName('international', $prefix));
        $internationalBox->appendChild($international);

        if ($this->getProduct() !== null) {
            $international->appendChild(
                $document->createElement(XmlHelper::getPrefixedTagName('product', $prefix), $this->getProduct())
            );
        }

        $options = $this->getOptions();
        if (!empty($options)) {
            $optionsElement = $document->createElement(XmlHelper::getPrefixedTagName('options', $prefix));
            foreach ($options as $option) {
                $optionsElement->appendChild(
                    $option->toXML($document, 'common')
                );
            }
            $international->appendChild($optionsElement);
        }

        if ($this->getReceiver() !== null) {
            $international->appendChild(
                $this->getReceiver()->toXML($document, $prefix)
            );
        }

        if ($this->getParcelWeight() !== null) {
            $international->appendChild(
                $document->createElement(
                    XmlHelper::getPrefixedTagName('parcelWeight', $prefix),
                    $this->getParcelWeight()
                )
            );
        }

        if ($this->getCustomsInfo() !== null) {
            $international->appendChild(
                $this->getCustomsInfo()->toXML($document, $prefix)
            );
        }

        if ($this->getParcelContents()) {
            $parcelContents = $document->createElement(XmlHelper::getPrefixedTagName('parcelContents', $prefix));
            foreach ($this->getParcelContents() as $parcelContent) {
                $parcelContents->appendChild(
                    $parcelContent->toXML($document, $prefix)
                );
            }
            $international->appendChild($parcelContents);
        }

        return $internationalBox;
    }

    /**
     * @param SimpleXMLElement $xml
     *
     * @return International
     *
     * @throws BpostInvalidLengthException
     * @throws BpostInvalidValueException
     * @throws BpostNotImplementedException
     */
    public static function createFromXML(SimpleXMLElement $xml)
    {
        $international = new International();

        if (isset($xml->international->product) && $xml->international->product != '') {
            $international->setProduct(
                (string) $xml->international->product
            );
        }
        if (isset($xml->international->options)) {
            /** @var SimpleXMLElement $optionData */
            $options = $xml->international->options->children(Bpost::NS_V3_COMMON);
            foreach ($options as $optionData) {
                switch ($optionData->getName()) {
                    case Messaging::MESSAGING_TYPE_INFO_DISTRIBUTED:
                    case Messaging::MESSAGING_TYPE_KEEP_ME_INFORMED:
                    case Messaging::MESSAGING_TYPE_INFO_REMINDER:
                    case Messaging::MESSAGING_TYPE_INFO_NEXT_DAY:
                        $option = Messaging::createFromXML($optionData);
                        break;
                    default:
                        $className = '\Bpost\BpostApiClient\Bpost\Order\Box\Option\\' . ucfirst($optionData->getName());
                        XmlHelper::assertMethodCreateFromXmlExists($className);
                        $option = call_user_func(
                            array($className, 'createFromXML'),
                            $optionData
                        );
                }

                $international->addOption($option);
            }
        }
        if (isset($xml->international->parcelWeight) && $xml->international->parcelWeight != '') {
            $international->setParcelWeight(
                (int) $xml->international->parcelWeight
            );
        }
        if (isset($xml->international->receiver)) {
            $receiverData = $xml->international->receiver->children(Bpost::NS_V3_COMMON);
            $international->setReceiver(
                Receiver::createFromXML($receiverData)
            );
        }
        if (isset($xml->international->customsInfo)) {
            $international->setCustomsInfo(
                CustomsInfo::createFromXML($xml->international->customsInfo)
            );
        }
        if (isset($xml->international->parcelContents)) {
            /** @var SimpleXMLElement $optionData */
            $parcelContents = $xml->international->parcelContents->children('international', true);
            foreach ($parcelContents as $parcelContentXml) {
                $parcelContent = ParcelContent::createFromXML($parcelContentXml);
                $international->addParcelContent($parcelContent);
            }
        }

        return $international;
    }
}
