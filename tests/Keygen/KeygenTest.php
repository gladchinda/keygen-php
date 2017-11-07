<?php

use Keygen\Keygen;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass Keygen
 */
final class KeygenTest extends TestCase
{
	protected $keygen;

	protected function setUp()
	{
		$this->keygen = new Keygen;
	}

	/**
	 * @covers ::__call
	 * @covers ::overloadGeneratorMethod
	 * @covers ::newGeneratorFromAlias
	 */
	public function testGeneratorMethods()
	{
		$this->assertSame(16, Keygen::token()->length);
		$this->assertSame(32, Keygen::bytes(32)->length);
		$this->assertSame(20, Keygen::numeric20()->length);
		$this->assertSame(12, Keygen::digits_12()->length);

		$this->assertFalse(Keygen::alnum_32()->randomLength);
		$this->assertTrue(Keygen::alnum_random()->randomLength);
		$this->assertTrue(Keygen::alphanumRandom()->randomLength);
		$this->assertTrue(Keygen::alphanumeric_random()->randomLength);

		$this->assertFalse(Keygen::anyfirst_numeric_32()->nonZeroFirst);
		$this->assertTrue(Keygen::nzfirstDigits()->nonZeroFirst);
		
		$this->assertFalse(Keygen::hex_bytes_24()->base64);
		$this->assertTrue(Keygen::hexBytes()->hex);
		$this->assertFalse(Keygen::base64_bytes24()->hex);
		$this->assertTrue(Keygen::base64Bytes_20()->base64);
	}
	
	/**
	 * @covers ::__get
	 * @covers ::overloadGeneratorMethod
	 * @covers ::newGeneratorFromAlias
	 */
	public function testGeneratorPropertyAccessors()
	{
		$this->assertSame(16, $this->keygen->token->length);
		$this->assertSame(20, $this->keygen->numeric20->length);
		$this->assertSame(12, $this->keygen->digits_12->length);

		$this->assertFalse($this->keygen->bytes_32->randomLength);
		$this->assertTrue($this->keygen->alnum_random->randomLength);
		$this->assertTrue($this->keygen->alphanumRandom->randomLength);
		$this->assertTrue($this->keygen->alphanumeric_random->randomLength);

		$this->assertFalse($this->keygen->anyfirst_numeric_32->nonZeroFirst);
		$this->assertTrue($this->keygen->nzfirstDigits->nonZeroFirst);
		
		$this->assertFalse($this->keygen->hex_bytes_24->base64);
		$this->assertTrue($this->keygen->hexBytes->hex);
		$this->assertFalse($this->keygen->base64_bytes24->hex);
		$this->assertTrue($this->keygen->base64Bytes_20->base64);
	}
}
