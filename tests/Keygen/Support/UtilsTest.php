<?php

use Keygen\Support\Utils;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass Keygen\Support\Utils
 */
final class UtilsTest extends TestCase
{
	/**
	 * @covers ::flattenArguments
	 */
	public function testFlattenArguments()
	{
		$argsSet = ['hello', 1, 'john', -23, '5.67', new \stdClass];
		$emptySet = [];

		$firstCase = Utils::flattenArguments('hello', 1, 'john', -23, '5.67', new \stdClass);
		$this->assertCount(6, $firstCase);
		$this->assertEquals($argsSet, $firstCase);

		$secondCase = Utils::flattenArguments(['hello'], 1, 'john', [[-23], '5.67', [new \stdClass]]);
		$this->assertCount(6, $secondCase);
		$this->assertEquals($argsSet, $secondCase);

		$thirdCase = Utils::flattenArguments([['hello', [1, ['john'], -23]], ['5.67'], new \stdClass]);
		$this->assertCount(6, $thirdCase);
		$this->assertEquals($argsSet, $thirdCase);

		$fourthCase = Utils::flattenArguments();
		$this->assertEmpty($fourthCase);

		$fifthCase = Utils::flattenArguments([[[]]]);
		$this->assertEmpty($fifthCase);
	}
}
