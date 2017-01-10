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

trait IntegerCasting
{
	/**
	 * Casts the given value into an integer and returns the integer.
	 * Throws an exception if value cannot be casted to an integer.
	 * 
	 * @param mixed $value
	 * @return int
	 * 
	 * @throws \InvalidArgumentException
	 */
	protected function intCast($value)
	{
		if (is_numeric($value)) {
			return intval($value);
		}

		throw new InvalidArgumentException('The given value cannot be converted to an integer.');
	}
}
