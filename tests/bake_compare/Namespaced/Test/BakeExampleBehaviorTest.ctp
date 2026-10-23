<?php

namespace App\Test\TestCase\Model\Behavior;

use App\Model\Behavior\BakeExampleBehavior;
use Cake\TestSuite\CakeTestCase;

/**
 * BakeExampleBehavior Test Case
 */
class BakeExampleBehaviorTest extends CakeTestCase {

/**
 * setUp method
 *
 * @return void
 */
	public function setUp(): void {
		parent::setUp();
		$this->BakeExample = new BakeExampleBehavior();
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown(): void {
		unset($this->BakeExample);

		parent::tearDown();
	}

/**
 * testSlug method
 *
 * @return void
 */
	public function testSlug() {
		$this->markTestIncomplete('testSlug not implemented.');
	}

}
