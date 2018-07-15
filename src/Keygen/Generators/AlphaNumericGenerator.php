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

use Keygen\Generator;

class AlphaNumericGenerator extends Generator
{
	/**
	 * Generates a random key.
	 *
	 * @param numeric $length
	 * @return string
	 */
	protected function keygen($length)
	{
		$key = '';
		$chars = array_merge(range(0, 9), range('a', 'z'), range(0, 9), range('A', 'Z'), range(0, 9));
		shuffle($chars);

		$chars = str_shuffle(str_rot13(join('', $chars)));
		$split = intval(ceil($length / 5));
		$size = strlen($chars);

		$splitSize = ceil($size / $split);
		$chunkSize = 5 + $splitSize + mt_rand(1, 5);
		$chunkArray = array();

		$chars = str_shuffle(str_repeat($chars, 2));
		$size = strlen($chars);

		while ($split != 0) {
			$strip = substr($chars, mt_rand(0, $size - $chunkSize), $chunkSize);
			array_push($chunkArray, strrev($strip));
			$split--;
		}

		foreach ($chunkArray as $set) {
			$modulus = ($length - strlen($key)) % 5;
			$adjusted = ($modulus > 0) ? $modulus : 5;

			$key .= substr($set, mt_rand(0, strlen($set) - $adjusted), $adjusted);
		}

		return str_rot13(str_shuffle($key));
	}
}
