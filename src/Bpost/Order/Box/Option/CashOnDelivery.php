<?php

namespace Bpost\BpostApiClient\Bpost\Order\Box\Option;

use Bpost\BpostApiClient\Bpost;
use Bpost\BpostApiClient\Common\XmlHelper;
use Bpost\BpostApiClient\Exception\BpostLogicException\BpostInvalidLengthException;
use Bpost\BpostApiClient\Exception\BpostLogicException\BpostInvalidValueException;
use DomDocument;
use DomElement;
use SimpleXMLElement;

/**
 * bPost CashOnDelivery class
 *
 * @author    Tijs Verkoyen <php-bpost@verkoyen.eu>
 *
 * @version   3.0.0
 *
 * @copyright Copyright (c), Tijs Verkoyen. All rights reserved.
 * @license   BSD License
 */
class CashOnDelivery extends Option
{
    /**
     * @var float
     */
    private $amount;

    /**
     * @var string
     */
    private $iban;

    /**
     * @var string
     */
    private $bic;

    /**
     * @param float $amount
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
    }

    /**
     * @return float
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param string $bic
     */
    public function setBic($bic)
    {
        $this->bic = $bic;
    }

    /**
     * @return string
     */
    public function getBic()
    {
        return $this->bic;
    }

    /**
     * @param string $iban
     */
    public function setIban($iban)
    {
        $this->iban = $iban;
    }

    /**
     * @return string
     */
    public function getIban()
    {
        return $this->iban;
    }

    /**
     * @param float  $amount
     * @param string $iban
     * @param string $bic
     */
    public function __construct($amount, $iban, $bic)
    {
        $this->setAmount($amount);
        $this->setIban($iban);
        $this->setBic($bic);
    }

    /**
     * Return the object as an array for usage in the XML
     *
     * @param DomDocument $document
     * @param string      $prefix
     *
     * @return DomElement
     */
    public function toXML(DOMDocument $document, $prefix = 'common')
    {
        $cod = $document->createElement(XmlHelper::getPrefixedTagName('cod', $prefix));

        if ($this->getAmount() !== null) {
            $cod->appendChild(
                $document->createElement(
                    XmlHelper::getPrefixedTagName('codAmount', $prefix),
                    $this->getAmount()
                )
            );
        }
        if ($this->getIban() !== null) {
            $cod->appendChild(
                $document->createElement(
                    XmlHelper::getPrefixedTagName('iban', $prefix),
                    $this->getIban()
                )
            );
        }
        if ($this->getBic() !== null) {
            $cod->appendChild(
                $document->createElement(
                    XmlHelper::getPrefixedTagName('bic', $prefix),
                    $this->getBic()
                )
            );
        }

        return $cod;
    }

    /**
     * @param SimpleXMLElement $xml
     *
     * @return static
     *
     * @throws BpostInvalidLengthException
     * @throws BpostInvalidValueException
     */
    public static function createFromXML(SimpleXMLElement $xml)
    {
        $details = $xml->children(Bpost::NS_V3_COMMON);

        return new static(floatval($details->codAmount), (string) $details->iban, (string) $details->bic);
    }
}
