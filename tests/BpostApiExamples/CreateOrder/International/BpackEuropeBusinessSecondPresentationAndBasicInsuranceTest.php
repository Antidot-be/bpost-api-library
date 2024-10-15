<?php

namespace Tests\BpostApiExamples\CreateOrder\International;

use Bpost\BpostApiClient\Bpost;
use Bpost\BpostApiClient\Bpost\Order;
use Bpost\BpostApiClient\Bpost\Order\Address;
use Bpost\BpostApiClient\Bpost\Order\Box;
use Bpost\BpostApiClient\Bpost\Order\Box\CustomsInfo\CustomsInfo;
use Bpost\BpostApiClient\Bpost\Order\Box\International;
use Bpost\BpostApiClient\Bpost\Order\Box\Option\AutomaticSecondPresentation;
use Bpost\BpostApiClient\Bpost\Order\Box\Option\Insured;
use Bpost\BpostApiClient\Bpost\Order\Line;
use Bpost\BpostApiClient\Bpost\Order\Receiver;
use Bpost\BpostApiClient\Bpost\Order\Sender;
use DOMDocument;
use DOMElement;
use PHPUnit_Framework_TestCase;

class BpackEuropeBusinessSecondPresentationAndBasicInsuranceTest extends PHPUnit_Framework_TestCase
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
        $order = new Order('bpack Europe Business - Ins+2ndPr');
        $order->setCostCenter('Cost Center');

        $order->addLine(new Line('Product 1', 1));
        $order->addLine(new Line('Product 1', 5));

        $order->addBox($box = new Box());

        $box->setSender($sender = new Sender());
        $box->setInternationalBox($internationalBox = new International());
        $box->setRemark('bpack Europe Business - Ins+2ndPr');
        $box->setAdditionalCustomerReference('Reference that can be used for cross-referencing');

        $sender->setName('SENDER NAME');
        $sender->setCompany('SENDER COMPANY');
        $sender->setAddress(new Address('MUNT', 1, 1, '1000', 'Brussel', 'BE'));
        $sender->setEmailAddress('sender@mail.be');
        $sender->setPhoneNumber('022011111');

        $internationalBox->setProduct('bpack Europe Business');

        $internationalBox->addOption(new AutomaticSecondPresentation());
        $internationalBox->addOption(new Insured(Insured::INSURANCE_TYPE_BASIC_INSURANCE));

        $internationalBox->setReceiver($receiver = new Receiver());
        $internationalBox->setParcelWeight(2000);
        $internationalBox->setCustomsInfo($customsInfo = new CustomsInfo());

        $receiver->setName('RECEIVER NAME');
        $receiver->setCompany('RECEIVER COMPANY');
        $receiver->setAddress(new Address('ArenA Boulevard', 1, 'A', '1100 DL', 'Amsterdam', 'NL'));
        $receiver->setEmailAddress('sender@mail.com');
        $receiver->setPhoneNumber('+1212465741');

        $customsInfo->setParcelValue(700);
        $customsInfo->setContentDescription('PAPER DOCUMENTS');
        $customsInfo->setShipmentType('DOCUMENTS');
        $customsInfo->setParcelReturnInstructions('RTS');
        $customsInfo->setPrivateAddress(false);

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
    <tns:reference>bpack Europe Business - Ins+2ndPr</tns:reference>
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
                <international:product>bpack Europe Business</international:product>
                <international:options>
                    <common:automaticSecondPresentation/>
                    <common:insured>
                        <common:basicInsurance/>
                    </common:insured>
                </international:options>
                <international:receiver>
                    <common:name>RECEIVER NAME</common:name>
                    <common:company>RECEIVER COMPANY</common:company>
                    <common:address>
                        <common:streetName>ArenA Boulevard</common:streetName>
                        <common:number>1</common:number>
                        <common:box>A</common:box>
                        <common:postalCode>1100 DL</common:postalCode>
                        <common:locality>Amsterdam</common:locality>
                        <common:countryCode>NL</common:countryCode>
                    </common:address>
                    <common:emailAddress>sender@mail.com</common:emailAddress>
                    <common:phoneNumber>+1212465741</common:phoneNumber>
                </international:receiver>
                <international:parcelWeight>2000</international:parcelWeight>
                <international:customsInfo>
                    <international:parcelValue>700</international:parcelValue>
                    <international:contentDescription>PAPER DOCUMENTS</international:contentDescription>
                    <international:shipmentType>DOCUMENTS</international:shipmentType>
                    <international:parcelReturnInstructions>RTS</international:parcelReturnInstructions>
                    <international:privateAddress>false</international:privateAddress>
                </international:customsInfo>
            </international:international>
        </tns:internationalBox>
        <tns:remark>bpack Europe Business - Ins+2ndPr</tns:remark>
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
