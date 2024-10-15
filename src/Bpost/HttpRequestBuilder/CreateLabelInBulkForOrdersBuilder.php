<?php

namespace Bpost\BpostApiClient\Bpost\HttpRequestBuilder;

use Bpost\BpostApiClient\Bpost;
use Bpost\BpostApiClient\Common\ValidatedValue\LabelFormat;
use DOMDocument;
use DOMException;

class CreateLabelInBulkForOrdersBuilder implements HttpRequestBuilderInterface
{
    /**
     * @var array
     */
    protected $references;
    /**
     * @var LabelFormat
     */
    protected $labelFormat;
    /**
     * @var bool
     */
    protected $asPdf;
    /**
     * @var bool
     */
    protected $withReturnLabels;
    /**
     * @var bool
     */
    private $forcePrinting;

    /**
     * @param array       $references
     * @param LabelFormat $labelFormat
     * @param bool        $asPdf
     * @param bool        $withReturnLabels
     * @param bool        $forcePrinting
     */
    public function __construct($references, LabelFormat $labelFormat, $asPdf, $withReturnLabels, $forcePrinting)
    {
        $this->references = $references;
        $this->labelFormat = $labelFormat;
        $this->asPdf = $asPdf;
        $this->withReturnLabels = $withReturnLabels;
        $this->forcePrinting = $forcePrinting;
    }

    /**
     * @return string
     *
     * @throws DOMException
     */
    public function getXml()
    {
        $document = new DOMDocument('1.0', 'UTF-8');
        $document->preserveWhiteSpace = false;
        $document->formatOutput = true;

        $batchLabels = $document->createElement('batchLabels');
        $batchLabels->setAttribute('xmlns', Bpost::NS_V3_GLOBAL);
        foreach ($this->references as $reference) {
            $batchLabels->appendChild(
                $document->createElement('order', $reference)
            );
        }
        $document->appendChild($batchLabels);

        return $document->saveXML();
    }

    /**
     * @return string[]
     */
    public function getHeaders()
    {
        return array(
            'Accept: application/vnd.bpost.shm-label-' . ($this->asPdf ? 'pdf' : 'image') . '-v3.4+XML',
            'Content-Type: application/vnd.bpost.shm-labelRequest-v3+XML',
        );
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return '/labels/' . $this->labelFormat->getValue()
            . ($this->withReturnLabels ? '/withReturnLabels' : '')
            . ($this->forcePrinting ? '?forcePrinting=true' : '');
    }

    public function isExpectXml()
    {
        return true;
    }

    public function getMethod()
    {
        return self::METHOD_POST;
    }
}
