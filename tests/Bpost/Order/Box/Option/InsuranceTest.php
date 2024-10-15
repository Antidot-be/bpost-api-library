<?php

namespace Tests\Bpost\Order\Box\Option;

use Bpost\BpostApiClient\Bpost;
use Bpost\BpostApiClient\Bpost\Order\Box\Option\Insured;
use Bpost\BpostApiClient\Exception\BpostLogicException\BpostInvalidValueException;
use DOMDocument;
use DOMElement;
use Exception;
use PHPUnit_Framework_TestCase;
use SimpleXMLElement;

class InsuranceTest extends PHPUnit_Framework_TestCase
{
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

    /**
     * Tests Insurance->toXML
     */
    public function testToXML()
    {
        $self = new Insured(
            Insured::INSURANCE_TYPE_ADDITIONAL_INSURANCE,
            Insured::INSURANCE_AMOUNT_UP_TO_2500_EUROS
        );

        // Without specific prefix
        $rootDom = $this->createDomDocument();
        $document = $this->generateDomDocument($rootDom, $self->toXML($rootDom));

        $this->assertSame($this->getXml(), $document->saveXML());

        // With specific prefix
        $rootDom = $this->createDomDocument();
        $document = $this->generateDomDocument($rootDom, $self->toXML($rootDom, 'mushroom'));

        $this->assertSame($this->getNamespaceXml(), $document->saveXML());
    }

    /**
     * Test validation in the setters
     */
    public function testFaultyProperties()
    {
        try {
            new Insured(str_repeat('a', 10));
            $this->fail('BpostInvalidValueException not launched');
        } catch (BpostInvalidValueException $e) {
            // Nothing, the exception is good
        } catch (Exception $e) {
            $this->fail('BpostInvalidValueException not caught');
        }

        try {
            new Insured('additionalInsurance', str_repeat('1', 10));
            $this->fail('BpostInvalidValueException not launched');
        } catch (BpostInvalidValueException $e) {
            // Nothing, the exception is good
        } catch (Exception $e) {
            $this->fail('BpostInvalidValueException not caught');
        }

        // Exceptions were caught,
        $this->assertTrue(true);
    }

    public function testCreateFromXml()
    {
        $insured = Insured::createFromXml(new SimpleXMLElement($this->getAdditionalValue1Xml()));

        $this->assertSame(Insured::INSURANCE_TYPE_BASIC_INSURANCE, $insured->getType());
        $this->assertNull($insured->getValue());
    }

    private function getXml()
    {
        return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<common:insured xmlns="http://schema.post.be/shm/deepintegration/v5/national" xmlns:common="http://schema.post.be/shm/deepintegration/v5/common" xmlns:tns="http://schema.post.be/shm/deepintegration/v5/" xmlns:international="http://schema.post.be/shm/deepintegration/v5/international" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://schema.post.be/shm/deepintegration/v5/">
  <common:additionalInsurance value="2"/>
</common:insured>

XML;
    }

    private function getAdditionalValue1Xml()
    {
        return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<common:insured xmlns="http://schema.post.be/shm/deepintegration/v3/national" xmlns:common="http://schema.post.be/shm/deepintegration/v3/common" xmlns:tns="http://schema.post.be/shm/deepintegration/v3/" xmlns:international="http://schema.post.be/shm/deepintegration/v3/international" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://schema.post.be/shm/deepintegration/v3/">
  <common:additionalInsurance value="1"/>
</common:insured>

XML;
    }

    private function getNamespaceXml()
    {
        return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<mushroom:insured xmlns="http://schema.post.be/shm/deepintegration/v5/national" xmlns:common="http://schema.post.be/shm/deepintegration/v5/common" xmlns:tns="http://schema.post.be/shm/deepintegration/v5/" xmlns:international="http://schema.post.be/shm/deepintegration/v5/international" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://schema.post.be/shm/deepintegration/v5/">
  <mushroom:additionalInsurance value="2"/>
</mushroom:insured>

XML;
    }
}
