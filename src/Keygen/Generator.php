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

abstract class Generator extends AbstractGenerator implements GeneratorInterface
{
	/**
	 * Generates a random key.
	 * 
	 * @param numeric $length
	 * @return string
	 */
	abstract protected function keygen($length);

	/**
	 * Outputs a generated key including the prefix and suffix if any.
	 * May also return transformed keys.
	 * 
	 * @return string
	 */
	public function generate()
	{
		$args = func_get_args();
		$useKeyLength = array_shift($args);

		if (!is_bool($useKeyLength)) {
			array_unshift($args, $useKeyLength);
			$useKeyLength = false;
		}

		$callables = call_user_func_array(array($this, 'flattenArguments'), $args);

		$key = $this->keygen($useKeyLength ? $this->length : $this->getAdjustedKeyLength());

		while ($callable = current($callables)) {
			if (is_callable($callable)) {
				$key = call_user_func($callable, $key);
			}
			next($callables);
		}

		return sprintf("%s%s%s", $this->prefix, $key, $this->suffix);
	}
}
