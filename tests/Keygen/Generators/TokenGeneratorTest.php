<?php

use PHPUnit\Framework\TestCase;
use Keygen\Generators\TokenGenerator;

/**
 * @coversDefaultClass TokenGenerator
 */
final class TokenGeneratorTest extends TestCase
{
	protected $generator;

	protected function setUp()
	{
		$this->generator = new TokenGenerator;
	}

	/**
	 * @covers ::keygen
	 */
	public function testKeyGeneration()
	{
		$key = $this->generator->generate();
		$this->assertRegExp('/^[a-zA-Z0-9+\/]{16}$/', $key);
	}
}
