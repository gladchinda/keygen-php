<?php

use Keygen\Keygen;
use PHPUnit\Framework\TestCase;
use Keygen\Generators\TokenGenerator;
use Keygen\Generators\NumericGenerator;
use Keygen\Generators\RandomByteGenerator;
use Keygen\Generators\AlphaNumericGenerator;

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
	 * @covers Keygen\AbstractGenerator::__get
	 * @covers Keygen\AbstractGenerator::__isset
	 */
	public function testGeneratorProperties()
	{
		$this->assertSame(16, $this->keygen->length);
		$this->assertNull($this->keygen->prefix);
		$this->assertNull($this->keygen->suffix);
	}

	/**
	 * @covers ::__call
	 * @covers ::newGenerator
	 * @covers ::newGeneratorFromAlias
	 * @covers Keygen\GeneratorFactory::create
	 * @covers Keygen\AbstractGenerator::__call
	 * @covers Keygen\AbstractGenerator::__callStatic
	 */
	public function testGeneratorMethods()
	{
		$this->assertInstanceOf(TokenGenerator::class, Keygen::token());
		$this->assertInstanceOf(NumericGenerator::class, Keygen::numeric());
		$this->assertInstanceOf(RandomByteGenerator::class, Keygen::bytes());
		$this->assertInstanceOf(AlphaNumericGenerator::class, Keygen::alphanum());

		$this->assertInstanceOf(TokenGenerator::class, $this->keygen->token());
		$this->assertInstanceOf(NumericGenerator::class, $this->keygen->numeric());
		$this->assertInstanceOf(RandomByteGenerator::class, $this->keygen->bytes());
		$this->assertInstanceOf(AlphaNumericGenerator::class, $this->keygen->alphanum());

		$this->assertSame(16, Keygen::token()->length);
		$this->assertSame(32, Keygen::bytes(32)->length);
		$this->assertSame(20, Keygen::numeric(20)->length);
		$this->assertSame(12, Keygen::alphanum(12)->length);

		$this->assertSame(16, $this->keygen->token()->length);
		$this->assertSame(32, $this->keygen->bytes(32)->length);
		$this->assertSame(20, $this->keygen->numeric(20)->length);
		$this->assertSame(12, $this->keygen->alphanum(12)->length);
	}

	/**
	 * @covers ::__call
	 * @covers ::newGenerator
	 * @covers ::newGeneratorFromAlias
	 * @covers Keygen\GeneratorFactory::create
	 * @covers Keygen\AbstractGenerator::__call
	 * @covers Keygen\AbstractGenerator::__callStatic
	 * @expectedException \BadMethodCallException
	 * @expectedExceptionMessage Call to unknown method Keygen\Keygen::unknown()
	 */
	public function testInvalidGeneratorMethods()
	{
		$this->keygen->unknown();
	}
}
