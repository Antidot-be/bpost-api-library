<?php

namespace Tests\Bpost\Order\Box\Option;

use Bpost\BpostApiClient\Bpost\Order\Box\Option\Signed;
use DOMDocument;
use PHPUnit_Framework_TestCase;

class SignatureTest extends PHPUnit_Framework_TestCase
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
     * Tests Signature->toXML
     */
    public function testToXML()
    {
        $expectedDocument = self::createDomDocument();
        $expectedDocument->appendChild(
            $expectedDocument->createElement('common:signed')
        );

        $actualDocument = self::createDomDocument();
        $signature = new Signed();
        $actualDocument->appendChild(
            $signature->toXML($actualDocument)
        );

        $this->assertSame($expectedDocument->saveXML(), $actualDocument->saveXML());

        $expectedDocument = self::createDomDocument();
        $expectedDocument->appendChild(
            $expectedDocument->createElement('foo:signed')
        );

        $actualDocument = self::createDomDocument();
        $signature = new Signed();
        $actualDocument->appendChild(
            $signature->toXML($actualDocument, 'foo')
        );

        $this->assertSame($expectedDocument->saveXML(), $actualDocument->saveXML());
    }
}
