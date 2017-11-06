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

class Keygen extends AbstractGenerator
{
	/**
	 * Map of available generator aliases.
	 * 
	 * @var array
	 */
	protected static $generatorAliases = [
		TokenGenerator::class 			=> 'token',
		NumericGenerator::class 		=> 'numeric',
		RandomByteGenerator::class 		=> ['bytes', 'randomByte'],
		AlphaNumericGenerator::class 	=> ['alphanum', 'alphaNumeric'],
	];

	/**
	 * List of all generator aliases.
	 * 
	 * @return array
	 */
	protected static function getAllGeneratorAliases()
	{
		$aliases = array_values(static::$generatorAliases);
		$aliases = array_map('strtolower', Utils::flattenArguments($aliases));

		return array_unique($aliases);
	}

	/**
	 * Checks if a generator alias exists.
	 *
	 * @param string $alias
	 * @return bool
	 */
	protected static function generatorAliasExists($alias)
	{
		return in_array(strtolower($alias), static::getAllGeneratorAliases());
	}

	/**
	 * Ensures that a generator alias exists.
	 * 
	 * @param string $alias
	 * @throws Keygen\Exceptions\InvalidGeneratorKeygenException
	 */
	protected static function assertGeneratorAliasExists($alias)
	{
		if (! static::generatorAliasExists($alias)) {
			throw new InvalidGeneratorKeygenException("Unknown generator alias: {$alias}.");
		}
	}

	/**
	 * Gets the generator class mapped to an alias.
	 * 
	 * @param string $alias
	 * @return string
	 * 
	 * @throws Keygen\Exceptions\InvalidGeneratorKeygenException
	 */
	protected static function getGeneratorFromAlias($alias)
	{
		$generator = null;
		static::assertGeneratorAliasExists($alias);

		$isValidGenerator = function ($generator) {
			return class_exists($generator) && is_subclass_of($generator, Generator::class);
		};

		foreach (static::$generatorAliases as $g => $aliases) {

			if ( in_array(strtolower($alias), array_map('strtolower', (array) $aliases)) ) {
				$generator = $isValidGenerator($g) ? $g : null;
				break;
			}
		}

		if (! $generator) {
			throw new InvalidGeneratorKeygenException("Alias({$alias}) refers to invalid generator.");
		}

		return $generator;
	}

	/**
	 * Creates instance of generator from alias.
	 * 
	 * @param string $alias
	 * @param int|bool $length
	 * @return Keygen\Generator
	 * 
	 * @throws Keygen\Exceptions\InvalidGeneratorKeygenException
	 */
	protected function newGeneratorFromAlias($alias, $length = null)
	{
		$generator = static::getGeneratorFromAlias($alias);

		return (new $generator)->mutate($this)
			->length($length ?: $this->length)
			->prefix($this->prefix)
			->suffix($this->suffix);
	}

	/**
	 * Overloaded method for creating generator instances.
	 *
	 * @param string $method
	 * @param array $args
	 * @return false|Keygen\Generator
	 */
	protected function overloadGeneratorMethod($method, $args)
	{
		$_args = $args;

		if (static::generatorAliasExists($method)) {
			array_unshift($_args, $method);
			return call_user_func_array([$this, 'newGeneratorFromAlias'], $_args);
		}

		$method = strtolower($method);

		$aliases = static::getAllGeneratorAliases();

		$methodRegex = sprintf("(%s)([1-9]\d*|random)?", join('|', $aliases));
		$methodRegex = '/'. $methodRegex .'$/';

		if (preg_match($methodRegex, $method, $matches)) {

			$length = $matches[2];
			$length = $length ? (($length === 'random') ? false : intval($length)) : null;

			$attribute = preg_replace($methodRegex, '', $method);

			$generator = $this->newGeneratorFromAlias($matches[1], $length);

			if (empty($attribute)) {
				return $generator;
			}

			if ($generator->isOverloaded($attribute)) {
				return $generator->{$attribute}();
			}
		}

		return false;
	}

	/**
	 * Overload the __get method
	 */
	public function __get($prop)
	{
		if ($overloader = $this->overloadGeneratorMethod($prop, [])) {
			return $overloader;
		}

		return parent::__get($prop);
	}

	/**
	 * Overload the __call method
	 */
	public function __call($method, $args)
	{
		if ($overloader = $this->overloadGeneratorMethod($method, $args)) {
			return $overloader;
		}

		return parent::__call($method, $args);
	}
}
