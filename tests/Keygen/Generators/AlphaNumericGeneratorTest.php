<?php

use PHPUnit\Framework\TestCase;
use Keygen\Generators\AlphaNumericGenerator;

/**
 * @covers AlphaNumericGenerator
 */
final class AlphaNumericGeneratorTest extends TestCase
{
	protected $generator;

	protected function setUp()
	{
		$this->generator = new AlphaNumericGenerator;
	}

	public function testKeyGeneration()
	{
		$key = $this->generator->generate();
		$this->assertRegExp('/^[a-zA-Z0-9]{16}$/', $key);
	}
}
