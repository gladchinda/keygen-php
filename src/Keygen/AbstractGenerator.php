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

use Keygen\Traits\KeyManipulation;
use Keygen\Traits\MutableGenerator;
use Keygen\Traits\GeneratorMutation;

abstract class AbstractGenerator
{
	use KeyManipulation, MutableGenerator, GeneratorMutation {
		MutableGenerator::flattenArguments insteadof GeneratorMutation;
	}

	/**
	 * Creates a new KeyGenerator instance.
	 * 
	 * @param mixed $length
	 * 
	 * @throws \InvalidArgumentException
	 */
	public function __construct($length = 16)
	{
		$this->length($length);
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
	 */
	public function __get($prop)
	{
		if (isset($this->{$prop})) {
			return $this->{$prop};
		}
	}

	/**
	 * Overload the __call method 
	 */
	public function __call($method, $args)
	{
		return $this->__overloadMethods($method, $args);
	}

	/**
	 * Overload the __callStatic method 
	 */
	public static function __callStatic($method, $args)
	{
		return (new static)->__call($method, $args);
	}
}
