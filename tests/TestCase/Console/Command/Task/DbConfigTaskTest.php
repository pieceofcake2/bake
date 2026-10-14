<?php
/**
 * DBConfigTask Test Case
 *
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link          https://cakephp.org CakePHP(tm) Project
 * @package       Cake.Test.Case.Console.Command.Task
 * @since         CakePHP(tm) v 1.3
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */

App::uses('ShellDispatcher', 'Console');
App::uses('ConsoleOutput', 'Console');
App::uses('ConsoleInput', 'Console');
App::uses('Shell', 'Console');
App::uses('DbConfigTask', 'Bake.Console/Command/Task');
App::uses('Folder', 'Utility');

/**
 * DbConfigTest class
 *
 * @package       Cake.Test.Case.Console.Command.Task
 */
class DbConfigTaskTest extends CakeTestCase
{
    /**
     * setUp method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        CakePlugin::load('Bake', [
            'path' => dirname(__DIR__, 5) . DS,
        ]);

        $out = $this->getMock('ConsoleOutput', [], [], '', false);
        $in = $this->getMock('ConsoleInput', [], [], '', false);

        $this->Task = $this->getMock(
            'DbConfigTask',
            ['in', 'out', 'err', 'hr', 'createFile', '_stop', '_checkUnitTest', '_verify'],
            [$out, $out, $in],
        );

        $this->Task->path = CONFIG;
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        unset($this->Task);

        parent::tearDown();
    }

    /**
     * Test the getConfig method.
     *
     * @return void
     */
    public function testGetConfig(): void
    {
        $this->Task->expects($this->any())
            ->method('in')
            ->will($this->returnValue('test'));

        $result = $this->Task->getConfig();
        $this->assertEquals('test', $result);
    }

    /**
     * test that initialize sets the path up.
     *
     * @return void
     */
    public function testInitialize(): void
    {
        $this->Task->initialize();
        $this->assertFalse(empty($this->Task->path));
        $this->assertEquals(CONFIG, $this->Task->path);
    }

    /**
     * test execute and by extension _interactive
     *
     * @return void
     */
    public function testExecuteIntoInteractive(): void
    {
        $this->Task->initialize();

        $out = $this->getMock('ConsoleOutput', [], [], '', false);
        $in = $this->getMock('ConsoleInput', [], [], '', false);
        $this->Task = $this->getMock(
            'DbConfigTask',
            ['in', '_stop', 'createFile', 'bake'],
            [$out, $out, $in],
        );

        $this->Task->expects($this->once())->method('_stop');

        $inReturns = [
            'default', // 0: name
            'mysql', // 1: db type
            'n', // 2: persistent
            'localhost', // 3: server
            'n', // 4: port
            'root', // 5: user
            'password', // 6: password
            null,
            null,
            null,
            'cake_test', // 10: db
            'n', // 11: prefix
            'n', // 12: encoding
            'y', // 13: looks good
            'n', // 14: another
        ];
        $inCallIndex = 0;
        $this->Task->expects($this->any())
            ->method('in')
            ->willReturnCallback(function () use ($inReturns, &$inCallIndex) {
                $return = $inReturns[$inCallIndex] ?? null;
                $inCallIndex++;

                return $return;
            });

        $this->Task->expects($this->once())
            ->method('bake')
            ->with([
                [
                    'name' => 'default',
                    'datasource' => 'mysql',
                    'persistent' => 'false',
                    'host' => 'localhost',
                    'login' => 'root',
                    'password' => 'password',
                    'database' => 'cake_test',
                    'prefix' => null,
                    'encoding' => null,
                    'port' => '',
                    'schema' => null,
                ],
            ]);

        $this->Task->execute();
    }

    /**
     * test that answering an upper case N stops asking for more configurations
     *
     * @return void
     */
    public function testExecuteIntoInteractiveUpperCaseAnswer(): void
    {
        $out = $this->getMock('ConsoleOutput', [], [], '', false);
        $in = $this->getMock('ConsoleInput', [], [], '', false);
        $this->Task = $this->getMock(
            'DbConfigTask',
            ['in', '_stop', 'createFile', 'bake'],
            [$out, $out, $in],
        );

        $inReturns = [
            'default', // name
            'mysql', // db type
            'n', // persistent
            'localhost', // server
            'n', // port
            'root', // user
            'password', // password
            'cake_test', // db
            'n', // prefix
            'n', // encoding
            'y', // looks good
            'N', // another
        ];
        $inCallIndex = 0;
        $this->Task->expects($this->any())
            ->method('in')
            ->willReturnCallback(function () use ($inReturns, &$inCallIndex) {
                $return = $inReturns[$inCallIndex] ?? 'N';
                $inCallIndex++;

                return $return;
            });

        $this->Task->expects($this->once())->method('bake');
        $this->Task->expects($this->once())->method('_stop');

        $this->Task->execute();
        $this->assertSame(count($inReturns), $inCallIndex);
    }

    /**
     * test bake writes the database configuration
     *
     * @return void
     */
    public function testBake(): void
    {
        $path = TMP . 'tests' . DS . 'bake_db_config' . DS;
        new Folder($path, true);
        $this->Task->path = $path;

        $this->Task->expects($this->once())
            ->method('createFile')
            ->with(
                $path . 'database.php',
                $this->logicalAnd(
                    $this->stringContains("public \$default = array(\n"),
                    $this->stringContains("'datasource' => 'Database/Mysql',"),
                    $this->stringContains("'persistent' => false,"),
                    $this->stringContains("'host' => 'localhost',"),
                    $this->stringContains("'port' => 3306,"),
                    $this->stringContains("'login' => 'root',"),
                    $this->stringContains("'password' => 'password',"),
                    $this->stringContains("'database' => 'cake',"),
                    $this->stringContains("'prefix' => 'app_',"),
                    $this->stringContains("'encoding' => 'utf8'"),
                ),
            )
            ->will($this->returnValue(true));

        $result = $this->Task->bake([[
            'name' => 'default',
            'datasource' => 'Mysql',
            'persistent' => 'false',
            'host' => 'localhost',
            'port' => 3306,
            'login' => 'root',
            'password' => 'password',
            'database' => 'cake',
            'prefix' => 'app_',
            'encoding' => 'utf8',
        ]]);
        $this->assertTrue($result);

        $Folder = new Folder($path);
        $Folder->delete();
    }
}
