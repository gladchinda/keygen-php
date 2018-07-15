<?php

/*
 * This file is part of the Keygen package.
 *
 * (c) Glad Chinda <gladxeqs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Keygen\Generators;

use RuntimeException;
use Keygen\Generator;

class RandomByteGenerator extends Generator
{
	/**
	 * Hexadecimal output enabled.
	 *
	 * @var bool
	 */
	protected $hex = false;

	/**
	 * Enables hexadecimal output of byte string.
	 *
	 * @return $this
	 */
	public function hex()
	{
		$this->hex = true;
		return $this;
	}

	/**
	 * Generates a random key.
	 *
	 * @param numeric $length
	 * @return string
	 *
	 * @throws \RuntimeException
	 */
	protected function keygen($length)
	{
		$hex = !is_bool($this->hex) ?: $this->hex;
		$bytelength = $hex ? ceil($length / 2) : $length;

		if (function_exists('random_bytes')) {
			$bytes = random_bytes($bytelength);
		}

		elseif (function_exists('openssl_random_pseudo_bytes')) {
			$bytes = openssl_random_pseudo_bytes($bytelength);
		}

		elseif (@file_exists('/dev/urandom') && $bytelength < 100) {
			$bytes = file_get_contents('/dev/urandom', false, null, 0, $bytelength);
		}

		else {
			throw new RuntimeException('Cannot generate binary data.');
		}

		return $hex ? substr(bin2hex($bytes), 0, $length) : $bytes;
	}

	/**
	 * Outputs a generated key including the prefix and suffix if any.
	 * May also return transformed keys.
	 *
	 * @return string
	 */
	public function generate()
	{
		$key = call_user_func_array('parent::generate', func_get_args());

		if ($this->hex === true) {
			$this->hex = false;
		}

		return $key;
	}
}
