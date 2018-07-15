<?php

use Keygen\Keygen;
use PHPUnit\Framework\TestCase;
use Keygen\Generators\NumericGenerator;

/**
 * @coversDefaultClass Keygen\AbstractGenerator
 */
final class AbstractGeneratorTest extends TestCase
{
	protected $generator;

	protected function setUp()
	{
		$this->generator = new NumericGenerator;
	}

	/**
	 * @covers ::__get
	 * @covers ::__isset
	 */
	public function testGeneratorProperties()
	{
		$this->assertSame(16, $this->generator->length);
		$this->assertNull($this->generator->prefix);
		$this->assertNull($this->generator->suffix);
	}

	/**
	 * @covers ::__call
	 * @covers Keygen\Traits\KeyManipulation::__overloadMethods
	 * @covers Keygen\Traits\KeyManipulation::length
	 * @covers Keygen\Traits\IntegerCasting::intCast
	 */
	public function testLengthWithValidNumericArgument()
	{
		$this->generator->length(24);
		$this->assertSame(24, $this->generator->length);

		$this->generator->length('12');
		$this->assertSame(12, $this->generator->length);

		$this->generator->length(32.0);
		$this->assertSame(32, $this->generator->length);

		$this->generator->length('40.0');
		$this->assertSame(40, $this->generator->length);
	}

	/**
	 * @covers ::__call
	 * @covers Keygen\Traits\KeyManipulation::__overloadMethods
	 * @covers Keygen\Traits\KeyManipulation::length
	 * @covers Keygen\Traits\IntegerCasting::intCast
	 */
	public function testLengthWithFloatArgument()
	{
		$this->generator->length(24.4);
		$this->assertSame(24, $this->generator->length);
	}

	/**
	 * @covers ::__call
	 * @covers Keygen\Traits\KeyManipulation::__overloadMethods
	 * @covers Keygen\Traits\KeyManipulation::length
	 * @covers Keygen\Traits\IntegerCasting::intCast
	 * @expectedException \InvalidArgumentException
	 * @expectedExceptionMessage The given value cannot be converted to an integer.
	 */
	public function testLengthWithNonNumericArgument()
	{
		$this->generator->length([5]);
	}

	/**
	 * @covers ::__call
	 * @covers Keygen\Traits\KeyManipulation::__overloadMethods
	 * @covers Keygen\Traits\KeyManipulation::length
	 * @covers Keygen\Traits\IntegerCasting::intCast
	 * @expectedException \InvalidArgumentException
	 * @expectedExceptionMessage The given value cannot be converted to an integer.
	 */
	public function testLengthWithNonNumericStringArgument()
	{
		$this->generator->length('53.0bees');
	}

	/**
	 * @covers ::__call
	 * @covers Keygen\Traits\KeyManipulation::__overloadMethods
	 * @covers Keygen\Traits\KeyManipulation::prefix
	 * @covers Keygen\Traits\IntegerCasting::affix
	 */
	public function testPrefixWithValidArgument()
	{
		$this->generator->prefix(123);
		$this->assertSame('123', $this->generator->prefix);

		$this->generator->prefix('TM-');
		$this->assertSame('TM-', $this->generator->prefix);

		$this->generator->prefix(true);
		$this->assertSame('1', $this->generator->prefix);

		$this->generator->prefix(false);
		$this->assertSame('', $this->generator->prefix);
	}

	/**
	 * @covers ::__call
	 * @covers Keygen\Traits\KeyManipulation::__overloadMethods
	 * @covers Keygen\Traits\KeyManipulation::prefix
	 * @covers Keygen\Traits\IntegerCasting::affix
	 * @expectedException \InvalidArgumentException
	 * @expectedExceptionMessage The given prefix cannot be converted to a string.
	 */
	public function testPrefixWithNonScalarArgument()
	{
		$this->generator->prefix([123]);
	}

	/**
	 * @covers ::__call
	 * @covers Keygen\Traits\KeyManipulation::__overloadMethods
	 * @covers Keygen\Traits\KeyManipulation::suffix
	 * @covers Keygen\Traits\IntegerCasting::affix
	 */
	public function testSuffixWithValidArgument()
	{
		$this->generator->suffix(123);
		$this->assertSame('123', $this->generator->suffix);

		$this->generator->suffix('.img');
		$this->assertSame('.img', $this->generator->suffix);

		$this->generator->suffix(true);
		$this->assertSame('1', $this->generator->suffix);

		$this->generator->suffix(false);
		$this->assertSame('', $this->generator->suffix);
	}

