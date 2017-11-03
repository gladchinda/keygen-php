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
use Keygen\Exceptions\InvalidAffixKeygenException;
use Keygen\Exceptions\InvalidLengthKeygenException;
use Keygen\Exceptions\LengthTooShortKeygenException;
use Keygen\Exceptions\UnknownMethodCallKeygenException;
use Keygen\Exceptions\KeyCannotBeGeneratedKeygenException;
use Keygen\Exceptions\InvalidTransformationKeygenException;
use Keygen\Exceptions\UnknownPropertyAccessKeygenException;

abstract class AbstractGenerator implements GeneratorInterface
{
	/**
	 * Generated key length.
	 *
	 * @var int
	 */
	protected $length;

	/**
	 * Affix prepended to generated string.
	 *
	 * @var string
	 */
	protected $prefix;

	/**
	 * Affix appended to generated string.
	 *
	 * @var string
	 */
	protected $suffix;

	/**
	 * Key length should include affix length.
	 *
	 * @var bool
	 */
	protected $inclusiveAffix = true;

	/**
	 * Should generate random length keys.
	 *
	 * @var bool
	 */
	protected $randomLength = false;

	/**
	 * Queue of registered key transformations.
	 *
	 * @var array
	 */
	protected $transformations = [];

	/**
	 * Collection of linked generator objects watching for property mutations.
	 *
	 * @var array
	 */
	protected $mutates = [];

	/**
	 * Collection of generator objects blacklisted from watching for property mutations.
	 *
	 * @var array
	 */
	protected $dontMutates = [];

	/**
	 * Collection of mutable generator properties.
	 *
	 * @var array
	 */
	protected $mutables = [];

	/**
	 * Collection of immutable generator properties.
	 *
	 * @var array
	 */
	protected $immutables = [];

	/**
	 * The base default key length.
	 *
	 * @var int
	 */
	protected static $defaultKeyLength = 16;

	/**
	 * List of available affixes.
	 *
	 * @var array
	 */
	protected static $affixes = ['prefix', 'suffix'];

	/**
	 * Creates a new KeyGenerator instance.
	 *
	 * @param int|bool $length
	 */
	public function __construct($length = null)
	{
		$this->length($length ?: $this->getResolvedDefaultKeyLength());
	}

	/**
	 * Sets the generated key length.
	 * May also enable or disable random key lengths.
	 *
	 * @param int|bool $length
	 * @param bool $propagate
	 * @return $this
	 *
	 * @throws Keygen\Exceptions\InvalidLengthKeygenException
	 */
	protected function length($length, $propagate = true)
	{
		$boolLength = is_bool($length);
		$floatRegex = '/^\d+(.0+)?$/';

		if ( !$boolLength && !(is_numeric($length) && preg_match($floatRegex, strval($length))) ) {
			throw new InvalidLengthKeygenException("Invalid length.");
		}

		$randomLength = $boolLength && !$length;
		$defaultLength = $this->getResolvedDefaultKeyLength();

		$length = $boolLength ? $defaultLength : (intval($length) ?: $defaultLength);

		if ($length < 1) {
			throw new InvalidLengthKeygenException("Length cannot be less than 1.");
		}

		$this->length = $length;
		$this->randomLength = $randomLength;

		return $this->propagatePropertyMutation('length', !$randomLength && $propagate);
	}

	/**
	 * Enables or disables affix length inclusion.
	 *
	 * @param bool $flag
	 * @return $this
	 */
	protected function inclusiveAffix($flag)
	{
		$this->inclusiveAffix = !!!is_bool($flag) ?: $flag;
		return $this;
	}

	/**
	 * Returns the active key length of the instance if set.
	 * Otherwise, returns the default key length.
	 *
	 * @return int
	 */
	protected function getResolvedDefaultKeyLength()
	{
		return intval($this->length ?: static::$defaultKeyLength);
	}

