<?php
namespace Bpost;

use Bpost\BpostApiClient\Bpost\Label;

class BarcodeTest extends \PHPUnit_Framework_TestCase {

	public function testConstructor() {
		$this->assertNotNull( new Label\Barcode( '' ) );
	}

	public function testGetter() {
		$barcode = new Label\Barcode( '546545' );
		$this->assertSame('546545', $barcode->getBarcode() );
	}


	public function testIsReturnEmpty() {
		$barcode = new Label\Barcode( '' );
		$this->assertFalse( $barcode->isReturnBarcode() );
	}

	public function testIsReturnThreeInvalidDigit() {
		$barcode = new Label\Barcode( '105' );
		$this->assertFalse( $barcode->isReturnBarcode() );
	}

	public function testIsReturnThreeValidDigit() {
		$barcode = new Label\Barcode( '050' );
		$this->assertTrue( $barcode->isReturnBarcode() );
	}

	public function testIsReturnValidBpostBarcode() {
		$barcode = new Label\Barcode( '323210742359909732710050' );
		$this->assertTrue( $barcode->isReturnBarcode() );
	}

}
