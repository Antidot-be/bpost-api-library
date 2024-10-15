<?php

namespace Tests\BpostApiExamples\CreateOrder\International;

use Bpost\BpostApiClient\Bpost;
use Bpost\BpostApiClient\Bpost\Order;
use Bpost\BpostApiClient\Bpost\Order\Address;
use Bpost\BpostApiClient\Bpost\Order\Box;
use Bpost\BpostApiClient\Bpost\Order\Box\CustomsInfo\CustomsInfo;
use Bpost\BpostApiClient\Bpost\Order\Box\International;
use Bpost\BpostApiClient\Bpost\Order\Box\International\ParcelContent;
use Bpost\BpostApiClient\Bpost\Order\Box\Option\Insured;
use Bpost\BpostApiClient\Bpost\Order\Line;
use Bpost\BpostApiClient\Bpost\Order\Receiver;
use Bpost\BpostApiClient\Bpost\Order\Sender;
use DOMDocument;
use DOMElement;
use PHPUnit_Framework_TestCase;

class BpackWorldExpressProBasicInsuranceTest extends PHPUnit_Framework_TestCase
{
    public function testToXml()
    {
        $order = $this->getOrder();

        $rootDom = $this->createDomDocument();
        $document = $this->generateDomDocument($rootDom, $order->toXML($rootDom, '{accountID}'));

        $expectedDom = $this->createDomDocument();
        $expectedDom->loadXML($this->getXml());
        $this->assertSame($expectedDom->saveXML(), $document->saveXML());
    }

    private function getOrder()
    {
        $order = new Order('bpack world Express Pro - Ins');
        $order->setCostCenter('Cost Center');

        $order->addLine(new Line('Product 1', 1));
        $order->addLine(new Line('Product 1', 5));

        $order->addBox($box = new Box());

        $box->setSender($sender = new Sender());
        $box->setInternationalBox($internationalBox = new International());
        $box->setRemark('bpack world Express Pro - Ins');
        $box->setAdditionalCustomerReference('Reference that can be used for cross-referencing');

        $sender->setName('SENDER NAME');
        $sender->setCompany('SENDER COMPANY');
        $sender->setAddress(new Address('MUNT', 1, 1, '1000', 'Brussel', 'BE'));
        $sender->setEmailAddress('sender@mail.be');
        $sender->setPhoneNumber('022011111');

        $internationalBox->setProduct('bpack World Express Pro');

        $internationalBox->addOption(new Insured(Insured::INSURANCE_TYPE_BASIC_INSURANCE));

        $internationalBox->setReceiver($receiver = new Receiver());
        $internationalBox->setParcelWeight(900);
        $internationalBox->setCustomsInfo($customsInfo = new CustomsInfo());

        $receiver->setName('RECEIVER NAME');
        $receiver->setCompany('RECEIVER COMPANY');
        $receiver->setAddress(new Address('Pennsylvania Plaza', 4, 'A', '10001', 'New York', 'US'));
        $receiver->setEmailAddress('sender@mail.com');
        $receiver->setPhoneNumber('+1212465741');

        $customsInfo->setParcelValue(1100);
        $customsInfo->setContentDescription('TSHIRTS');
        $customsInfo->setShipmentType('GIFT');
        $customsInfo->setParcelReturnInstructions('RTS');
        $customsInfo->setPrivateAddress(false);
        $customsInfo->setCurrency(CustomsInfo::CUSTOM_INFO_CURRENCY_EUR);
        $customsInfo->setAmtPostagePaidByAddresse(12.50);

        $parcelContent = new ParcelContent();
        $parcelContent
            ->setNumberOfItemType(2)
            ->setValueOfItem(200)
            ->setItemDescription('t-shirt ARMANI L collection BG')
            ->setNettoWeight(400)
            ->setHsTariffCode(11)
            ->setOriginOfGoods('US');
        $internationalBox->addParcelContent($parcelContent);

        $parcelContent = new ParcelContent();
        $parcelContent
            ->setNumberOfItemType(4)
            ->setValueOfItem(900)
            ->setItemDescription('t-shirt ARMANI L collection BG')
            ->setNettoWeight(500)
            ->setHsTariffCode(12)
            ->setOriginOfGoods('BE');
        $internationalBox->addParcelContent($parcelContent);

        return $order;
    }

