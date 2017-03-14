<?php

use Keygen\Keygen;
use Keygen\AbstractGenerator;
use PHPUnit\Framework\TestCase;
use Keygen\Generators\NumericGenerator;
use Keygen\Exceptions\InvalidTransformationKeygenException;

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

	public function testGeneratorProperties()
	{
		$this->assertTrue($this->generator->inclusiveAffix);
	}

	/**
	 * @covers ::length
	 * @covers ::getResolvedDefaultKeyLength
	 */
	public function testLengthWithBooleanArgument()
	{
		$this->generator->length(true);
		$this->assertSame(16, $this->generator->length);
		$this->assertFalse($this->generator->randomLength);

		$this->generator->length(24);
		$this->generator->length(false);
		$this->assertTrue($this->generator->randomLength);

		$this->generator->length(true);
		$this->assertSame(24, $this->generator->length);
		$this->assertFalse($this->generator->randomLength);
	}

	/**
	 * @covers ::length
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
	 * @covers ::length
	 * @expectedException Keygen\Exceptions\InvalidLengthKeygenException
	 * @expectedExceptionMessage Invalid length.
	 */
	public function testLengthWithFloatArgument()
	{
		$this->generator->length(24.4);
	}

	/**
	 * @covers ::length
	 * @expectedException Keygen\Exceptions\InvalidLengthKeygenException
	 * @expectedExceptionMessage Invalid length.
	 */
	public function testLengthWithNonNumericArgument()
	{
		$this->generator->length([5]);
	}

	/**
	 * @covers ::length
	 * @expectedException Keygen\Exceptions\InvalidLengthKeygenException
	 * @expectedExceptionMessage Invalid length.
	 */
	public function testLengthWithNonNumericStringArgument()
	{
		$this->generator->length('53.0bees');
	}

	/**
	 * @covers ::affix
	 * @covers ::registerAffix
	 */
	public function testAffixWithValidArgument()
	{
		$this->assertNull($this->generator->prefix);
		$this->assertNull($this->generator->suffix);

		$this->generator->affix(123, '.img');
		$this->assertSame('123', $this->generator->prefix);
		$this->assertSame('.img', $this->generator->suffix);

		$this->generator->affix(null, true);
		$this->assertSame('123', $this->generator->prefix);
		$this->assertSame('.img', $this->generator->suffix);

		$this->generator->affix(false, false);
		$this->assertNull($this->generator->prefix);
		$this->assertNull($this->generator->suffix);
	}

	/**
	 * @covers ::affix
	 * @covers ::registerAffix
	 * @expectedException Keygen\Exceptions\InvalidAffixKeygenException
	 * @expectedExceptionMessage Prefix cannot be converted to a string.
	 */
	public function testAffixWithNonScalarPrefixArgument()
	{
		$this->generator->affix([123], '.img');
	}

	/**
	 * @covers ::affix
	 * @covers ::registerAffix
	 * @expectedException Keygen\Exceptions\InvalidAffixKeygenException
	 * @expectedExceptionMessage Suffix cannot be converted to a string.
	 */
	public function testAffixWithNonScalarSuffixArgument()
	{
		$this->generator->affix(123, $this->generator);
	}

	/**
	 * @covers ::prefix
	 */
	public function testPrefixWithValidArgument()
	{
		$this->generator->prefix(123);
		$this->assertSame('123', $this->generator->prefix);

		$this->generator->prefix(null);
		$this->assertSame('123', $this->generator->prefix);

		$this->generator->prefix('TM-');
		$this->assertSame('TM-', $this->generator->prefix);

		$this->generator->prefix(true);
		$this->assertSame('TM-', $this->generator->prefix);

		$this->generator->prefix(false);
		$this->assertNull($this->generator->prefix);
	}

	/**
	 * @covers ::registerAffix
	 * @expectedException Keygen\Exceptions\InvalidAffixKeygenException
	 * @expectedExceptionMessage Prefix cannot be converted to a string.
	 */
	public function testPrefixWithNonScalarArgument()
	{
		$this->generator->prefix([123]);
	}

	/**
	 * @covers ::suffix
	 */
	public function testSuffixWithValidArgument()
	{
		$this->generator->suffix(123);
		$this->assertSame('123', $this->generator->suffix);

		$this->generator->suffix(null);
		$this->assertSame('123', $this->generator->suffix);

		$this->generator->suffix('.img');
		$this->assertSame('.img', $this->generator->suffix);

		$this->generator->suffix(true);
		$this->assertSame('.img', $this->generator->suffix);

		$this->generator->suffix(false);
		$this->assertNull($this->generator->suffix);
	}

	/**
	 * @covers ::registerAffix
	 * @expectedException Keygen\Exceptions\InvalidAffixKeygenException
	 * @expectedExceptionMessage Suffix cannot be converted to a string.
	 */
	public function testSuffixWithNonScalarArgument()
	{
		$this->generator->suffix($this->generator);
	}

	/**
	 * @covers ::transformation
	 * @covers ::transformations
	 * @covers ::resetTransformations
	 */
	public function testKeyTransformations()
	{
		$this->assertEmpty($this->generator->transformations);
		$this->assertEquals([], $this->generator->transformations);

		$substr = function($s) { return substr($s, 3); };
		$transforms = ['md5', $substr, 'strtoupper'];

		$this->generator->transformation($substr);
		$this->assertEquals([$substr], $this->generator->transformations);

		$this->generator->transformation($transforms);
		$this->assertEquals([$substr, 'md5', $substr, 'strtoupper'], $this->generator->transformations);

		$this->generator->transformations($transforms);
		$this->assertEquals(['md5', $substr, 'strtoupper'], $this->generator->transformations);

		$this->generator->transformations([]);
		$this->assertEmpty($this->generator->transformations);
		$this->assertEquals([], $this->generator->transformations);
	}

	/**
	 * @covers ::transformation
	 * @covers ::transformations
	 * @expectedException Keygen\Exceptions\InvalidTransformationKeygenException
	 * @expectedExceptionMessage Only callables are allowed as transformations.
	 */
	public function testNonCallableTransformations()
	{
		$uppercase = 'strtoupper';
		$substr = function($s) { return substr($s, 3); };

		$transforms = ['md5', $this->generator, $substr, new \stdClass, $uppercase];

		$this->generator->transformations($transforms);
	}

	/**
	 * @covers ::generate
	 * @expectedException Keygen\Exceptions\KeyCannotBeGeneratedKeygenException
	 * @expectedExceptionMessage Cannot generate key directly from Keygen\Keygen instance.
	 */
	public function testCallToGenerateMethod()
	{
		(new Keygen)->generate();
	}

	/**
	 * @covers ::__get
	 * @expectedException Keygen\Exceptions\UnknownPropertyAccessKeygenException
	 * @expectedExceptionMessage Trying to access unknown property: Keygen\Generators\NumericGenerator::degenerate.
	 */
	public function testUnknownPropertyAccess()
	{
		$this->generator->degenerate;
	}

	/**
	 * @covers ::__call
	 * @expectedException Keygen\Exceptions\UnknownMethodCallKeygenException
	 * @expectedExceptionMessage Call to unknown method: Keygen\Generators\NumericGenerator::degenerate().
	 */
	public function testUnknownMethodCall()
	{
		$this->generator->degenerate();
	}
}