	/**
	 * Affixes string to generated keys.
	 *
	 * @param string $affix (Either 'prefix' or 'suffix')
	 * @param string|bool $value
	 * @param bool $propagate
	 * @return $this
	 *
	 * @throws Keygen\Exceptions\InvalidAffixKeygenException
	 */
	protected function registerAffix($affix, $value = null, $propagate = true)
	{
		$affix = strtolower($affix);

		if (in_array($affix, static::$affixes)) {

			$isBool = is_bool($value);

			if (! ($isBool || is_null($value) || is_scalar($value)) ) {
				$problem = sprintf("%s cannot be converted to a string.", ucfirst($affix));
				throw new InvalidAffixKeygenException($problem);
			}

			$this->{$affix} = $isBool && !$value ? null : (!$isBool && $value ? strval($value) : $this->{$affix});
		}

		return $this->propagatePropertyMutation($affix, $propagate);
	}

	/**
	 * Attaches affixes to generated keys.
	 *
	 * @param string|bool $prefix
	 * @param string|bool $suffix
	 * @return $this
	 *
	 * @throws Keygen\Exceptions\InvalidAffixKeygenException
	 */
	protected function affix($prefix = null, $suffix = null)
	{
		return $this->registerAffix('prefix', $prefix)
					->registerAffix('suffix', $suffix);
	}

	/**
	 * Prepends string to generated keys.
	 *
	 * @param string|bool $prefix
	 * @param bool $propagate
	 * @return $this
	 *
	 * @throws Keygen\Exceptions\InvalidAffixKeygenException
	 */
	protected function prefix($prefix, $propagate = true)
	{
		return $this->registerAffix('prefix', $prefix, $propagate);
	}

	/**
	 * Appends string to generated keys.
	 *
	 * @param string|bool $suffix
	 * @param bool $propagate
	 * @return $this
	 *
	 * @throws Keygen\Exceptions\InvalidAffixKeygenException
	 */
	protected function suffix($suffix, $propagate = true)
	{
		return $this->registerAffix('suffix', $suffix, $propagate);
	}

	/**
	 * Gets the affix length of the generator.
	 *
	 * @return int
	 */
	protected function getAffixLength()
	{
		return intval(strlen($this->prefix) + strlen($this->suffix));
	}

	/**
	 * Gets the key length based on generator properties.
	 *
	 * @return int
	 */
	protected function getLengthProperty()
	{
		if ($this->randomLength) {
			$length = mt_rand(1, 60) + ($this->inclusiveAffix ? $this->getAffixLength() : 0);
			return $length;
		}

		return $this->length;
	}

	/**
	 * Gets the key length less the affix length.
	 *
	 * @return int
	 */
	protected function getAdjustedKeyLength()
	{
		return $this->getLengthProperty() - $this->getAffixLength();
	}

	/**
	 * Gets the key length offset based on affix inclusion.
	 *
	 * @return int
	 */
	protected function getAffixLengthOffset()
	{
		return $this->inclusiveAffix ? $this->getAdjustedKeyLength() : $this->getLengthProperty();
	}

	/**
	 * Asserts that key length can contain affixes.
	 *
	 * @throws Keygen\Exceptions\LengthTooShortKeygenException
	 */
	protected function assertLengthIsSufficient()
	{
		if ($this->getAffixLengthOffset() < 1) {
			throw new LengthTooShortKeygenException('Length too short to contain affixes.');
		}
	}

	/**
	 * Removes all transformations and creates a fresh queue.
	 *
	 * @param mixed $transformation
	 * @return $this
	 *
	 * @throws Keygen\Exceptions\InvalidTransformationKeygenException
	 */
	protected function transformations($tranformation)
	{
		return $this->resetTransformations()->transformation(func_get_args());
	}

	/**
	 * Appends transformations to the queue.
	 *
	 * @param mixed $transformation
	 * @return $this
	 *
	 * @throws Keygen\Exceptions\InvalidTransformationKeygenException
	 */
	protected function transformation($tranformation)
	{
		$transformations = array_values(Utils::flattenArguments(func_get_args()));
		$countFromArgs = count($transformations);

		$transformations = array_filter($transformations, 'is_callable');

		if ($countFromArgs !== count($transformations)) {
			throw new InvalidTransformationKeygenException("Only callables are allowed as transformations.");
		}

		$this->transformations = array_merge($this->transformations, $transformations);

		return $this;
	}

	/**
	 * Removes all transformations from the queue.
	 *
	 * @return $this
	 */
	protected function resetTransformations()
	{
		$this->transformations = [];
		return $this;
	}

