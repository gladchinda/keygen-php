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

interface GeneratorInterface
{
	/**
	 * Outputs a generated key including the prefix and suffix if any.
	 * May also return transformed keys.
	 * 
	 * @return string
	 */
	public function generate();
}
