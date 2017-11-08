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
	 * @covers ::mutable
	 * @covers ::immutable
	 * @covers ::isMutable
	 * @covers ::isImmutable
	 * @covers ::resolvePropertiesMutability
	 */
	public function testMutableProperties()
	{
		$this->assertEquals([], $this->generator->mutable);
		$this->assertEquals([], $this->generator->immutable);

		$this->generator->mutable('prefix', 'mutate', 'length')->mutable(new \stdClass);
		$this->assertTrue($this->generator->isMutable('length'));
		$this->assertTrue($this->generator->isMutable('prefix'));
		$this->assertFalse($this->generator->isMutable('mutate'));
		$this->assertFalse($this->generator->isMutable('suffix'));
		$this->assertEquals(['prefix', 'length'], $this->generator->mutable);

		$this->generator->immutable('suffix', 'length', new \stdClass)->immutable('mutate');
		$this->assertTrue($this->generator->isImmutable('length'));
		$this->assertTrue($this->generator->isImmutable('suffix'));
		$this->assertFalse($this->generator->isImmutable('mutate'));
		$this->assertFalse($this->generator->isImmutable('prefix'));
		$this->assertEquals(['prefix'], $this->generator->mutable);
		$this->assertEquals(['suffix', 'length'], $this->generator->immutable);
		
		$this->generator->mutable([])->immutable([]);
		$this->assertEquals([], $this->generator->mutable);
		$this->assertEquals([], $this->generator->immutable);
	}

	/**
	 * @covers ::mutate
	 * @covers ::dontMutate
	 * @covers ::isLinked
	 * @covers ::linkBlocked
	 * @covers ::resolveObjectsMutationLinkage
	 * @covers ::propagatePropertyMutation
	 * @covers ::setPropertyForGenerator
	 */
	public function testObjectMutations()
	{
		$generator1 = (new NumericGenerator(10))->mutable('length');
		$generator2 = (new NumericGenerator(10))->mutable('length', 'prefix');

		$this->generator->mutate($generator2, $generator1);

		$this->assertEquals([$generator2, $generator1], $this->generator->mutate);
		$this->assertTrue($this->generator->isLinked($generator1));
		$this->assertTrue($generator2->isLinked($this->generator));

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

		$this->assertEquals([$generator1], $this->generator->mutate);
		$this->assertEquals([$generator2], $this->generator->dontMutate);
		$this->assertTrue($this->generator->linkBlocked($generator2));
		$this->assertTrue($generator2->linkBlocked($this->generator));

		$generator1->length(10);

		$this->assertEquals(20, $this->generator->length);
		$this->assertEquals(10, $generator1->length);
		$this->assertEquals(20, $generator2->length);

		$this->generator->mutable('length');
		$generator1->length(24);
		
		$this->assertEquals(24, $this->generator->length);
		$this->assertEquals(24, $generator1->length);
		
		$this->generator->mutate([])->dontMutate([]);
		$this->assertEquals([], $this->generator->mutate);
		$this->assertEquals([], $this->generator->dontMutate);
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
	 * @covers ::defaults
	 */
	public function testRestoreDefaultValue()
	{
		$this->generator->length(40)->affix('INTL-', '-REG');
		$this->assertEquals(40, $this->generator->length);

		$this->generator->defaults('length');
		$this->assertEquals(16, $this->generator->length);
		$this->assertEquals('INTL-', $this->generator->prefix);
		$this->assertEquals('-REG', $this->generator->suffix);

		$this->generator->defaults();
		$this->assertEquals(null, $this->generator->prefix);
		$this->assertEquals(null, $this->generator->suffix);
	}

	/**
	 * @covers ::__set
	 * @covers ::setPropertyForGenerator
	 * @covers ::tryChangePropValueViaMethod
	 * @covers ::defaults
	 */
	public function testSetGeneratorProperty()
	{
		$this->generator->length = false;
		$this->assertTrue($this->generator->randomLength);
		
		$this->generator->length = '10';
		$this->assertFalse($this->generator->randomLength);
		$this->assertEquals(10, $this->generator->length);

		$this->generator->prefix = 'IMG-';
		$this->assertEquals('IMG-', $this->generator->prefix);

		$this->generator->suffix = '.jpg';
		$this->assertEquals('.jpg', $this->generator->suffix);
		
		$this->generator->transformations = ['strrev', 'md5'];
		$this->assertEquals(['strrev', 'md5'], $this->generator->transformations);
		
		$this->generator->transformations = [];
		$this->assertEquals([], $this->generator->transformations);
		
		$this->generator->mutable = ['affix', 'length'];
		$this->assertEquals(['length'], $this->generator->mutable);
		
		$this->generator->mutable = ['prefix'];
		$this->assertEquals(['prefix'], $this->generator->mutable);
		
		$this->generator->mutable = [];
		$this->assertEquals([], $this->generator->mutable);
	}

	/**
	 * @covers ::__set
	 * @covers ::setPropertyForGenerator
	 * @expectedException Keygen\Exceptions\DirectModificationKeygenException
	 * @expectedExceptionMessage Trying to directly modify unknown property: Keygen\Generators\NumericGenerator::transformation.
	 */
	public function testSetUnknownProperty()
	{
		$this->generator->transformation = ['strrev', 'md5'];
	}

	/**
	 * @covers ::__set
	 * @covers ::setPropertyForGenerator
	 * @expectedException Keygen\Exceptions\DirectModificationKeygenException
	 * @expectedExceptionMessage Cannot directly modify property: Keygen\Generators\NumericGenerator::randomLength.
	 */
	public function testSetRestrictedProperty()
	{
		$this->generator->randomLength = true;
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