	/**
	 * Adds or removes properties from the mutables and immutables collection where necessary.
	 *
	 * @param array $props
	 * @param bool $mutable
	 * @return $this
	 */
	protected function resolvePropertiesMutability(array $props = [], $mutable = true)
	{
		$props = array_filter(array_values($props), 'is_string');

		$props = array_unique(array_map('strtolower', $props));
		$mutable = !!!is_bool($mutable) ?: $mutable;

		foreach ($props as $prop) {

			$isMutable = $this->isMutable($prop);
			$isImmutable = $this->isImmutable($prop);

			if (!property_exists($this, $prop)) {
				continue;
			}

			if ($mutable) {
				$this->mutables = array_merge($this->mutables, $isMutable ? [] : [$prop]);
				$this->immutables = array_diff($this->immutables, $isImmutable ? [$prop] : []);
			}

			else {
				$this->mutables = array_diff($this->mutables, $isMutable ? [$prop] : []);
				$this->immutables = array_merge($this->immutables, $isImmutable ? [] : [$prop]);
			}
		}

		$this->mutables = array_values($this->mutables);
		$this->immutables = array_values($this->immutables);

		return $this;
	}

	/**
	 * Add mutable properties to the mutables collection.
	 *
	 * @param mixed $props
	 * @return $this
	 */
	protected function mutable($props)
	{
		$props = Utils::flattenArguments(func_get_args());
		return $this->resolvePropertiesMutability($props, true);
	}

	/**
	 * Add immutable properties to the immutables collection.
	 *
	 * @param mixed $props
	 * @return $this
	 */
	protected function immutable($props)
	{
		$props = Utils::flattenArguments(func_get_args());
		return $this->resolvePropertiesMutability($props, false);
	}

	/**
	 * Checks if property is marked as mutable.
	 *
	 * @param string $prop
	 * @return bool
	 */
	protected function isMutable($prop)
	{
		return in_array($prop, $this->mutables);
	}

	/**
	 * Checks if property is marked as immutable.
	 *
	 * @param string $prop
	 * @return bool
	 */
	protected function isImmutable($prop)
	{
		return in_array($prop, $this->immutables);
	}

	/**
	 * Adds or removes generator objects from the mutates and dontMutates collection where necessary.
	 *
	 * @param array $objects
	 * @param bool $link
	 * @return $this
	 */
	protected function resolveObjectsMutationLinkage(array $objects = [], $link = true)
	{
		$objects = array_filter(array_values($objects), function($obj) {
			return $obj instanceof AbstractGenerator;
		});

		$link = !!!is_bool($link) ?: $link;

		foreach ($objects as $obj) {

			$isLinked = $this->isLinked($obj);
			$notLinked = $this->linkBlocked($obj);

			if ($link) {
				if ($notLinked) {
					$this->dontMutates = array_filter($this->dontMutates, function($object) use ($obj) {
						return $object !== $obj;
					});
				}

				$this->mutates = array_merge($this->mutates, $isLinked ? [] : [$obj]);
			}

			else {
				if ($isLinked) {
					$this->mutates = array_filter($this->mutates, function($object) use ($obj) {
						return $object !== $obj;
					});
				}
				
				$this->dontMutates = array_merge($this->dontMutates, $notLinked ? [] : [$obj]);
			}

			$this->mutates = array_values($this->mutates);
			$this->dontMutates = array_values($this->dontMutates);

			$transcendLinkage = $link && !$obj->isLinked($this);
			$transcendNoLinkage = !($link || $obj->linkBlocked($this));

			if ($transcendLinkage || $transcendNoLinkage) {
				$obj->resolveObjectsMutationLinkage([$this], $link);
			}
		}

		return $this;
	}

	/**
	 * Add linked generator objects to the mutates collection.
	 *
	 * @param mixed $objects
	 * @return $this
	 */
	protected function mutate($objects)
	{
		$objects = Utils::flattenArguments(func_get_args());
		return $this->resolveObjectsMutationLinkage($objects, true);
	}

	/**
	 * Add blacklisted generator objects to the dontMutates collection.
	 *
	 * @param mixed $objects
	 * @return $this
	 */
	protected function dontMutate($objects)
	{
		$objects = Utils::flattenArguments(func_get_args());
		return $this->resolveObjectsMutationLinkage($objects, false);
	}

