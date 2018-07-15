<?php

use Keygen\Support\Utils;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass Keygen\Support\Utils
 */
final class UtilsTest extends TestCase
{
	/**
	 * @covers ::flatten
	 */
	public function testflatten()
	{
		$argsSet = ['hello', 1, 'john', -23, '5.67', new \stdClass];
		$emptySet = [];

		$firstCase = Utils::flatten('hello', 1, 'john', -23, '5.67', new \stdClass);
		$this->assertCount(6, $firstCase);
		$this->assertEquals($argsSet, $firstCase);

		$secondCase = Utils::flatten(['hello'], 1, 'john', [[-23], '5.67', [new \stdClass]]);
		$this->assertCount(6, $secondCase);
		$this->assertEquals($argsSet, $secondCase);

		$thirdCase = Utils::flatten([['hello', [1, ['john'], -23]], ['5.67'], new \stdClass]);
		$this->assertCount(6, $thirdCase);
		$this->assertEquals($argsSet, $thirdCase);

		$fourthCase = Utils::flatten();
		$this->assertEmpty($fourthCase);

		$fifthCase = Utils::flatten([[[]]]);
		$this->assertEmpty($fifthCase);
	}
}
