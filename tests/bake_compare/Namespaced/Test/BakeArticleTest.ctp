<?php

namespace App\Test\TestCase\Model;

use App\Model\BakeArticle;
use Cake\TestSuite\CakeTestCase;
use Cake\Utility\ClassRegistry;

/**
 * BakeArticle Test Case
 */
class BakeArticleTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.bake_article',
		'app.bake_comment',
		'app.bake_tag',
		'app.bake_articles_bake_tag'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp(): void {
		parent::setUp();
		$this->BakeArticle = ClassRegistry::init(BakeArticle::class);
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown(): void {
		unset($this->BakeArticle);

		parent::tearDown();
	}

/**
 * testPublish method
 *
 * @return void
 */
	public function testPublish() {
		$this->markTestIncomplete('testPublish not implemented.');
	}

}
