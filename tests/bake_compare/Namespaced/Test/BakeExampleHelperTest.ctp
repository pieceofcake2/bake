<?php

namespace App\Test\TestCase\View\Helper;

use App\View\Helper\BakeExampleHelper;
use Cake\TestSuite\CakeTestCase;
use Cake\View\View;

/**
 * BakeExampleHelper Test Case
 */
class BakeExampleHelperTest extends CakeTestCase
{
    /**
     * setUp method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $View = new View();
        $this->BakeExample = new BakeExampleHelper($View);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        unset($this->BakeExample);

        parent::tearDown();
    }

    /**
     * testFormat method
     *
     * @return void
     */
    public function testFormat(): void
    {
        $this->markTestIncomplete('testFormat not implemented.');
    }
}
