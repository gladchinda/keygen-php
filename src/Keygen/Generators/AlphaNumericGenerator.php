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
	 * The chunking factor.
	 *
	 * @var int
	 */
	protected static $chunkFactor = 5;

	/**
	 * Initiates a long alphanumeric character sequence.
	 *
	 * @return string
	 */
	protected static function initAlphaNumericChars()
	{
		$numeric = range(0, 9);
		$bigAlpha = range('A', 'Z');
		$smallAlpha = range('a', 'z');

		$chars = array_merge($numeric, $smallAlpha, $numeric, $bigAlpha, $numeric);
		shuffle($chars);

		return str_shuffle(str_rot13(join('', $chars)));
	}

	/**
	 * Generates a set character chunks based on length.
	 *
	 * @param int $length
	 * @return array
	 */
	protected static function generateCharChunksByLength($length)
	{
		$chunkArray = array();
		$chars = static::initAlphaNumericChars();

		$size = strlen($chars);
		$split = intval(ceil($length / static::$chunkFactor));

		$splitSize = ceil($size / $split);
		$chunkSize = static::$chunkFactor + $splitSize + mt_rand(1, static::$chunkFactor);

		$chars = str_shuffle(str_repeat($chars, 2));
		$size = strlen($chars);

		while ($split != 0) {
			$strip = substr($chars, mt_rand(0, $size - $chunkSize), $chunkSize);
			array_push($chunkArray, strrev($strip));
			$split--;
		}

		return $chunkArray;
	}

	/**
	 * Generates an alphanumeric character sequence.
	 *
	 * @param int $length
	 * @return string
	 */
	protected static function generateAlphaNumericCharsByLength($length)
	{
		$chars = '';
		$chunks = static::generateCharChunksByLength($length);

		foreach ($chunks as $set) {

			$modulus = ($length - strlen($chars)) % static::$chunkFactor;
			$adjusted = ($modulus > 0) ? $modulus : static::$chunkFactor;

			$chars .= substr($set, mt_rand(0, strlen($set) - $adjusted), $adjusted);

		}

		return $chars;
	}

	/**
	 * Generates a random key.
	 *
	 * @param int $length
	 * @return string
	 */
	protected function keygen($length)
	{
		$key = static::generateAlphaNumericCharsByLength($length);
		return str_rot13(str_shuffle($key));
	}
}
