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
	 * Handles key generation logic and returns the generated key.
	 * 
	 * @return string
	 */
	public function generate();
}
