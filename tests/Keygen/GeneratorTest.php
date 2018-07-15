<?php

use Keygen\Generator;
use PHPUnit\Framework\TestCase;
use Keygen\Generators\NumericGenerator;

/**
 * @coversDefaultClass Generator
 * @covers GeneratorInterface
 */
final class GeneratorTest extends TestCase
{
	protected $generator;

	protected function setUp()
	{
		$this->generator = new NumericGenerator;
	}

	/**
	 * @covers ::generate
	 * @covers Keygen\Traits\KeyManipulation::getAdjustedKeyLength
	 */
	public function testGenerateMethod()
	{
		$ga = $this->generator->generate();
		$this->assertSame($this->generator->length, strlen($ga));

		$gb = $this->generator->prefix('TM-')->generate();
		$this->assertSame($this->generator->length, strlen($gb));
		$this->assertRegExp('/^TM-/', $gb);

		$gc = $this->generator->prefix('token::')->suffix('::123')->generate(true);
		$this->assertSame($this->generator->length + strlen($this->generator->prefix) + strlen($this->generator->suffix), strlen($gc));
		$this->assertRegExp('/^token::\d+?::123$/', $gc);

		$callable = function($s) { return strtoupper(substr($s, 8)); };

		$gd = $this->generator->generate('md5', $callable);
		$this->assertSame(24 + strlen($this->generator->prefix) + strlen($this->generator->suffix), strlen($gd));
		$this->assertRegExp('/^token::[A-F0-9]+?::123$/', $gd);

		$ge = $this->generator->prefix('')->suffix('')->generate(true, $callable);
		$this->assertSame($this->generator->length - 8, strlen($ge));
	}
}
