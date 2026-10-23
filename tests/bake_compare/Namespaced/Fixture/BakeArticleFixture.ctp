<?php

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\CakeTestFixture;

/**
 * BakeArticle Fixture
 */
class BakeArticleFixture extends CakeTestFixture {

/**
 * Table name
 *
 * @var string
 */
	public $table = 'articles';

/**
 * Import
 *
 * @var array
 */
	public $import = array('model' => 'BakeArticle', 'records' => true);

}
