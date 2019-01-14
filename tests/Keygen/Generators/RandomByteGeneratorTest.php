<?php

namespace Keygen\Tests;

use PHPUnit\Framework\TestCase;
use Keygen\Generators\RandomByteGenerator;

/**
 * @coversDefaultClass RandomByteGenerator
 */
final class RandomByteGeneratorTest extends TestCase
{
	protected $generator;

	protected function setUp()
	{
		$this->generator = new RandomByteGenerator;
	}

	/**
	 * @covers Keygen\Generators\RandomByteGenerator::hex
	 * @covers Keygen\Generator::keygen
	 * @covers Keygen\Generator::generate
	 */
	public function testKeyGeneration()
	{
		$key = $this->generator->generate();
		$this->assertEquals(16, strlen($key));

		$this->assertFalse($this->generator->hex);
		$hexKey = $this->generator->hex()->generate();
		$this->assertRegExp('/^[a-f0-9]{16}$/', $hexKey);
		$this->assertFalse($this->generator->hex);
	}
}
