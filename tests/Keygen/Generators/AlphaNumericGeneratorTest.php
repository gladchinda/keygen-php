<?php

use PHPUnit\Framework\TestCase;
use Keygen\Generators\AlphaNumericGenerator;

/**
 * @coversDefaultClass AlphaNumericGenerator
 */
final class AlphaNumericGeneratorTest extends TestCase
{
	protected $generator;

	protected function setUp()
	{
		$this->generator = new AlphaNumericGenerator;
	}

	/**
	 * @covers ::keygen
	 * @covers ::initAlphaNumericChars
	 * @covers ::generateCharChunksByLength
	 * @covers ::generateAlphaNumericCharsByLength
	 */
	public function testKeyGeneration()
	{
		$key = $this->generator->generate();
		$this->assertRegExp('/^[a-zA-Z0-9]{16}$/', $key);
	}
}
