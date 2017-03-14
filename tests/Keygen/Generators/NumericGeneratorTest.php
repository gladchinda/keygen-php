<?php

use PHPUnit\Framework\TestCase;
use Keygen\Generators\NumericGenerator;

/**
 * @covers NumericGenerator
 */
final class NumericGeneratorTest extends TestCase
{
	protected $generator;

	protected function setUp()
	{
		$this->generator = new NumericGenerator;
	}

	public function testNonZeroFirstAttribute()
	{
		$this->assertFalse($this->generator->nonZeroFirst);

		$this->generator->nzfirst();
		$this->assertTrue($this->generator->nonZeroFirst);
		
		$this->generator->anyfirst();
		$this->assertFalse($this->generator->nonZeroFirst);
	}

	public function testKeyGeneration()
	{
		$key = $this->generator->generate();
		$this->assertRegExp('/^\d{16}$/', $key);
	}
}
