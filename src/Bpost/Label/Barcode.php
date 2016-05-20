<?php
namespace Bpost\BpostApiClient\Bpost\Label;


/**
 * Class Barcode
 * @package Bpost\BpostApiClient\Bpost\Label
 */
class Barcode {
	/** suffix present on a barcode of return type */
	const BARCODE_TYPE_RETURN_SUFFIX = '050';

	/** @var string */
	private $barcode;

	/**
	 * @param string $barcode
	 */
	public function __construct( $barcode ) {
		$this->barcode = strtoupper( (string) $barcode );
	}

	/**
	 * @return string
	 */
	public function getBarcode() {
		return $this->barcode;
	}

	/**
	 * @return bool
	 */
	public function isReturnBarcode() {
		return substr( $this->barcode, - 3 ) === self::BARCODE_TYPE_RETURN_SUFFIX;
	}

}