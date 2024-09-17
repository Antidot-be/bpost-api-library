<?php

namespace Bpost\BpostApiClient\Bpost\Order\Box\Option;

use Bpost\BpostApiClient\Common\XmlHelper;
use Bpost\BpostApiClient\Exception\BpostLogicException\BpostInvalidValueException;
use DomDocument;
use DomElement;
use DOMException;
use SimpleXMLElement;

/**
 * bPost Insurance class
 *
 * @author    Tijs Verkoyen <php-bpost@verkoyen.eu>
 *
 * @version   3.0.0
 *
 * @copyright Copyright (c), Tijs Verkoyen. All rights reserved.
 * @license   BSD License
 */
class Insured extends Option
{
    const INSURANCE_TYPE_BASIC_INSURANCE = 'basicInsurance';
    const INSURANCE_TYPE_ADDITIONAL_INSURANCE = 'additionalInsurance';

    const INSURANCE_AMOUNT_UP_TO_2500_EUROS = 2;
    const INSURANCE_AMOUNT_UP_TO_5000_EUROS = 3;

    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $value;

    /**
     * @return array
     */
    public static function getPossibleTypeValues()
    {
        return array(
            self::INSURANCE_TYPE_BASIC_INSURANCE,
            self::INSURANCE_TYPE_ADDITIONAL_INSURANCE,
        );
    }

    /**
     * @param string $type
     *
     * @throws BpostInvalidValueException
     */
    public function setType($type)
    {
        if (!in_array($type, self::getPossibleTypeValues())) {
            throw new BpostInvalidValueException('type', $type, self::getPossibleTypeValues());
        }

        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $value
     *
     * @throws BpostInvalidValueException
     */
    public function setValue($value)
    {
        if (!in_array($value, self::getPossibleValueValues())) {
            throw new BpostInvalidValueException('value', $value, self::getPossibleValueValues());
        }

        $this->value = $value;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return array
     */
    public static function getPossibleValueValues()
    {
        return array(
            self::INSURANCE_AMOUNT_UP_TO_2500_EUROS,
            self::INSURANCE_AMOUNT_UP_TO_5000_EUROS,
            self::INSURANCE_AMOUNT_UP_TO_7500_EUROS,
            self::INSURANCE_AMOUNT_UP_TO_10000_EUROS,
            self::INSURANCE_AMOUNT_UP_TO_12500_EUROS,
            self::INSURANCE_AMOUNT_UP_TO_15000_EUROS,
            self::INSURANCE_AMOUNT_UP_TO_17500_EUROS,
            self::INSURANCE_AMOUNT_UP_TO_20000_EUROS,
            self::INSURANCE_AMOUNT_UP_TO_22500_EUROS,
            self::INSURANCE_AMOUNT_UP_TO_25000_EUROS,
        );
    }

    /**
     * @param string      $type
     * @param string|null $value
     *
     * @throws BpostInvalidValueException
     */
    public function __construct($type, $value = null)
    {
        $this->setType($type);
        if ($value !== null) {
            $this->setValue($value);
        }
    }

    /**
     * Return the object as an array for usage in the XML
     *
     * @param DomDocument $document
     * @param string      $prefix
     *
     * @return DomElement
     *
     * @throws DOMException
     */
    public function toXML(DOMDocument $document, $prefix = 'common')
    {
        $insured = $document->createElement(XmlHelper::getPrefixedTagName('insured', $prefix));

        $insurance = $document->createElement(XmlHelper::getPrefixedTagName($this->getType(), $prefix));
        $insured->appendChild($insurance);

        if ($this->getValue() !== null) {
            $insurance->setAttribute('value', $this->getValue());
        }

        return $insured;
    }

    /**
     * @param SimpleXMLElement $xml
     *
     * @return static
     *
     * @throws BpostInvalidValueException
     */
    public static function createFromXML(SimpleXMLElement $xml)
    {
        $insuranceDetail = $xml->children('common', true);

        $type = $insuranceDetail->getName();
        $value = $insuranceDetail->attributes()->value !== null ? (int) $insuranceDetail->attributes()->value : null;

        if ($type === static::INSURANCE_TYPE_ADDITIONAL_INSURANCE && $value === 1) {
            $type = static::INSURANCE_TYPE_BASIC_INSURANCE;
            $value = null;
        }

        return new static($type, $value);
    }
}
