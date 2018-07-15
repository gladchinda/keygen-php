<?php

use Keygen\GeneratorFactory;
use PHPUnit\Framework\TestCase;
use Keygen\Generators\TokenGenerator;
use Keygen\Generators\NumericGenerator;
use Keygen\Generators\RandomByteGenerator;
use Keygen\Generators\AlphaNumericGenerator;

/**
 * @coversDefaultClass Keygen\GeneratorFactory
 */
final class GeneratorFactoryTest extends TestCase
{
    /**
     * @covers ::getAllGeneratorAliases
     * @covers ::generatorAliasExists
     * @covers ::assertGeneratorAliasExists
     * @expectedException Keygen\Exceptions\InvalidGeneratorKeygenException
     * @expectedExceptionMessage Unknown generator alias: alpha.
     */
    public function testGeneratorAliases()
    {
        $aliases = GeneratorFactory::getAllGeneratorAliases();

        $this->assertCount(7, $aliases);
        $this->assertTrue(GeneratorFactory::generatorAliasExists('token'));
        $this->assertTrue(GeneratorFactory::generatorAliasExists('numeric'));
        $this->assertTrue(GeneratorFactory::generatorAliasExists('digits'));
        $this->assertTrue(GeneratorFactory::generatorAliasExists('bytes'));
        $this->assertTrue(GeneratorFactory::generatorAliasExists('alphanum'));
        $this->assertTrue(GeneratorFactory::generatorAliasExists('alphanumeric'));
        $this->assertTrue(GeneratorFactory::generatorAliasExists('alnum'));
        $this->assertFalse(GeneratorFactory::generatorAliasExists('alpha'));

        GeneratorFactory::assertGeneratorAliasExists('alphanum');
        GeneratorFactory::assertGeneratorAliasExists('alpha');
    }

    /**
     * @covers ::getGeneratorFromAlias
     * @covers ::generator
     * @covers ::isValidGenerator
     * @covers ::assertGeneratorAliasExists
     * @expectedException Keygen\Exceptions\InvalidGeneratorKeygenException
     * @expectedExceptionMessage Unknown generator alias: alpha.
     */
    public function testCreateGenerators()
    {
        $this->assertTrue(GeneratorFactory::isValidGenerator(NumericGenerator::class));
        $this->assertTrue(GeneratorFactory::isValidGenerator(GeneratorFactory::generator('token')));
        $this->assertFalse(GeneratorFactory::isValidGenerator(60));
        $this->assertFalse(GeneratorFactory::isValidGenerator('NotAClass'));
        $this->assertFalse(GeneratorFactory::isValidGenerator(new \stdClass));

        $this->assertTrue(GeneratorFactory::generator('token') instanceof TokenGenerator);
        $this->assertTrue(GeneratorFactory::generator('digits') instanceof NumericGenerator);
        $this->assertTrue(GeneratorFactory::generator('numeric') instanceof NumericGenerator);
        $this->assertTrue(GeneratorFactory::generator('bytes') instanceof RandomByteGenerator);
        $this->assertTrue(GeneratorFactory::generator('alnum') instanceof AlphaNumericGenerator);
        $this->assertTrue(GeneratorFactory::generator('alphanum') instanceof AlphaNumericGenerator);
        $this->assertTrue(GeneratorFactory::generator('alphanumeric') instanceof AlphaNumericGenerator);

        $generator = GeneratorFactory::generator('alpha');
    }
}