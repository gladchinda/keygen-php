<?php

use PHPUnit\Framework\TestCase;
use Keygen\Generators\NumericGenerator;

/**
 * @coversDefaultClass NumericGenerator
 */
final class NumericGeneratorTest extends TestCase
{
	protected $generator;

	protected function setUp()
	{
		$this->generator = new NumericGenerator;
	}

	/**
	 * @covers ::nzfirst
	 * @covers ::anyfirst
	 */
	public function testNonZeroFirstAttribute()
	{
		$this->assertFalse($this->generator->nonZeroFirst);

		$this->generator->nzfirst();
		$this->assertTrue($this->generator->nonZeroFirst);
		
		$this->generator->anyfirst();
		$this->assertFalse($this->generator->nonZeroFirst);
	}

	/**
	 * @covers ::keygen
	 * @covers ::generateNumericChars
	 */
	public function testKeyGeneration()
	{
		$key = $this->generator->generate();
		$this->assertRegExp('/^\d{16}$/', $key);
	}
}
