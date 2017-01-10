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
	 * Generates a random key.
	 * 
	 * @param numeric $length
	 * @return string
	 */
	protected function keygen($length)
	{
		$chars = str_shuffle('3759402687094152031368921');
		$chars = str_shuffle(str_repeat($chars, ceil($length / strlen($chars))));
		
		return strrev(str_shuffle(substr($chars, mt_rand(0, (strlen($chars) - $length - 1)), $length)));
	}
}