	/**
	 * @covers ::__call
	 * @covers Keygen\Traits\KeyManipulation::__overloadMethods
	 * @covers Keygen\Traits\KeyManipulation::suffix
	 * @covers Keygen\Traits\IntegerCasting::affix
	 * @expectedException \InvalidArgumentException
	 * @expectedExceptionMessage The given suffix cannot be converted to a string.
	 */
	public function testSuffixWithNonScalarArgument()
	{
		$this->generator->suffix($this->generator);
	}

	/**
	 * @covers Keygen\Traits\MutableGenerator::mutable
	 * @covers Keygen\Traits\MutableGenerator::immutable
	 * @covers Keygen\Traits\FlattenArguments::flattenArguments
	 */
	public function testMutableProperties()
	{
		$this->assertEquals([], $this->generator->mutables);

		$this->generator->mutable('prefix', 'length')->mutable('prefix');
		$this->assertEquals(['length', 'prefix'], $this->generator->mutables);

		$this->generator->immutable('suffix', 'length')->immutable('suffix');
		$this->assertNotContains('length', $this->generator->mutables);
		$this->assertContains('prefix', $this->generator->mutables);
	}

	/**
	 * @covers Keygen\Traits\MutableGenerator::mutable
	 * @covers Keygen\Traits\FlattenArguments::flattenArguments
	 * @expectedException \InvalidArgumentException
	 * @expectedExceptionMessage Cannot add unknown property to mutables collection ('mutate').
	 */
	public function testInvalidMutableProperties()
	{
		$this->generator->mutable('prefix', 'mutate', 'length');
	}

	/**
	 * @covers Keygen\Traits\GeneratorMutation::mutate
	 * @covers Keygen\Traits\GeneratorMutation::dontMutate
	 * @covers Keygen\Traits\FlattenArguments::flattenArguments
	 * @covers Keygen\Traits\KeyManipulation::propagateMutation
	 */
	public function testObjectMutations()
	{
		$generator1 = (new NumericGenerator(10))->mutable('length');
		$generator2 = (new NumericGenerator(10))->mutable('length', 'prefix');

		$this->generator->mutate($generator2, $generator1);

		$this->assertEquals([$generator2, $generator1], $this->generator->mutates);

		$this->assertEquals(16, $this->generator->length);
		$this->assertEquals(10, $generator1->length);
		$this->assertEquals(10, $generator2->length);

		$this->generator->length(20)->prefix('IMG-');

		$this->assertEquals(20, $this->generator->length);
		$this->assertEquals(20, $generator1->length);
		$this->assertEquals(20, $generator2->length);

		$this->assertEquals('IMG-', $this->generator->prefix);
		$this->assertEquals(null, $generator1->prefix);
		$this->assertEquals('IMG-', $generator2->prefix);

		$this->generator->dontMutate($generator2);

		$this->assertContains($generator1, $this->generator->mutates);
		$this->assertNotContains($generator2, $this->generator->mutates);

		$generator1->length(10);

		$this->assertEquals(20, $this->generator->length);
		$this->assertEquals(10, $generator1->length);
		$this->assertEquals(20, $generator2->length);

		$this->generator->mutable('length');
		$generator1->length(24);

		// Root generator not mutable by sub-generators
		$this->assertEquals(20, $this->generator->length);

		$this->assertEquals(24, $generator1->length);
	}

	/**
	 * @covers Keygen\Traits\GeneratorMutation::mutate
	 * @covers Keygen\Traits\FlattenArguments::flattenArguments
	 * @expectedException \InvalidArgumentException
	 * @expectedExceptionMessage Mutable objects must be instances of Keygen\AbstractGenerator.
	 */
	public function testInvalidMutationObject()
	{
		$generator1 = (new NumericGenerator(10))->mutable('length');
		$generator2 = (new NumericGenerator(10))->mutable('length', 'prefix');

		$this->generator->mutate($generator2, $generator1, new \stdClass);
	}

	/**
	 * @covers ::__call
	 * @covers Keygen\Traits\KeyManipulation::__overloadMethods
	 * @expectedException \BadMethodCallException
	 * @expectedExceptionMessage Call to unknown method Keygen\Generators\NumericGenerator::degenerate()
	 */
	public function testUnknownMethodCall()
	{
		$this->generator->degenerate();
	}
}
