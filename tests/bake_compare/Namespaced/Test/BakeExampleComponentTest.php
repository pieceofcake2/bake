<?php

namespace App\Test\TestCase\Controller\Component;

use App\Controller\Component\BakeExampleComponent;
use Cake\Controller\ComponentCollection;
use Cake\TestSuite\CakeTestCase;

/**
 * BakeExampleComponent Test Case
 */
class BakeExampleComponentTest extends CakeTestCase
{
    /**
     * setUp method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $Collection = new ComponentCollection();
        $this->BakeExample = new BakeExampleComponent($Collection);
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
     * testGreet method
     *
     * @return void
     */
    public function testGreet(): void
    {
        $this->markTestIncomplete('testGreet not implemented.');
    }
}
