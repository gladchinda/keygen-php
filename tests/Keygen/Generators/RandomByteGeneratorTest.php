<?php

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
	 * @covers ::hex
	 * @covers ::base64
	 * @covers ::resetByteOutput
	 * @covers ::enableByteOutput
	 */
	public function testOutputTypes()
	{
		$this->assertFalse($this->generator->hex);
		$this->assertFalse($this->generator->base64);

		$this->generator->hex();
		$this->assertTrue($this->generator->hex);
		$this->assertFalse($this->generator->base64);
		
		$this->generator->base64();
		$this->assertFalse($this->generator->hex);
		$this->assertTrue($this->generator->base64);
	}

	/**
	 * @covers ::keygen
	 * @covers ::bytesAsHex
	 * @covers ::bytesAsBase64
	 * @covers ::generateRandomBytes
	 * @covers ::enabledByteOutput
	 * @covers ::finishKeyGeneration
	 */
	public function testKeyGeneration()
	{
		$key = $this->generator->generate();
		$this->assertEquals(16, strlen($key));

		$this->assertFalse($this->generator->hex);
		$hexKey = $this->generator->hex()->generate();
		$this->assertRegExp('/^[a-f0-9]{16}$/', $hexKey);
		$this->assertFalse($this->generator->hex);

		$this->assertFalse($this->generator->base64);
		$base64Key = $this->generator->base64()->generate();
		$this->assertRegExp('/^[a-zA-Z0-9+\/=]{16}$/', $base64Key);
		$this->assertFalse($this->generator->base64);
	}
}
