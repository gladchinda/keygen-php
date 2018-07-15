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

class NumericGenerator extends Generator
{
	/**
	 * Determines whether numeric keys can start with zero.
	 *
	 * @var bool
	 */
	protected $nonZeroFirst = false;

	/**
	 * Disables non-zero starting for generator.
	 *
	 * @return $this
	 */
	protected function anyfirst()
	{
		$this->nonZeroFirst = false;
		return $this;
	}

	/**
	 * Prevents zero at beginning of numeric key.
	 *
	 * @return $this
	 */
	protected function nzfirst()
	{
		$this->nonZeroFirst = true;
		return $this;
	}

	/**
	 * Generates a random numeric character sequence.
	 *
	 * @return string
	 */
	protected static function generateNumericChars($length)
	{
		$chars = str_shuffle('3759402687094152031368921');
		$repeatBy = ceil($length / (strlen($chars) - 1));

		$repeatBy = intval(($repeatBy > 0) ? $repeatBy : 1);
		$chars = str_shuffle(str_repeat($chars, $repeatBy));

		return strrev(str_shuffle(substr($chars, mt_rand(0, (strlen($chars) - $length - 1)), $length)));
	}

	/**
	 * Generates a random key.
	 *
	 * @param int $length
	 * @return string
	 */
	protected function keygen($length)
	{
		if ($this->nonZeroFirst) {
			return mt_rand(1, 9) . static::generateNumericChars($length - 1);
		}

		return static::generateNumericChars($length);
	}

	/**
	 * List of the allowed overloaded attributes.
	 *
	 * @return array
	 */
	protected static function getOverloadedAttributes()
	{
		$appends = ['anyfirst', 'nzfirst'];
		$attributes = parent::getOverloadedAttributes();

		return array_unique(array_merge($attributes, $appends));
	}

	/**
	 * List of the allowed overloaded methods.
	 *
	 * @return array
	 */
	protected static function getOverloadedMethods()
	{
		$appends = ['anyfirst', 'nzfirst'];
		$methods = parent::getOverloadedMethods();

		return array_unique(array_merge($methods, $appends));
	}
}
