<?php

namespace Tests\Bpost\Order\Box\Option;

use Bpost\BpostApiClient\Bpost\Order\Box\Option\AutomaticSecondPresentation;
use DOMDocument;
use PHPUnit_Framework_TestCase;

class AutomaticSecondPresentationTest extends PHPUnit_Framework_TestCase
{
    /**
     * Create a generic DOM Document
     *
     * @return DOMDocument
     */
    private static function createDomDocument()
    {
        $document = new DOMDocument('1.0', 'utf-8');
        $document->preserveWhiteSpace = false;
        $document->formatOutput = true;

        return $document;
    }

    /**
     * Tests AutomaticSecondPresentation->toXML
     */
    public function testToXML()
    {
        $expectedDocument = self::createDomDocument();
        $expectedDocument->appendChild(
            $expectedDocument->createElement('common:automaticSecondPresentation')
        );

        $actualDocument = self::createDomDocument();
        $automaticSecondPresentation = new AutomaticSecondPresentation();
        $actualDocument->appendChild(
            $automaticSecondPresentation->toXML($actualDocument)
        );

        $this->assertSame($expectedDocument->saveXML(), $actualDocument->saveXML());

        $expectedDocument = self::createDomDocument();
        $expectedDocument->appendChild(
            $expectedDocument->createElement('foo:automaticSecondPresentation')
        );

        $actualDocument = self::createDomDocument();
        $automaticSecondPresentation = new AutomaticSecondPresentation();
        $actualDocument->appendChild(
            $automaticSecondPresentation->toXML($actualDocument, 'foo')
        );

        $this->assertSame($expectedDocument->saveXML(), $actualDocument->saveXML());
    }
}
