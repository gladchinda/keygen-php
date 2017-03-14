<?php

use Keygen\Keygen;
use PHPUnit\Framework\TestCase;

/**
 * @covers Keygen
 */
final class KeygenTest extends TestCase
{
	protected $generator;

	protected function setUp()
	{
		$this->generator = new Keygen;
	}
}
