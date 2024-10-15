<?php

namespace Bpost\BpostApiClient\Bpost\Order\Box\Option;

use Bpost\BpostApiClient\Common\XmlHelper;
use DomDocument;
use DomElement;
use SimpleXMLElement;

/**
 * bPost AutomaticSecondPresentation class
 *
 * @author    Tijs Verkoyen <php-bpost@verkoyen.eu>
 *
 * @version   3.0.0
 *
 * @copyright Copyright (c), Tijs Verkoyen. All rights reserved.
 * @license   BSD License
 */
class AutomaticSecondPresentation extends Option
{
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
        return $document->createElement(XmlHelper::getPrefixedTagName('automaticSecondPresentation', $prefix));
    }
}
