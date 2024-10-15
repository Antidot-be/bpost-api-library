<?php

namespace Bpost\BpostApiClient\Bpost\Order\Box\Option;

use DOMDocument;
use DOMElement;
use SimpleXMLElement;

/**
 * bPost Option class
 *
 * @author    Tijs Verkoyen <php-bpost@verkoyen.eu>
 *
 * @version   3.0.0
 *
 * @copyright Copyright (c), Tijs Verkoyen. All rights reserved.
 * @license   BSD License
 */
abstract class Option
{
    /**
     * @param DOMDocument $document
     * @param string      $prefix
     *
     * @return DOMElement
     */
    abstract public function toXML(DOMDocument $document, $prefix = null);

    public static function createFromXML(SimpleXMLElement $xml)
    {
        return new static();
    }
}
