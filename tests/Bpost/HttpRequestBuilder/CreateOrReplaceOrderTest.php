<?php

namespace Tests\Bpost\HttpRequestBuilder;

use Bpost\BpostApiClient\Bpost\HttpRequestBuilder\CreateOrReplaceOrderBuilder;
use Bpost\BpostApiClient\Bpost\Order;
use Bpost\BpostApiClient\Bpost\Order\Address;
use Bpost\BpostApiClient\Bpost\Order\Box;
use Bpost\BpostApiClient\Bpost\Order\Box\AtBpost;
use Bpost\BpostApiClient\Bpost\Order\Box\Option\CashOnDelivery;
use Bpost\BpostApiClient\Bpost\Order\Box\Option\Messaging;
use Bpost\BpostApiClient\Bpost\Order\Box\Option\SaturdayDelivery;
use Bpost\BpostApiClient\Bpost\Order\Line;
use Bpost\BpostApiClient\Bpost\Order\PugoAddress;
use Bpost\BpostApiClient\Bpost\Order\Sender;
use PHPUnit_Framework_TestCase;

class CreateOrReplaceOrderTest extends PHPUnit_Framework_TestCase
{
    /**
     * @param array  $input
     * @param string $url
     * @param string $xml
     * @param string $method
     * @param bool   $isExpectXml
     * @param array  $headers
     *
     * @return void
     *
     * @dataProvider dataResults
     */
    public function testResults(array $input, $url, $xml, $headers, $method, $isExpectXml)
    {
        $builder = new CreateOrReplaceOrderBuilder($input[0], $input[1]);

        $this->assertSame($url, $builder->getUrl());
        $this->assertSame($method, $builder->getMethod());
        $this->assertSame($xml, $builder->getXml());
        $this->assertSame($isExpectXml, $builder->isExpectXml());
        $this->assertSame($headers, $builder->getHeaders());
    }

    public function dataResults()
    {
        $accountId = '123456789';

        return array(
            array(
                'input' => array($this->getOrder(), $accountId),
                'url' => '/orders',
                'xml' => $this->getOrderXml(),
                'headers' => array('Content-type: application/vnd.bpost.shm-order-v5+XML'),
                'method' => 'POST',
                'isExpectXml' => false,
            ),
        );
    }

    /**
     * @return Order
     */
    private function getOrder()
    {
        $order = new Order('ref_1');

        $order->setCostCenter('Cost Center');

        $order->setLines(array(new Line('Product 1', 1)));
        $order->addLine(new Line('Product 1', 5));

        $senderAddress = new Address();
        $senderAddress->setStreetName('MUNT');
        $senderAddress->setNumber(1);
        $senderAddress->setBox(1);
        $senderAddress->setPostalCode(1000);
        $senderAddress->setLocality('Brussel');
        $senderAddress->setCountryCode('BE');
        $senderAddress->setBox(1);

        $pugoAddress = new PugoAddress();
        $pugoAddress->setStreetName('Turnhoutsebaan');
        $pugoAddress->setNumber(468);
        $pugoAddress->setBox('A');
        $pugoAddress->setPostalCode(2110);
        $pugoAddress->setLocality('Wijnegem');
        $pugoAddress->setCountryCode('BE');

        $sender = new Sender();
        $sender->setName('SENDER NAME');
        $sender->setCompany('SENDER COMPANY');
        $sender->setAddress($senderAddress);
        $sender->setEmailAddress('sender@mail.be');
        $sender->setPhoneNumber('022011111');

        $atBpost = new AtBpost();

        $atBpost->setOptions(array(
            new Messaging('infoDistributed', 'EN', null, '0476123456'),
            new Messaging('keepMeInformed', 'EN', null, '0032475123456'),
        ));
        $atBpost->addOption(new SaturdayDelivery());
        $atBpost->addOption(new CashOnDelivery(1251, 'BE19210023508812', 'GEBABEBB'));

        $atBpost->setWeight(2000);

        $atBpost->setPugoId(207500);
        $atBpost->setPugoName('WIJNEGEM');
        $atBpost->setPugoAddress($pugoAddress);
        $atBpost->setReceiverName('RECEIVER NAME');
        $atBpost->setReceiverCompany('RECEIVER COMPANY');
        $atBpost->setRequestedDeliveryDate('2020-10-22');

        $box = new Box();
        $box->setSender($sender);
        $box->setNationalBox($atBpost);
        $box->setRemark('bpack@bpost VAS 038 - COD+SAT+iD');
        $box->setAdditionalCustomerReference('Reference that can be used for cross-referencing');

        $order->setBoxes(array());
        $this->assertCount(0, $order->getBoxes());
        $order->addBox($box);

        return $order;
    }

    private function getOrderXml()
    {
        return <<< XML
<?xml version="1.0" encoding="utf-8"?>
<tns:order xmlns="http://schema.post.be/shm/deepintegration/v5/national" xmlns:common="http://schema.post.be/shm/deepintegration/v5/common" xmlns:tns="http://schema.post.be/shm/deepintegration/v5/" xmlns:international="http://schema.post.be/shm/deepintegration/v5/international" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://schema.post.be/shm/deepintegration/v5/">
  <tns:accountId>123456789</tns:accountId>
  <tns:reference>ref_1</tns:reference>
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
    <tns:nationalBox>
      <atBpost>
        <product>bpack@bpost</product>
        <options>
          <common:infoDistributed language="EN">
            <common:mobilePhone>0476123456</common:mobilePhone>
          </common:infoDistributed>
          <common:keepMeInformed language="EN">
            <common:mobilePhone>0032475123456</common:mobilePhone>
          </common:keepMeInformed>
          <common:saturdayDelivery/>
          <common:cod>
            <common:codAmount>1251</common:codAmount>
            <common:iban>BE19210023508812</common:iban>
            <common:bic>GEBABEBB</common:bic>
          </common:cod>
        </options>
        <weight>2000</weight>
        <pugoId>207500</pugoId>
        <pugoName>WIJNEGEM</pugoName>
        <pugoAddress>
          <common:streetName>Turnhoutsebaan</common:streetName>
          <common:number>468</common:number>
          <common:box>A</common:box>
          <common:postalCode>2110</common:postalCode>
          <common:locality>Wijnegem</common:locality>
          <common:countryCode>BE</common:countryCode>
        </pugoAddress>
        <receiverName>RECEIVER NAME</receiverName>
        <receiverCompany>RECEIVER COMPANY</receiverCompany>
        <requestedDeliveryDate>2020-10-22</requestedDeliveryDate>
      </atBpost>
    </tns:nationalBox>
    <tns:remark>bpack@bpost VAS 038 - COD+SAT+iD</tns:remark>
    <tns:additionalCustomerReference>Reference that can be used for cross-referencing</tns:additionalCustomerReference>
  </tns:box>
</tns:order>

XML;
    }
}
