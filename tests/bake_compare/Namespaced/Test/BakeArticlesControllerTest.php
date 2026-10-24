<?php

namespace App\Test\TestCase\Controller;

use Cake\TestSuite\ControllerTestCase;

/**
 * BakeArticlesController Test Case
 */
class BakeArticlesControllerTest extends ControllerTestCase
{
    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.bake_article',
        'app.bake_comment',
        'app.bake_tag',
        'app.bake_articles_bake_tag',
    ];

    /**
     * testIndex method
     *
     * @return void
     */
    public function testIndex(): void
    {
        $this->markTestIncomplete('testIndex not implemented.');
    }

    /**
     * testAdd method
     *
     * @return void
     */
    public function testAdd(): void
    {
        $this->markTestIncomplete('testAdd not implemented.');
    }
}
