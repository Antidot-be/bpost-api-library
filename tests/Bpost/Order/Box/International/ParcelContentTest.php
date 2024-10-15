<?php

namespace Tests\Bpost\Order\Box\International;

use Bpost\BpostApiClient\Bpost;
use Bpost\BpostApiClient\Bpost\Order\Box\International\ParcelContent;
use DOMDocument;
use PHPUnit_Framework_TestCase;

class ParcelContentTest extends PHPUnit_Framework_TestCase
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

    public function testSetItemDescription()
    {
        $parcelContent = new ParcelContent();

        $parcelContent->setItemDescription('123456789_1234');
        $this->assertSame('123456789_1234', $parcelContent->getItemDescription());

        $parcelContent->setItemDescription('123456789_123456789_123456789_123');
        $this->assertSame('123456789_123456789_123456789_', $parcelContent->getItemDescription());
    }

    public function testToXML()
    {
        $parcelContent = new ParcelContent();
        $parcelContent
            ->setHsTariffCode(11)
            ->setItemDescription('t-shirt ARMANI L collection BG')
            ->setNettoWeight(400)
            ->setNumberOfItemType(2)
            ->setOriginOfGoods('US')
            ->setValueOfItem(200);

        $expectedDocument = self::createDomDocument();
        $parcelContentDom = $parcelContent->toXML($expectedDocument, 'international');
        $expectedDocument->appendChild($parcelContentDom);
        $parcelContentDom->setAttribute('xmlns:international', Bpost::NS_V5_INTERNATIONAL);
        $this->assertSame($this->getXml(), $expectedDocument->saveXML());
    }

    private function getXml()
    {
        return <<<XML
<?xml version="1.0" encoding="utf-8"?>
<international:parcelContent xmlns:international="http://schema.post.be/shm/deepintegration/v5/international">
  <international:numberOfItemType>2</international:numberOfItemType>
  <international:valueOfItem>200</international:valueOfItem>
  <international:itemDescription>t-shirt ARMANI L collection BG</international:itemDescription>
  <international:nettoWeight>400</international:nettoWeight>
  <international:hsTariffCode>11</international:hsTariffCode>
  <international:originOfGoods>US</international:originOfGoods>
</international:parcelContent>

XML;
    }

    public function testCreateFromXML()
    {
        $parcelContentSimpleXml = simplexml_load_string($this->getXml())->children('international', true);
        $parcelContent = ParcelContent::createFromXML($parcelContentSimpleXml);

        $this->assertSame(11, $parcelContent->getHsTariffCode());
        $this->assertSame('t-shirt ARMANI L collection BG', $parcelContent->getItemDescription());
        $this->assertSame(400, $parcelContent->getNettoWeight());
        $this->assertSame(2, $parcelContent->getNumberOfItemType());
        $this->assertSame('US', $parcelContent->getOriginOfGoods());
        $this->assertSame(200, $parcelContent->getValueOfItem());
    }
}
