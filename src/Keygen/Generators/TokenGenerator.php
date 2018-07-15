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

class TokenGenerator extends Generator
{
	/**
	 * Generates a random key.
	 *
	 * @param numeric $length
	 * @return string
	 */
	protected function keygen($length)
	{
		$token = '';
		$tokenlength = round($length * 4 / 3);

		for ($i = 0; $i < $tokenlength; ++$i) {
			$token .= chr(rand(32,1024));
		}

		$token = base64_encode(str_shuffle($token));

		return substr($token, 0, $length);
	}
}
