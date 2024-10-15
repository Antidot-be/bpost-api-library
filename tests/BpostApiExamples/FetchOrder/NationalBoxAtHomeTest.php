<?php

namespace Tests\BpostApiExamples\FetchOrder;

use Bpost\BpostApiClient\Bpost\Order;
use Bpost\BpostApiClient\Bpost\Order\Box;
use Bpost\BpostApiClient\Bpost\ProductConfiguration\Product;
use PHPUnit_Framework_TestCase;

class NationalBoxAtHomeTest extends PHPUnit_Framework_TestCase
{

    private function getXmlFromBpost()
    {
        return <<<XML
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<orderInfo xmlns="http://schema.post.be/shm/deepintegration/v3/" xmlns:ns2="http://schema.post.be/shm/deepintegration/v3/common" xmlns:ns3="http://schema.post.be/shm/deepintegration/v3/national" xmlns:ns4="http://schema.post.be/shm/deepintegration/v3/international">
	<accountId>120865</accountId>
	<reference>my-reference</reference>
	<box>
		<sender>
			<ns2:name/>
			<ns2:company>Antidot</ns2:company>
			<ns2:address>
				<ns2:streetName>Rue du Grand Duc</ns2:streetName>
				<ns2:number>13</ns2:number>
				<ns2:postalCode>1040</ns2:postalCode>
				<ns2:locality>Etterbeek</ns2:locality>
				<ns2:countryCode>BE</ns2:countryCode>
			</ns2:address>
			<ns2:emailAddress>no-reply@antidot.com</ns2:emailAddress>
			<ns2:phoneNumber>0470000000</ns2:phoneNumber>
		</sender>
		<nationalBox>
			<ns3:atHome>
				<ns3:product>bpack 24h Pro</ns3:product>
				<ns3:options>
					<ns2:insured>
						<ns2:additionalInsurance value="1"/>
					</ns2:insured>
					<ns2:signed/>
				</ns3:options>
				<ns3:weight>1000</ns3:weight>
				<ns3:openingHours/>
				<ns3:receiver>
					<ns2:name>test tester</ns2:name>
					<ns2:address>
						<ns2:streetName>Grand Place</ns2:streetName>
						<ns2:number>1</ns2:number>
						<ns2:postalCode>1000</ns2:postalCode>
						<ns2:locality>Bruxelles</ns2:locality>
						<ns2:countryCode>BE</ns2:countryCode>
					</ns2:address>
					<ns2:emailAddress>esolutions@bpost.be</ns2:emailAddress>
					<ns2:phoneNumber>1111111111</ns2:phoneNumber>
				</ns3:receiver>
			</ns3:atHome>
		</nationalBox>
		<additionalCustomerReference>additional-reference</additionalCustomerReference>
		<barcode>323212086559959096067040</barcode>
		<status>ANNOUNCED</status>
	</box>
</orderInfo>
XML;
    }

    public function testCreateFromXml()
    {
        $xml = simplexml_load_string($this->getXmlFromBpost());
        $order = Order::createFromXML($xml);

        $this->assertSame('my-reference', $order->getReference());
        $this->assertNull($order->getCostCenter());
        $this->assertNull($order->getLines());
        $this->assertNotNull($order->getBoxes());
        $this->assertCount(1, $order->getBoxes());

        /** @var Box $box */
        $box = current($order->getBoxes());
        $sender = $box->getSender();

        $this->assertNull($sender->getName());
        $this->assertSame('Antidot', $sender->getCompany());
        $this->assertSame('no-reply@antidot.com', $sender->getEmailAddress());
        $this->assertSame('0470000000', $sender->getPhoneNumber());
        $this->assertSame('Rue du Grand Duc', $sender->getAddress()->getStreetName());
        $this->assertSame('13', $sender->getAddress()->getNumber());
        $this->assertSame('1040', $sender->getAddress()->getPostalCode());
        $this->assertSame('Etterbeek', $sender->getAddress()->getLocality());
        $this->assertSame('BE', $sender->getAddress()->getCountryCode());

        $this->assertSame('additional-reference', $box->getAdditionalCustomerReference());
        $this->assertSame(Box::BOX_STATUS_ANNOUNCED, $box->getStatus());
        $this->assertNull($box->getRemark());
        $this->assertNull($box->getInternationalBox());
        $this->assertNotNull($box->getNationalBox());

        /** @var Box\AtHome $nationalBox */
        $nationalBox = $box->getNationalBox();

        $this->assertInstanceOf('Bpost\BpostApiClient\Bpost\Order\Box\National', $nationalBox);
        $this->assertSame(Product::PRODUCT_NAME_BPACK_24H_PRO, $nationalBox->getProduct());
        $this->assertSame(1000, $nationalBox->getWeight());
        $this->assertNotNull($nationalBox->getReceiver());

        $receiver = $nationalBox->getReceiver();

        $this->assertSame('test tester', $receiver->getName());
        $this->assertNull($receiver->getCompany());
        $this->assertSame('esolutions@bpost.be', $receiver->getEmailAddress());
        $this->assertSame('1111111111', $receiver->getPhoneNumber());
        $this->assertSame('Grand Place', $receiver->getAddress()->getStreetName());
        $this->assertSame('1', $receiver->getAddress()->getNumber());
        $this->assertSame('1000', $receiver->getAddress()->getPostalCode());
        $this->assertSame('Bruxelles', $receiver->getAddress()->getLocality());
        $this->assertSame('BE', $receiver->getAddress()->getCountryCode());


//        var_dump($nationalBox->getOptions());
        $this->assertCount(2, $nationalBox->getOptions());
        $options = $nationalBox->getOptions();

        /** @var Box\Option\Insured $option */
        $option = $options[0];
        $this->assertInstanceOf('Bpost\BpostApiClient\Bpost\Order\Box\Option\Insured', $option);
        $this->assertSame(Box\Option\Insured::INSURANCE_TYPE_BASIC_INSURANCE, $option->getType());
        $this->assertNull($option->getValue());

        /** @var Box\Option\Signed $option */
        $option = $options[1];
        $this->assertInstanceOf('Bpost\BpostApiClient\Bpost\Order\Box\Option\Signed', $option);
    }
}
