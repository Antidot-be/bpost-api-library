<?php

namespace Tests\BpostApiExamples\FetchOrder;

use Bpost\BpostApiClient\Bpost\Order;
use Bpost\BpostApiClient\Bpost\Order\Box;
use Bpost\BpostApiClient\Bpost\ProductConfiguration\Product;
use PHPUnit_Framework_TestCase;

class NationalBoxAtBpostTest extends PHPUnit_Framework_TestCase
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
			<ns3:atBpost>
				<ns3:product>bpack@bpost</ns3:product>
				<ns3:options>
					<ns2:keepMeInformed language="FR">
						<ns2:emailAddress>tester.test@telenet.be</ns2:emailAddress>
					</ns2:keepMeInformed>
					<ns2:insured>
						<ns2:additionalInsurance value="1"/>
					</ns2:insured>
					<ns2:signed/>
				</ns3:options>
				<ns3:weight>1000</ns3:weight>
				<ns3:openingHours/>
				<ns3:pugoId>619037</ns3:pugoId>
				<ns3:pugoName>GB EXPRESS HOEILAART</ns3:pugoName>
				<ns3:pugoAddress>
					<ns2:streetName>JOSEPH KUMPSSTRAAT</ns2:streetName>
					<ns2:number>5</ns2:number>
					<ns2:postalCode>1560</ns2:postalCode>
					<ns2:locality>HOEILAART</ns2:locality>
					<ns2:countryCode>BE</ns2:countryCode>
				</ns3:pugoAddress>
				<ns3:receiverName>Tester Test</ns3:receiverName>
			</ns3:atBpost>
		</nationalBox>
		<additionalCustomerReference>additional-reference</additionalCustomerReference>
		<barcode>323212086559959097180037</barcode>
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

        /** @var Box\AtBpost $nationalBox */
        $nationalBox = $box->getNationalBox();

        $this->assertInstanceOf('Bpost\BpostApiClient\Bpost\Order\Box\National', $nationalBox);
        $this->assertSame(Product::PRODUCT_NAME_BPACK_AT_BPOST, $nationalBox->getProduct());
        $this->assertSame(1000, $nationalBox->getWeight());
        $this->assertSame('619037', $nationalBox->getPugoId());
        $this->assertSame('GB EXPRESS HOEILAART', $nationalBox->getPugoName());
        $this->assertNotNull($nationalBox->getPugoAddress());

        $pugoAddress = $nationalBox->getPugoAddress();

        $this->assertSame('Tester Test', $nationalBox->getReceiverName());
        $this->assertNull($nationalBox->getReceiverCompany());
        $this->assertSame('JOSEPH KUMPSSTRAAT', $pugoAddress->getStreetName());
        $this->assertSame('5', $pugoAddress->getNumber());
        $this->assertSame('1560', $pugoAddress->getPostalCode());
        $this->assertSame('HOEILAART', $pugoAddress->getLocality());
        $this->assertSame('BE', $pugoAddress->getCountryCode());

        $this->assertCount(3, $nationalBox->getOptions());
        $options = $nationalBox->getOptions();

        /** @var Box\Option\Messaging $option */
        $option = $options[0];
        $this->assertInstanceOf('Bpost\BpostApiClient\Bpost\Order\Box\Option\Messaging', $option);
        $this->assertSame(Box\Option\Messaging::MESSAGING_TYPE_KEEP_ME_INFORMED, $option->getType());
        $this->assertSame(Box\Option\Messaging::MESSAGING_LANGUAGE_FR, $option->getLanguage());
        $this->assertNull($option->getMobilePhone());
        $this->assertSame('tester.test@telenet.be', $option->getEmailAddress());

        /** @var Box\Option\Insured $option */
        $option = $options[1];
        $this->assertInstanceOf('Bpost\BpostApiClient\Bpost\Order\Box\Option\Insured', $option);
        $this->assertSame(Box\Option\Insured::INSURANCE_TYPE_BASIC_INSURANCE, $option->getType());
        $this->assertNull($option->getValue());

        /** @var Box\Option\Signed $option */
        $option = $options[2];
        $this->assertInstanceOf('Bpost\BpostApiClient\Bpost\Order\Box\Option\Signed', $option);
    }
}
