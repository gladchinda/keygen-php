<?php

/*
 * This file is part of the Keygen package.
 *
 * (c) Glad Chinda <gladxeqs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Keygen\Traits;

use InvalidArgumentException;
use Keygen\AbstractGenerator;

trait GeneratorMutation
{
	use FlattenArguments;

	/**
	 * A collection of mutable generators to receive property mutations.
	 * 
	 * @var array
	 */
	protected $mutates = [];

	/**
	 * Object difference comparator using array_diff callback.
	 * 
	 * @param array $array1
	 * @param array $array2
	 * @return array
	 */
	private static function objectDiffInArrays($array1, $array2)
	{
		return array_udiff($array1, $array2, function($a, $b) {
			if ($a === $b) {
				return 0;
			} elseif ($a < $b) {
				return -1;
			} elseif ($a > $b) {
				return 1;
			}
		});
	}

	/**
	 * Add mutable generators to the mutates collection
	 * 
	 * @param mixed $objects
	 * @return $this
	 * 
	 * @throws \InvalidArgumentException
	 */
	public function mutate($objects)
	{
		$objects = call_user_func_array(array($this, 'flattenArguments'), func_get_args());
		$collect = [];

		foreach ($objects as $obj) {
			if ($obj instanceof AbstractGenerator) {
				array_push($collect, $obj);
				continue;
			}

			throw new InvalidArgumentException(sprintf('Mutable objects must be instances of %s.', AbstractGenerator::class));
		}
		
		$this->mutates = array_merge(static::objectDiffInArrays($this->mutates, $collect), $collect);
		
		return $this;
	}

	/**
	 * Remove generators from the mutates collection
	 * 
	 * @param mixed $objects
	 * @return $this
	 */
	public function dontMutate($objects)
	{
		$objects = call_user_func_array(array($this, 'flattenArguments'), func_get_args());
		
		$this->mutates = static::objectDiffInArrays($this->mutates, $objects);
		
		return $this;
	}
}
