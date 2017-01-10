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

use InvalidArgumentException;

class GeneratorFactory
{
	/**
	 * Create a generator instance from the specified type.
	 * 
	 * @param string $type Generator type.
	 * @return Keygen\Generator
	 * 
	 * @throws \InvalidArgumentException
	 */
	public static function create($type)
	{
		$generator = sprintf("Keygen\Generators\%sGenerator", ucfirst($type));

		if (class_exists($generator)) {
			$generator = new $generator;

			if ($generator instanceof Generator) {
				return $generator;
			}
		}

		throw new InvalidArgumentException('Cannot create unknown generator type.');
	}
}
