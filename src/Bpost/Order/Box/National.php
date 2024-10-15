<?php

namespace Bpost\BpostApiClient\Bpost\Order\Box;

use Bpost\BpostApiClient\Bpost;
use Bpost\BpostApiClient\Bpost\Order\Box\OpeningHour\Day;
use Bpost\BpostApiClient\Bpost\Order\Box\Option\CashOnDelivery;
use Bpost\BpostApiClient\Bpost\Order\Box\Option\Messaging;
use Bpost\BpostApiClient\Bpost\Order\Box\Option\Option;
use Bpost\BpostApiClient\BpostException;
use Bpost\BpostApiClient\Common\ComplexAttribute;
use Bpost\BpostApiClient\Common\XmlHelper;
use Bpost\BpostApiClient\Exception\BpostLogicException\BpostInvalidLengthException;
use Bpost\BpostApiClient\Exception\BpostLogicException\BpostInvalidValueException;
use Bpost\BpostApiClient\Exception\BpostNotImplementedException;
use Bpost\BpostApiClient\Exception\XmlException\BpostXmlInvalidItemException;
use DomDocument;
use DomElement;
use SimpleXMLElement;

/**
 * bPost National class
 *
 * @author    Tijs Verkoyen <php-bpost@verkoyen.eu>
 *
 * @version   3.0.0
 *
 * @copyright Copyright (c), Tijs Verkoyen. All rights reserved.
 * @license   BSD License
 */
abstract class National extends ComplexAttribute implements IBox
{
    /** @var string */
    protected $product;

    /** @var Option[] */
    protected $options;

    /** @var int */
    protected $weight;

    /** @var Day[] */
    private $openingHours;

    /** @var string */
    private $desiredDeliveryPlace;

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
     * @param string $product
     */
    public function setProduct($product)
    {
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
     * @remark should be implemented by the child class
     *
     * @return array
     */
    public static function getPossibleProductValues()
    {
        return array();
    }

    /**
     * @param int $weight
     */
    public function setWeight($weight)
    {
        $this->weight = $weight;
    }

    /**
     * @return int
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * @param Day[] $openingHours
     */
    public function setOpeningHours(array $openingHours)
    {
        $this->openingHours = $openingHours;
    }

    /**
     * @param Day $day
     */
    public function addOpeningHour(Day $day)
    {
        $this->openingHours[] = $day;
    }

    /**
     * @return Day[]
     */
    public function getOpeningHours()
    {
        return $this->openingHours;
    }

    /**
     * @param string $desiredDeliveryPlace
     */
    public function setDesiredDeliveryPlace($desiredDeliveryPlace)
    {
        $this->desiredDeliveryPlace = $desiredDeliveryPlace;
    }

    /**
     * @return string
     */
    public function getDesiredDeliveryPlace()
    {
        return $this->desiredDeliveryPlace;
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
        $typeElement = $document->createElement($type);

        if ($this->getProduct() !== null) {
            $typeElement->appendChild(
                $document->createElement(
                    XmlHelper::getPrefixedTagName('product', $prefix),
                    $this->getProduct()
                )
            );
        }

        $options = $this->getOptions();
        if (!empty($options)) {
            $optionsElement = $document->createElement('options');
            foreach ($options as $option) {
                $optionsElement->appendChild(
                    $option->toXML($document)
                );
            }
            $typeElement->appendChild($optionsElement);
        }

        if ($this->getWeight() !== null) {
            $typeElement->appendChild(
                $document->createElement(XmlHelper::getPrefixedTagName('weight', $prefix), $this->getWeight())
            );
        }

        $openingHours = $this->getOpeningHours();
        if (!empty($openingHours)) {
            $openingHoursElement = $document->createElement('openingHours');
            /** @var Day $day */
            foreach ($openingHours as $day) {
                $openingHoursElement->appendChild(
                    $day->toXML($document)
                );
            }
            $typeElement->appendChild($openingHoursElement);
        }

        if ($this->getDesiredDeliveryPlace() !== null) {
            $typeElement->appendChild(
                $document->createElement(
                    XmlHelper::getPrefixedTagName('desiredDeliveryPlace', $prefix),
                    $this->getDesiredDeliveryPlace()
                )
            );
        }

        return $typeElement;
    }

    /**
     * @param SimpleXMLElement $nationalXml
     * @param National         $self
     *
     * @return National
     *
     * @throws BpostException
     * @throws BpostXmlInvalidItemException
     */
    public static function createFromXML(SimpleXMLElement $nationalXml, National $self = null)
    {
        if ($self === null) {
            throw new BpostException('Set an instance of National');
        }

        if (isset($nationalXml->product) && $nationalXml->product != '') {
            $self->setProduct(
                (string) $nationalXml->product
            );
        }

        if (!empty($nationalXml->options)) {
            /** @var SimpleXMLElement $optionData */
            foreach ($nationalXml->options->children(Bpost::NS_V3_COMMON) as $optionData) {
                $self->addOption(self::getOptionFromOptionData($optionData));
            }
        }

        if (isset($nationalXml->weight) && $nationalXml->weight != '') {
            $self->setWeight(
                (int) $nationalXml->weight
            );
        }

        if (isset($nationalXml->openingHours) && $nationalXml->openingHours != '') {
            foreach ($nationalXml->openingHours->children() as $day => $value) {
                $self->addOpeningHour(new Day($day, (string) $value));
            }
        }

        if (isset($nationalXml->desiredDeliveryPlace) && $nationalXml->desiredDeliveryPlace != '') {
            $self->setDesiredDeliveryPlace(
                (string) $nationalXml->desiredDeliveryPlace
            );
        }

        return $self;
    }

    /**
     * @param SimpleXMLElement $optionData
     *
     * @return Option
     *
     * @throws BpostNotImplementedException
     * @throws BpostInvalidLengthException
     * @throws BpostInvalidValueException
     */
    protected static function getOptionFromOptionData(SimpleXMLElement $optionData)
    {
        switch ($optionData->getName()) {
            case Messaging::MESSAGING_TYPE_INFO_DISTRIBUTED:
            case Messaging::MESSAGING_TYPE_INFO_NEXT_DAY:
            case Messaging::MESSAGING_TYPE_INFO_REMINDER:
            case Messaging::MESSAGING_TYPE_KEEP_ME_INFORMED:
                return Messaging::createFromXML($optionData);

            case 'cod':
                return CashOnDelivery::createFromXML($optionData);

            default:
                $className = '\\Bpost\\BpostApiClient\\Bpost\\Order\\Box\\Option\\' . ucfirst($optionData->getName());

                XmlHelper::assertMethodCreateFromXmlExists($className);

                return call_user_func(
                    array($className, 'createFromXML'),
                    $optionData
                );
        }
    }
}