    private function getXml()
    {
        return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<tns:order 
    xmlns="http://schema.post.be/shm/deepintegration/v5/national" 
    xmlns:common="http://schema.post.be/shm/deepintegration/v5/common" 
    xmlns:tns="http://schema.post.be/shm/deepintegration/v5/" 
    xmlns:international="http://schema.post.be/shm/deepintegration/v5/international" 
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" 
    xsi:schemaLocation="http://schema.post.be/shm/deepintegration/v5/"
    >
    <tns:accountId>{accountID}</tns:accountId>
    <tns:reference>bpack world Express Pro - Ins</tns:reference>
    <tns:costCenter>Cost Center</tns:costCenter>
    <tns:orderLine>
        <tns:text>Product 1</tns:text>
        <tns:nbOfItems>1</tns:nbOfItems>
    </tns:orderLine>
    <tns:orderLine>
        <tns:text>Product 1</tns:text>
        <tns:nbOfItems>5</tns:nbOfItems>
    </tns:orderLine>
    <tns:box>
        <tns:sender>
            <common:name>SENDER NAME</common:name>
            <common:company>SENDER COMPANY</common:company>
            <common:address>
                <common:streetName>MUNT</common:streetName>
                <common:number>1</common:number>
                <common:box>1</common:box>
                <common:postalCode>1000</common:postalCode>
                <common:locality>Brussel</common:locality>
                <common:countryCode>BE</common:countryCode>
            </common:address>
            <common:emailAddress>sender@mail.be</common:emailAddress>
            <common:phoneNumber>022011111</common:phoneNumber>
        </tns:sender>
        <tns:internationalBox>
            <international:international>
                <international:product>bpack World Express Pro</international:product>
                <international:options>
                    <common:insured>
                        <common:basicInsurance/>
                    </common:insured>
                </international:options>
                <international:receiver>
                    <common:name>RECEIVER NAME</common:name>
                    <common:company>RECEIVER COMPANY</common:company>
                    <common:address>
                        <common:streetName>Pennsylvania Plaza</common:streetName>
                        <common:number>4</common:number>
                        <common:box>A</common:box>
                        <common:postalCode>10001</common:postalCode>
                        <common:locality>New York</common:locality>
                        <common:countryCode>US</common:countryCode>
                    </common:address>
                    <common:emailAddress>sender@mail.com</common:emailAddress>
                    <common:phoneNumber>+1212465741</common:phoneNumber>
                </international:receiver>
                <international:parcelWeight>900</international:parcelWeight>
                <international:customsInfo>
                    <international:parcelValue>1100</international:parcelValue>
                    <international:contentDescription>TSHIRTS</international:contentDescription>
                    <international:shipmentType>GIFT</international:shipmentType>
                    <international:parcelReturnInstructions>RTS</international:parcelReturnInstructions>
                    <international:privateAddress>false</international:privateAddress>
                    <international:currency>EUR</international:currency>
                    <international:amtPostagePaidByAddresse>12.50</international:amtPostagePaidByAddresse>
                </international:customsInfo>
                <international:parcelContents>
                    <international:parcelContent>
                        <international:numberOfItemType>2</international:numberOfItemType>
                        <international:valueOfItem>200</international:valueOfItem>
                        <international:itemDescription>t-shirt ARMANI L collection BG</international:itemDescription>
                        <international:nettoWeight>400</international:nettoWeight>
                        <international:hsTariffCode>11</international:hsTariffCode>
                        <international:originOfGoods>US</international:originOfGoods>
                    </international:parcelContent>
                    <international:parcelContent>
                        <international:numberOfItemType>4</international:numberOfItemType>
                        <international:valueOfItem>900</international:valueOfItem>
                        <international:itemDescription>t-shirt ARMANI L collection BG</international:itemDescription>
                        <international:nettoWeight>500</international:nettoWeight>
                        <international:hsTariffCode>12</international:hsTariffCode>
                        <international:originOfGoods>BE</international:originOfGoods>
                    </international:parcelContent>
                </international:parcelContents>
            </international:international>
        </tns:internationalBox>
        <tns:remark>bpack world Express Pro - Ins</tns:remark>
        <tns:additionalCustomerReference>Reference that can be used for cross-referencing</tns:additionalCustomerReference>
    </tns:box>
</tns:order>

XML;
    }

    /**
     * Create a generic DOM Document
     *
     * @return DOMDocument
     */
    private function createDomDocument()
    {
        $document = new DOMDocument('1.0', 'UTF-8');
        $document->preserveWhiteSpace = false;
        $document->formatOutput = true;

        return $document;
    }

    /**
     * Generate the document, by adding the namespace declarations
     *
     * @param DOMDocument $document
     * @param DOMElement  $element
     *
     * @return DOMDocument
     */
    private function generateDomDocument(DOMDocument $document, DOMElement $element)
    {
        $element->setAttribute('xmlns:common', Bpost::NS_V5_COMMON);
        $element->setAttribute('xmlns:tns', Bpost::NS_V5_GLOBAL);
        $element->setAttribute('xmlns', Bpost::NS_V5_NATIONAL);
        $element->setAttribute('xmlns:international', Bpost::NS_V5_INTERNATIONAL);
        $element->setAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
        $element->setAttribute('xsi:schemaLocation', Bpost::NS_V5_GLOBAL);

        $document->appendChild($element);

        return $document;
    }
}