	/**
	 * Checks if object is linked to watch for mutations.
	 *
	 * @param Keygen\AbstractGenerator $object
	 * @return bool
	 */
	protected function isLinked(AbstractGenerator $object)
	{
		return in_array($object, $this->mutates, true);
	}

	/**
	 * Checks if object is blacklisted from watching for mutations.
	 *
	 * @param Keygen\AbstractGenerator $object
	 * @return bool
	 */
	protected function linkBlocked(AbstractGenerator $object)
	{
		return in_array($object, $this->dontMutates, true);
	}

	/**
	 * Propagates property mutation to linked mutable generators.
	 *
	 * @param string $prop
	 * @param bool $propagate
	 * @return $this
	 */
	protected function propagatePropertyMutation($prop, $propagate = true)
	{
		$propagate = !!!is_bool($propagate) ?: $propagate;

		if ($propagate) {
			foreach ($this->mutates as $obj) {
				if ($obj->isMutable($prop) && ( $obj->{$prop} !== $this->{$prop} )) {
					$obj->setPropertyForGenerator($prop, $this->{$prop});
				}
			}
		}

		return $this;
	}

	/**
	 * Lock the generate method for implementation by child classes.
	 *
	 * @throws Keygen\Exceptions\KeyCannotBeGeneratedKeygenException
	 */
	public function generate()
	{
		$problem = sprintf("Cannot generate key directly from %s instance.", static::class);
		throw new KeyCannotBeGeneratedKeygenException($problem);
	}

	/**
	 * Checks if instance can overload the given method.
	 *
	 * @param string $method
	 * @return bool
	 */
	protected function hasOverloadedMethod($method)
	{
		$methods = array_values((array) static::getOverloadedMethods());
		$methods = array_map('strtolower', $methods);

		return in_array(strtolower($method), $methods) && method_exists($this, $method);
	}

	/**
	 * List of the allowed overloaded methods.
	 *
	 * @return array
	 */
	protected static function getOverloadedMethods()
	{
		return [
			'length', 'affix', 'prefix', 'suffix',
			'inclusiveAffix', 'transformation', 'transformations',
			'mutable', 'immutable', 'isMutable', 'isImmutable',
			'mutate', 'dontMutate', 'isLinked', 'linkBlocked',
		];
	}

	/**
	 * Sets the value of a property.
	 *
	 * @param string $prop
	 * @param mixed $value
	 * @return $this
	 */
	protected function setPropertyForGenerator($prop, $value)
	{
		$args = func_get_args();
		array_shift($args);

		if (method_exists($this, $method = "set{$prop}Property")) {
			call_user_func_array([$this, $method], $args);
		}

		elseif (method_exists($this, $prop)) {
			call_user_func_array([$this, $prop], $args);
		}

		elseif (property_exists($prop)) {
			$this->$prop = $value;
		}

		return $this;
	}

	/**
	 * Overload the __isset method
	 */
	public function __isset($prop)
	{
		return array_key_exists($prop, get_object_vars($this));
	}

	/**
	 * Overload the __get method
	 *
	 * @throws Keygen\Exceptions\UnknownPropertyAccessKeygenException
	 */
	public function __get($prop)
	{
		if (method_exists($this, $method = "get{$prop}Property")) {
			return $this->{$method}();
		}

		elseif (property_exists($this, $prop)) {
			return $this->{$prop};
		}

		$problem = sprintf("Trying to access unknown property: %s::%s.", static::class, $prop);
		throw new UnknownPropertyAccessKeygenException($problem);
	}

	/**
	 * Overload the __call method
	 *
	 * @throws Keygen\Exceptions\UnknownMethodCallKeygenException
	 */
	public function __call($method, $args)
	{
		if ($this->hasOverloadedMethod($method)) {
			return call_user_func_array([$this, $method], $args);
		}

		$problem = sprintf("Call to unknown method: %s::%s().", static::class, $method);
		throw new UnknownMethodCallKeygenException($problem);
	}

	/**
	 * Overload the __callStatic method
	 *
	 * @throws Keygen\Exceptions\UnknownMethodCallKeygenException
	 */
	public static function __callStatic($method, $args)
	{
		return (new static)->__call($method, $args);
	}
}
