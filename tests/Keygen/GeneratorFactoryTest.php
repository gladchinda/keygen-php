<?php

use Keygen\GeneratorFactory;
use PHPUnit\Framework\TestCase;
use Keygen\Generators\TokenGenerator;
use Keygen\Generators\NumericGenerator;
use Keygen\Generators\RandomByteGenerator;
use Keygen\Generators\AlphaNumericGenerator;

/**
 * @coversDefaultClass Keygen\GeneratorFactory
 */
final class GeneratorFactoryTest extends TestCase
{
	/**
	 * @covers ::create
	 * @expectedException \InvalidArgumentException
	 * @expectedExceptionMessage Cannot create unknown generator type.
	 */
	public function testCreateGenerators()
	{
		$this->assertInstanceOf(TokenGenerator::class, GeneratorFactory::create('token'));
		$this->assertInstanceOf(NumericGenerator::class, GeneratorFactory::create('numeric'));
		$this->assertInstanceOf(RandomByteGenerator::class, GeneratorFactory::create('randombyte'));
		$this->assertInstanceOf(AlphaNumericGenerator::class, GeneratorFactory::create('alphanumeric'));

		$generator = GeneratorFactory::create('bytes');
	}
}
