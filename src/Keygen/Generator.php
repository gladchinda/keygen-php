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
use Keygen\Exceptions\TooMuchKeyIterationsKeygenException;

abstract class Generator extends AbstractGenerator
{
	/**
	 * List of excluded keys.
	 *
	 * @var array
	 */
	protected $exclusions = [];

	/**
	 * Maximum key generation iterations.
	 *
	 * @var int
	 */
	protected static $maxKeyIterations = 1000;

	/**
	 * Generates a random key.
	 *
	 * @param int $length
	 * @return string
	 */
	abstract protected function keygen($length);

	/**
	 * Gets the required length for the key generation.
	 *
	 * @return int
	 * @throws Keygen\Exceptions\LengthTooShortKeygenException
	 */
	protected function getKeygenLength()
	{
		$this->assertLengthIsSufficient();
		return $this->getAffixLengthOffset();
	}

	/**
	 * Determines inclusiveAffix property from the generation arguments.
	 *
	 * @param array $args
	 * @return array (Remaining arguments)
	 */
	protected function resolveInclusiveAffixFromGenerationArguments(array $args = null)
	{
		$args = (array) $args;

		$disableInclusiveAffix = array_shift($args);

		if (!is_bool($disableInclusiveAffix)) {
			array_unshift($args, $disableInclusiveAffix);
			$disableInclusiveAffix = false;
		}
		
		$this->inclusiveAffix = $disableInclusiveAffix ? false : $this->inclusiveAffix;

		return $args;
	}

	/**
	 * Compiles all key transformations based on generation arguments.
	 *
	 * @param array $args
	 * @return array (Key transformations)
	 */
	protected function resolveTransformationsFromGenerationArguments(array $args = null)
	{
		$transformations = array_values(Utils::flatten((array) $args));
		$transformations = array_filter($transformations, 'is_callable');

		return array_merge($this->transformations, $transformations);
	}

	/**
	 * Applies transformations to key and returns the transformed key.
	 *
	 * @param string $key
	 * @param array $transformations
	 * @return string (Transformed Key)
	 */
	protected function applyTransformationsToGeneratedKey($key, array $transformations = null)
	{
		$transformations = (array) $transformations;

		while ($transformation = current($transformations)) {
			if (is_callable($transformation)) {
				$key = $transformation($key);
			}
			next($transformations);
		}

		return $key;
	}

	/**
	 * Applies affixes to key and returns the affixed key.
	 *
	 * @param string $key
	 * @return string (Affixed Key)
	 */
	protected function applyAffixesToGeneratedKey($key)
	{
		return sprintf("%s%s%s", $this->prefix, $key, $this->suffix);
	}

	/**
	 * Removes all excluded keys and creates a fresh list.
	 *
	 * @param mixed $key
	 * @return $this
	 */
	protected function exclusions($key)
	{
		return $this->resetExclusions()->exclusion(func_get_args());
	}

	/**
	 * Adds excluded keys to the list.
	 *
	 * @param mixed $key
	 * @return $this
	 */
	protected function exclusion($key)
	{
		$exclusions = array_values(Utils::flatten(func_get_args()));
		$exclusions = array_merge($this->exclusions, $exclusions);

		$this->exclusions = array_unique($exclusions);

		return $this;
	}

	/**
	 * Removes all excluded keys from the list.
	 *
	 * @return $this
	 */
	protected function resetExclusions()
	{
		$this->exclusions = [];
		return $this;
	}

	/**
	 * Handles key generation logic and returns the generated key.
	 *
	 * @return string
	 * @throws Keygen\Exceptions\TooMuchKeyIterationsKeygenException
	 */
	public function generate()
	{
		return $this->overloadGenerateMethod('generate', func_get_args());
	}

	/**
	 * Returns the generated key after applying transformations and affixes.
	 *
	 * @param array $transformations
	 * @return string
	 */
	protected function getGeneratedKey($length, array $transformations = null)
	{
		$key = $this->applyTransformationsToGeneratedKey($this->keygen($length), $transformations);
		return $this->applyAffixesToGeneratedKey($key);
	}

	/**
	 * Finish up key generation logic and return the generated key or key collection.
	 *
	 * @param string|array $key (The generated key or key collection)
	 * @return string|array
	 */
	protected function finishKeyGeneration($key)
	{
		return $key;
	}

	/**
	 * Overloaded generate method for generating key collections.
	 *
	 * @param string $method
	 * @param array $args
	 * @return false|array (Collection of generated keys)
	 *
	 * @throws Keygen\Exceptions\TooMuchKeyIterationsKeygenException
	 */
	protected function overloadGenerateMethod($method, $args)
	{
		$inclusiveAffix = $this->inclusiveAffix;
		$_method = strtolower($method);

		$isGenerate = $_method === 'generate';
		$methodRegex = '/^generate_?(unique)?_?([1-9]\d+|[2-9])$/';

		if ($isGenerate || preg_match($methodRegex, $_method, $matches)) {

			if ($isGenerate) {
				$size = 1;
				$ensureUnique = null;
			} else {
				$size = intval($matches[2]);
				$ensureUnique = !!$matches[1];
			}

			$collection = [];
			$exclusions = (array) $this->exclusions;

			$args = $this->resolveInclusiveAffixFromGenerationArguments($args);
			$transforms = $this->resolveTransformationsFromGenerationArguments($args);
			$keyLength = $this->getKeygenLength();

			$iterations = 0;

			do {

				$key = $this->getGeneratedKey($keyLength, $transforms);

				if (in_array($key, $exclusions)) {

					$iterations++;

					if ($iterations == static::$maxKeyIterations) {
						throw new TooMuchKeyIterationsKeygenException('Maximum key generation iterations exceeded.');
					}

					continue;
				}

				$iterations = 0;

				if ($ensureUnique) {
					array_push($exclusions, $key);
				}

				array_push($collection, $key);

			} while (count($collection) != $size);

			$this->inclusiveAffix = $inclusiveAffix;
			$collection = $this->finishKeyGeneration($collection);

			return $isGenerate ? $collection[0] : $collection;

		}

		return false;
	}

	/**
	 * Checks if a given attribute can be overloaded at instantiation.
	 *
	 * @param string $attribute
	 * @return bool
	 */
	protected function hasOverloadedAttribute($attribute)
	{
		$attributes = array_values((array) static::getOverloadedAttributes());
		$attributes = array_map('strtolower', $attributes);

		return in_array(strtolower($attribute), $attributes) && method_exists($this, $attribute);
	}

	/**
	 * Checks if a given attribute can be overloaded at instantiation.
	 *
	 * @param string $attribute
	 * @return bool
	 */
	public function isOverloaded($attribute)
	{
		return $this->hasOverloadedAttribute($attribute);
	}

	/**
	 * List of the allowed overloaded attributes.
	 *
	 * @return array
	 */
	protected static function getOverloadedAttributes()
	{
		return [];
	}

	/**
	 * List of the allowed overloaded methods.
	 *
	 * @return array
	 */
	protected static function getOverloadedMethods()
	{
		$appends = ['exclusion', 'exclusions'];
		$methods = parent::getOverloadedMethods();

		return array_unique(array_merge($methods, $appends));
	}

	/**
	 * Overload the __call method
	 */
	public function __call($method, $args)
	{
		if ($overloader = $this->overloadGenerateMethod($method, $args)) {
			return $overloader;
		}

		return parent::__call($method, $args);
	}
}
