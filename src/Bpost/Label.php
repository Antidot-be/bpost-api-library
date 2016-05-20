<?php

namespace Bpost\BpostApiClient\Bpost;

use Bpost\BpostApiClient\Bpost\Label\Barcode;
use Bpost\BpostApiClient\Exception\BpostLogicException\BpostInvalidValueException;

/**
 * bPost Label class
 *
 * @author Tijs Verkoyen <php-bpost@verkoyen.eu>
 */
class Label
{

    const LABEL_MIME_TYPE_IMAGE_PNG = 'image/png';
    const LABEL_MIME_TYPE_IMAGE_PDF = 'image/pdf';
    const LABEL_MIME_TYPE_APPLICATION_PDF = 'application/pdf';

    /**
     * @var Barcode[]
     */
    private $barcodes = array();

    /**
     * @var string
     */
    private $mimeType;

    /**
     * @var string
     */
    private $bytes;

    /**
     * @deprecated setBarcodes
     * @param string $barcode
     */
    public function setBarcode($barcode)
    {
        $this->barcodes[0] = new Barcode($barcode);
    }

    /**
     * @param Barcode[] $barcodes
     */
    public function setBarcodes(array $barcodes)
    {
        $this->barcodes = $barcodes;
    }

    /**
     * @deprecated getBarcodes
     * @return string
     */
    public function getBarcode()
    {
        if (! $this->barcodes) {
            return '';
        }
        return $this->barcodes[0]->getBarcode();
    }

    /**
     * @return Barcode[]
     */
    public function getBarcodes()
    {
        return $this->barcodes;
    }

    /**
     * @param string $bytes
     */
    public function setBytes($bytes)
    {
        $this->bytes = $bytes;
    }

    /**
     * @return string
     */
    public function getBytes()
    {
        return $this->bytes;
    }

    /**
     * @param string $mimeType
     * @throws BpostInvalidValueException
     */
    public function setMimeType($mimeType)
    {
        if (!in_array($mimeType, self::getPossibleMimeTypeValues())) {
            throw new BpostInvalidValueException('mimeType', $mimeType, self::getPossibleMimeTypeValues());
        }

        $this->mimeType = $mimeType;
    }

    /**
     * @return string
     */
    public function getMimeType()
    {
        return $this->mimeType;
    }

    /**
     * @return array
     */
    public static function getPossibleMimeTypeValues()
    {
        return array(
            self::LABEL_MIME_TYPE_IMAGE_PNG,
            self::LABEL_MIME_TYPE_IMAGE_PDF,
            self::LABEL_MIME_TYPE_APPLICATION_PDF,
        );
    }

    /**
     * Output the bytes directly to the screen
     */
    public function output()
    {
        header('Content-type: ' . $this->getMimeType());
        echo $this->getBytes();
        exit;
    }

    /**
     * @param  \SimpleXMLElement $xml
     *
     * @return Label
     * @throws BpostInvalidValueException
     */
    public static function createFromXML(\SimpleXMLElement $xml)
    {
        $label = new Label();
        if (isset($xml->barcode) && $xml->barcode != '') {
            $barcodeObjects = array();
            foreach ($xml->barcode as $barcode) {
                $barcodeObjects[] = new Barcode( (string) $barcode);
            }
            $label->setBarcodes($barcodeObjects);
        }
        if (isset($xml->mimeType) && $xml->mimeType != '') {
            $label->setMimeType((string) $xml->mimeType);
        }
        if (isset($xml->bytes) && $xml->bytes != '') {
            $label->setBytes((string) base64_decode($xml->bytes));
        }

        return $label;
    }
}
