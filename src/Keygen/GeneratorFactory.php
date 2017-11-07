<?php

/*
 * This file is part of the Keygen package.
 *
 * (c) Glad Chinda <gladxeqs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Keygen;

use Keygen\Support\Utils;
use Keygen\Generators\TokenGenerator;
use Keygen\Generators\NumericGenerator;
use Keygen\Generators\RandomByteGenerator;
use Keygen\Generators\AlphaNumericGenerator;
use Keygen\Exceptions\InvalidGeneratorKeygenException;

class GeneratorFactory
{
	/**
	 * Map of available generator aliases.
	 *
	 * @var array
	 */
	protected static $generatorAliases = [
		TokenGenerator::class 			=> 'token',
		NumericGenerator::class 		=> ['numeric', 'digits'],
		RandomByteGenerator::class 		=> 'bytes',
		AlphaNumericGenerator::class 	=> ['alphanum', 'alphanumeric', 'alnum'],
	];

	/**
	 * List of all generator aliases.
	 *
	 * @return array
	 */
	public static function getAllGeneratorAliases()
	{
		$aliases = array_values(static::$generatorAliases);
		$aliases = array_map('strtolower', Utils::flatten($aliases));

		return array_unique($aliases);
	}

	/**
	 * Checks if a generator alias exists.
	 *
	 * @param string $alias
	 * @return bool
	 */
	public static function generatorAliasExists($alias)
	{
		return in_array(strtolower($alias), static::getAllGeneratorAliases());
	}

	/**
	 * Ensures that a generator alias exists.
	 *
	 * @param string $alias
	 * @throws Keygen\Exceptions\InvalidGeneratorKeygenException
	 */
	public static function assertGeneratorAliasExists($alias)
	{
		if (! static::generatorAliasExists($alias)) {
			throw new InvalidGeneratorKeygenException("Unknown generator alias: {$alias}.");
		}
	}

	/**
	 * Checks if the given generator is a Generator object or class.
	 *
	 * @param mixed $generator
	 * @return bool
	 */
	public static function isValidGenerator($generator)
	{
		if (is_string($generator)) {
			return class_exists($generator) && is_subclass_of($generator, Generator::class);
		}

		if (is_object($generator)) {
			return $generator instanceof Generator;
		}

		return false;
	}

	/**
	 * Gets the generator class mapped to an alias.
	 *
	 * @param string $alias
	 * @return Keygen\Generator
	 *
	 * @throws Keygen\Exceptions\InvalidGeneratorKeygenException
	 */
	public static function getGeneratorFromAlias($alias)
	{
		$generator = null;
		static::assertGeneratorAliasExists($alias);

		foreach (static::$generatorAliases as $g => $aliases) {
			if ( in_array(strtolower($alias), array_map('strtolower', (array) $aliases)) ) {
				$generator = static::isValidGenerator($g) ? $g : null;
				break;
			}
		}

		if (! $generator) {
			throw new InvalidGeneratorKeygenException("Alias({$alias}) refers to invalid generator.");
		}

		return new $generator;
	}

	/**
	 * Returns a generator instance for the given generator.
	 *
	 * @param mixed $generator
	 * @return Keygen\Generator
	 *
	 * @throws Keygen\Exceptions\InvalidGeneratorKeygenException
	 */
	public static function generator($generator)
	{
		return static::getGeneratorFromAlias($generator);
	}
}
