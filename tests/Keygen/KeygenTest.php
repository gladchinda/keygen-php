<?php

use Keygen\Keygen;
use PHPUnit\Framework\TestCase;

/**
 * @covers Keygen
 */
final class KeygenTest extends TestCase
{
	protected $keygen;

	protected function setUp()
	{
		$this->keygen = new Keygen;
	}
}
