<?php
/**
 * TestTaskTest file
 *
 * Test Case for test generation shell task
 *
 * CakePHP :  Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link          https://cakephp.org CakePHP Project
 * @package       Cake.Test.Case.Console.Command.Task
 * @since         CakePHP v 1.2.0.7726
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */

/**
 * Test Article model
 *
 * @package       Cake.Test.Case.Console.Command.Task
 */
class TestTaskArticle extends Model
{
    /**
     * Table name to use
     *
     * @var string
     */
    public $useTable = 'articles';

    /**
     * HasMany Associations
     *
     * @var array
     */
    public $hasMany = [
        'Comment' => [
            'className' => 'TestTask.TestTaskComment',
            'foreignKey' => 'article_id',
        ],
    ];

    /**
     * Has and Belongs To Many Associations
     *
     * @var array
     */
    public $hasAndBelongsToMany = [
        'Tag' => [
            'className' => 'TestTaskTag',
            'joinTable' => 'articles_tags',
            'foreignKey' => 'article_id',
            'associationForeignKey' => 'tag_id',
        ],
    ];

    /**
     * Example public method
     *
     * @return void
     */
    public function doSomething(): void
    {
    }

    /**
     * Example Secondary public method
     *
     * @return void
     */
    public function doSomethingElse(): void
    {
    }

    /**
     * Example protected method
     *
     * @return void
     */
    protected function _innerMethod(): void
    {
    }
}

/**
 * Tag Testing Model
 *
 * @package       Cake.Test.Case.Console.Command.Task
 */
class TestTaskTag extends Model
{
    /**
     * Table name
     *
     * @var string
     */
    public $useTable = 'tags';

    /**
     * Has and Belongs To Many Associations
     *
     * @var array
     */
    public $hasAndBelongsToMany = [
        'Article' => [
            'className' => 'TestTaskArticle',
            'joinTable' => 'articles_tags',
            'foreignKey' => 'tag_id',
            'associationForeignKey' => 'article_id',
        ],
    ];
}

/**
 * Simulated plugin
 *
 * @package       Cake.Test.Case.Console.Command.Task
 */
class TestTaskAppModel extends Model
{
}

/**
 * Testing AppMode (TaskComment)
 *
 * @package       Cake.Test.Case.Console.Command.Task
 */
class TestTaskComment extends TestTaskAppModel
{
    /**
     * Table name
     *
     * @var string
     */
    public $useTable = 'comments';

    /**
     * Belongs To Associations
     *
     * @var array
     */
    public $belongsTo = [
        'Article' => [
            'className' => 'TestTaskArticle',
            'foreignKey' => 'article_id',
        ],
    ];
}

/**
 * Test Task Comments Controller
 *
 * @package       Cake.Test.Case.Console.Command.Task
 */
class TestTaskCommentsController extends Controller
{
    /**
     * Models to use
     *
     * @var array
     */
    public $uses = ['TestTaskComment', 'TestTaskTag'];
}

/**
 * TestTaskTest class
 *
 * @package       Cake.Test.Case.Console.Command.Task
 */
class TestTaskTest extends CakeTestCase
{
    /**
     * Fixtures
     *
     * @var string
     */
    public $fixtures = ['core.article', 'core.comment', 'core.articles_tag', 'core.tag'];

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
            'TestTask',
            ['in', 'err', 'createFile', '_stop', 'isLoadableClass', '_usesIsDeprecated'],
            [$out, $out, $in],
        );
        $this->Task->name = 'Test';
        $this->Task->Template = new TemplateTask($out, $out, $in);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        unset($this->Task);
        CakePlugin::unload();

        parent::tearDown();
    }

    /**
     * Test that file path generation doesn't continuously append paths.
     *
     * @return void
     */
    public function testFilePathGenerationModelRepeated(): void
    {
        $this->Task->expects($this->never())->method('err');
        $this->Task->expects($this->never())->method('_stop');

        $createFileCalls = [];
        $this->Task->expects($this->exactly(3))
            ->method('createFile')
            ->willReturnCallback(function ($file, $content) use (&$createFileCalls): void {
                $createFileCalls[] = ['file' => $file, 'content' => $content];
            });

        $this->Task->bake('Model', 'MyClass');
        $this->Task->bake('Model', 'MyClass');
        $this->Task->bake('Controller', 'Comments');

        $modelFile = TESTS . 'TestCase' . DS . 'Model' . DS . 'MyClassTest.php';
        $controllerFile = TESTS . 'TestCase' . DS . 'Controller' . DS . 'CommentsControllerTest.php';
        $this->assertEquals($modelFile, $createFileCalls[0]['file']);
        $this->assertEquals($modelFile, $createFileCalls[1]['file']);
        $this->assertEquals($controllerFile, $createFileCalls[2]['file']);
    }

    /**
     * Test that method introspection pulls all relevant non parent class
     * methods into the test case.
     *
     * @return void
     */
    public function testMethodIntrospection(): void
    {
        $result = $this->Task->getTestableMethods('TestTaskArticle');
        $expected = ['dosomething', 'dosomethingelse'];
        $this->assertEquals($expected, array_map('strtolower', $result));
    }

    /**
     * test that the generation of fixtures works correctly.
     *
     * @return void
     */
    public function testFixtureArrayGenerationFromModel(): void
    {
        $subject = ClassRegistry::init('TestTaskArticle');
        $result = $this->Task->generateFixtureList($subject);
        $expected = ['plugin.test_task.test_task_comment', 'app.articles_tags',
            'app.test_task_article', 'app.test_task_tag'];

        $this->assertEquals(sort($expected), sort($result));
    }

    /**
     * test that the generation of fixtures works correctly.
     *
     * @return void
     */
    public function testFixtureArrayGenerationFromController(): void
    {
        $subject = new TestTaskCommentsController();
        $result = $this->Task->generateFixtureList($subject);
        $expected = ['plugin.test_task.test_task_comment', 'app.articles_tags',
            'app.test_task_article', 'app.test_task_tag'];

        $this->assertEquals(sort($expected), sort($result));
    }

    /**
     * test user interaction to get object type
     *
     * @return void
     */
    public function testGetObjectType(): void
    {
        $this->Task->expects($this->once())->method('_stop');

        $this->Task->expects($this->exactly(2))
            ->method('in')
            ->willReturnOnConsecutiveCalls('q', 2);

        $this->Task->getObjectType();

        $result = $this->Task->getObjectType();
        $this->assertEquals($this->Task->classTypes['Controller'], $result);
    }

    /**
     * creating test subjects should clear the registry so the registry is always fresh
     *
     * @return void
     */
    public function testRegistryClearWhenBuildingTestObjects(): void
    {
        ClassRegistry::flush();
        $model = ClassRegistry::init('TestTaskComment');
        $model->bindModel([
            'belongsTo' => [
                'Random' => [
                    'className' => 'TestTaskArticle',
                    'foreignKey' => 'article_id',
                ],
            ],
        ]);
        $keys = ClassRegistry::keys();
        $this->assertTrue(in_array('test_task_comment', $keys));
        $this->Task->buildTestSubject('Model', 'TestTaskComment');

        $keys = ClassRegistry::keys();
        $this->assertFalse(in_array('random', $keys));
    }

    /**
     * test that getClassName returns the user choice as a class name.
     *
     * @return void
     */
    public function testGetClassName(): void
    {
        $objects = App::objects('model');
        $this->skipIf(empty($objects), 'No models in app.');

        $this->Task->expects($this->exactly(2))
            ->method('in')
            ->willReturnOnConsecutiveCalls('MyCustomClass', 1);

        $result = $this->Task->getClassName('Model');
        $this->assertEquals('MyCustomClass', $result);

        $result = $this->Task->getClassName('Model');
        $options = App::objects('model');
        $this->assertEquals($options[0], $result);
    }

    /**
     * Test the user interaction for defining additional fixtures.
     *
     * @return void
     */
    public function testGetUserFixtures(): void
    {
        $this->Task->expects($this->exactly(2))
            ->method('in')
            ->willReturnOnConsecutiveCalls('y', 'app.pizza, app.topping, app.side_dish');

        $result = $this->Task->getUserFixtures();
        $expected = ['app.pizza', 'app.topping', 'app.side_dish'];
        $this->assertEquals($expected, $result);
    }

    /**
     * test that resolving class names works
     *
     * @return void
     */
    public function testGetRealClassname(): void
    {
        $result = $this->Task->getRealClassname('Model', 'Post');
        $this->assertEquals('Post', $result);

        $result = $this->Task->getRealClassname('Controller', 'Posts');
        $this->assertEquals('PostsController', $result);

        $result = $this->Task->getRealClassname('Controller', 'PostsController');
        $this->assertEquals('PostsController', $result);

        $result = $this->Task->getRealClassname('Controller', 'AlertTypes');
        $this->assertEquals('AlertTypesController', $result);

        $result = $this->Task->getRealClassname('Helper', 'Form');
        $this->assertEquals('FormHelper', $result);

        $result = $this->Task->getRealClassname('Helper', 'FormHelper');
        $this->assertEquals('FormHelper', $result);

        $result = $this->Task->getRealClassname('Behavior', 'Containable');
        $this->assertEquals('ContainableBehavior', $result);

        $result = $this->Task->getRealClassname('Behavior', 'ContainableBehavior');
        $this->assertEquals('ContainableBehavior', $result);

        $result = $this->Task->getRealClassname('Component', 'Auth');
        $this->assertEquals('AuthComponent', $result);
    }

    /**
     * test baking files. The conditionally run tests are known to fail in PHP4
     * as PHP4 class names are all lower case, breaking the plugin path inflection.
     *
     * @return void
     */
    public function testBakeModelTest(): void
    {
        $this->Task->expects($this->once())->method('createFile')->will($this->returnValue(true));
        $this->Task->expects($this->once())->method('isLoadableClass')->will($this->returnValue(true));

        $result = $this->Task->bake('Model', 'TestTaskArticle');

        // The test app uses the modern layout: ClassRegistry::init() loads the model
        $this->assertStringNotContainsString('App::uses(', $result);
        $this->assertStringContainsString('class TestTaskArticleTest extends CakeTestCase', $result);

        $this->assertStringContainsString('function setUp()', $result);
        $this->assertStringContainsString("\$this->TestTaskArticle = ClassRegistry::init('TestTaskArticle')", $result);

        $this->assertStringContainsString('function tearDown()', $result);
        $this->assertStringContainsString('unset($this->TestTaskArticle)', $result);

        $this->assertStringContainsString('function testDoSomething()', $result);
        $this->assertStringContainsString('function testDoSomethingElse()', $result);
        $this->assertStringContainsString('$this->markTestIncomplete(\'testDoSomething not implemented.\')', $result);
        $this->assertStringContainsString('$this->markTestIncomplete(\'testDoSomethingElse not implemented.\')', $result);

        $this->assertStringContainsString("'app.test_task_article'", $result);
        $this->assertStringContainsString("'app.test_task_comment'", $result);
        $this->assertStringContainsString("'app.test_task_tag'", $result);
        $this->assertStringContainsString("'app.articles_tag'", $result);
    }

    /**
     * test baking controller test files
     *
     * @return void
     */
    public function testBakeControllerTest(): void
    {
        $this->Task->expects($this->once())->method('createFile')->will($this->returnValue(true));
        $this->Task->expects($this->once())->method('isLoadableClass')->will($this->returnValue(true));

        $result = $this->Task->bake('Controller', 'TestTaskComments');

        // The test app uses the modern layout: ControllerTestCase::generate() loads the controller
        $this->assertStringNotContainsString('App::uses(', $result);
        $this->assertStringContainsString('class TestTaskCommentsControllerTest extends ControllerTestCase', $result);

        $this->assertStringNotContainsString('function setUp()', $result);
        $this->assertStringNotContainsString('$this->TestTaskComments = new TestTaskCommentsController()', $result);
        $this->assertStringNotContainsString('$this->TestTaskComments->constructClasses()', $result);

        $this->assertStringNotContainsString('function tearDown()', $result);
        $this->assertStringNotContainsString('unset($this->TestTaskComments)', $result);

        $this->assertStringContainsString("'app.test_task_article'", $result);
        $this->assertStringContainsString("'app.test_task_comment'", $result);
        $this->assertStringContainsString("'app.test_task_tag'", $result);
        $this->assertStringContainsString("'app.articles_tag'", $result);
    }

    /**
     * test baking component test files,
     *
     * @return void
     */
    public function testBakeComponentTest(): void
    {
        $this->Task->expects($this->once())->method('createFile')->will($this->returnValue(true));

        $result = $this->Task->bake('Component', 'Example');

        // The CakePHP classes are autoloaded by the Composer classmap in the modern layout
        $this->assertStringNotContainsString("App::uses('Component', 'Controller')", $result);
        $this->assertStringNotContainsString("App::uses('ComponentCollection', 'Controller')", $result);
        $this->assertStringContainsString("App::uses('ExampleComponent', 'Controller/Component')", $result);
        $this->assertStringContainsString('class ExampleComponentTest extends CakeTestCase', $result);

        $this->assertStringContainsString('function setUp()', $result);
        $this->assertStringContainsString('$Collection = new ComponentCollection()', $result);
        $this->assertStringContainsString('$this->Example = new ExampleComponent($Collection)', $result);

        $this->assertStringContainsString('function tearDown()', $result);
        $this->assertStringContainsString('unset($this->Example)', $result);
    }

    /**
     * test that tests baked for the legacy layout or a plugin keep all App::uses() calls.
     *
     * @return void
     */
    public function testBakeComponentTestWithAppUses(): void
    {
        $out = $this->getMock('ConsoleOutput', [], [], '', false);
        $in = $this->getMock('ConsoleInput', [], [], '', false);
        $Task = $this->getMock(
            'TestTask',
            ['in', 'err', 'createFile', '_stop', 'isLoadableClass', '_needsAppUses', '_usesIsDeprecated'],
            [$out, $out, $in],
        );
        $Task->name = 'Test';
        $Task->Template = new TemplateTask($out, $out, $in);
        $Task->expects($this->once())->method('createFile')->will($this->returnValue(true));
        $Task->expects($this->any())->method('_needsAppUses')->will($this->returnValue(true));

        $result = $Task->bake('Component', 'Example');

        $this->assertStringContainsString("App::uses('Component', 'Controller')", $result);
        $this->assertStringContainsString("App::uses('ComponentCollection', 'Controller')", $result);
        $this->assertStringContainsString("App::uses('ExampleComponent', 'Controller/Component')", $result);
    }

    /**
     * test that tests baked for the modern layout do not call App::uses() on CakePHP 2.13 and later
     *
     * @return void
     */
    public function testBakeComponentTestWithoutAppUses(): void
    {
        $this->Task->expects($this->once())->method('createFile')->will($this->returnValue(true));
        $this->Task->expects($this->any())->method('_usesIsDeprecated')->will($this->returnValue(true));

        $result = $this->Task->bake('Component', 'Example');

        $this->assertStringNotContainsString('App::uses(', $result);
        $this->assertStringContainsString('$this->Example = new ExampleComponent($Collection)', $result);
    }

    /**
     * test that tests baked for the legacy layout or a plugin only load the class under test
     * with App::uses() on CakePHP 2.13 and later
     *
     * @return void
     */
    public function testBakeComponentTestWithAppUsesOfClassUnderTest(): void
    {
        $out = $this->getMock('ConsoleOutput', [], [], '', false);
        $in = $this->getMock('ConsoleInput', [], [], '', false);
        $Task = $this->getMock(
            'TestTask',
            ['in', 'err', 'createFile', '_stop', 'isLoadableClass', '_needsAppUses', '_usesIsDeprecated'],
            [$out, $out, $in],
        );
        $Task->name = 'Test';
        $Task->Template = new TemplateTask($out, $out, $in);
        $Task->expects($this->once())->method('createFile')->will($this->returnValue(true));
        $Task->expects($this->any())->method('_needsAppUses')->will($this->returnValue(true));
        $Task->expects($this->any())->method('_usesIsDeprecated')->will($this->returnValue(true));

        $result = $Task->bake('Component', 'Example');

        $this->assertStringNotContainsString("App::uses('Component', 'Controller')", $result);
        $this->assertStringNotContainsString("App::uses('ComponentCollection', 'Controller')", $result);
        $this->assertStringContainsString("App::uses('ExampleComponent', 'Controller/Component')", $result);
    }

    /**
     * test baking behavior test files,
     *
     * @return void
     */
    public function testBakeBehaviorTest(): void
    {
        $this->Task->expects($this->once())->method('createFile')->will($this->returnValue(true));

        $result = $this->Task->bake('Behavior', 'Example');

        $this->assertStringContainsString("App::uses('ExampleBehavior', 'Model/Behavior')", $result);
        $this->assertStringContainsString('class ExampleBehaviorTest extends CakeTestCase', $result);

        $this->assertStringContainsString('function setUp()', $result);
        $this->assertStringContainsString('$this->Example = new ExampleBehavior()', $result);

        $this->assertStringContainsString('function tearDown()', $result);
        $this->assertStringContainsString('unset($this->Example)', $result);
    }

    /**
     * test baking helper test files,
     *
     * @return void
     */
    public function testBakeHelperTest(): void
    {
        $this->Task->expects($this->once())->method('createFile')->will($this->returnValue(true));

        $result = $this->Task->bake('Helper', 'Example');

        $this->assertStringContainsString("App::uses('ExampleHelper', 'View/Helper')", $result);
        $this->assertStringContainsString('class ExampleHelperTest extends CakeTestCase', $result);

        $this->assertStringContainsString('function setUp()', $result);
        $this->assertStringContainsString('$View = new View()', $result);
        $this->assertStringContainsString('$this->Example = new ExampleHelper($View)', $result);

        $this->assertStringContainsString('function tearDown()', $result);
        $this->assertStringContainsString('unset($this->Example)', $result);
    }

    /**
     * test Constructor generation ensure that constructClasses is called for controllers
     *
     * @return void
     */
    public function testGenerateConstructor(): void
    {
        $result = $this->Task->generateConstructor('controller', 'PostsController', null);
        $expected = ['', '', ''];
        $this->assertEquals($expected, $result);

        $result = $this->Task->generateConstructor('model', 'Post', null);
        $expected = ['', "ClassRegistry::init('Post');\n", ''];
        $this->assertEquals($expected, $result);

        $result = $this->Task->generateConstructor('helper', 'FormHelper', null);
        $expected = ["\$View = new View();\n", "new FormHelper(\$View);\n", ''];
        $this->assertEquals($expected, $result);
    }

    /**
     * Test generateUses()
     *
     * @return void
     */
    public function testGenerateUses(): void
    {
        $result = $this->Task->generateUses('model', 'Model', 'Post');
        $expected = [
            ['Post', 'Model'],
        ];
        $this->assertEquals($expected, $result);

        $result = $this->Task->generateUses('controller', 'Controller', 'PostsController');
        $expected = [
            ['PostsController', 'Controller'],
        ];
        $this->assertEquals($expected, $result);

        $result = $this->Task->generateUses('helper', 'View/Helper', 'FormHelper');
        $expected = [
            ['View', 'View'],
            ['Helper', 'View'],
            ['FormHelper', 'View/Helper'],
        ];
        $this->assertEquals($expected, $result);

        $result = $this->Task->generateUses('component', 'Controller/Component', 'AuthComponent');
        $expected = [
            ['ComponentCollection', 'Controller'],
            ['Component', 'Controller'],
            ['AuthComponent', 'Controller/Component'],
        ];
        $this->assertEquals($expected, $result);
    }

    /**
     * Test that mock class generation works for the appropriate classes
     *
     * @return void
     */
    public function testMockClassGeneration(): void
    {
        $result = $this->Task->hasMockClass('controller');
        $this->assertTrue($result);
    }

    /**
     * test bake() with a -plugin param
     *
     * @return void
     */
    public function testBakeWithPlugin(): void
    {
        $this->Task->plugin = 'TestTest';

        //fake plugin path
        CakePlugin::load('TestTest', ['path' => APP . 'Plugin' . DS . 'TestTest' . DS]);
        $path = APP . 'Plugin' . DS . 'TestTest' . DS . 'Test' . DS . 'Case' . DS . 'View' . DS . 'Helper' . DS . 'FormHelperTest.php';
        $this->Task->expects($this->once())->method('createFile')
            ->with($path, $this->anything());

        $this->Task->bake('Helper', 'Form');
        CakePlugin::unload();
    }

    /**
     * test interactive with plugins lists from the plugin
     *
     * @return void
     */
    public function testInteractiveWithPlugin(): void
    {
        $testApp = CORE_ROOT . DS . 'tests' . DS . 'test_app' . DS . 'Plugin' . DS;
        App::build([
            'Plugin' => [$testApp],
        ], App::RESET);
        CakePlugin::load('TestPlugin');

        $this->Task->plugin = 'TestPlugin';
        $path = $testApp . 'TestPlugin' . DS . 'Test' . DS . 'Case' . DS . 'View' . DS . 'Helper' . DS . 'OtherHelperTest.php';
        $this->Task->expects($this->any())
            ->method('in')
            ->will($this->onConsecutiveCalls(
                5, //helper
                1, //OtherHelper
            ));

        $this->Task->expects($this->once())
            ->method('createFile')
            ->with($path, $this->anything());

        $writeCalls = [];
        $this->Task->stdout->expects($this->any())
            ->method('write')
            ->willReturnCallback(function ($message) use (&$writeCalls): void {
                $writeCalls[] = $message;
            });

        $this->Task->execute();

        $this->assertArrayHasKey(21, $writeCalls);
        $this->assertEquals('1. OtherHelperHelper', $writeCalls[21]);
    }

    /**
     * @return array{array{string}}
     */
    public static function caseFileNameProvider(): array
    {
        $cases = [
            ['Model', 'Post', 'Model' . DS . 'PostTest.php'],
            ['Helper', 'Form', 'View' . DS . 'Helper' . DS . 'FormHelperTest.php'],
            ['Controller', 'Posts', 'Controller' . DS . 'PostsControllerTest.php'],
            ['Behavior', 'Containable', 'Model' . DS . 'Behavior' . DS . 'ContainableBehaviorTest.php'],
            ['Component', 'Auth', 'Controller' . DS . 'Component' . DS . 'AuthComponentTest.php'],
            ['model', 'Post', 'Model' . DS . 'PostTest.php'],
            ['helper', 'Form', 'View' . DS . 'Helper' . DS . 'FormHelperTest.php'],
            ['controller', 'Posts', 'Controller' . DS . 'PostsControllerTest.php'],
            ['behavior', 'Containable', 'Model' . DS . 'Behavior' . DS . 'ContainableBehaviorTest.php'],
            ['component', 'Auth', 'Controller' . DS . 'Component' . DS . 'AuthComponentTest.php'],
        ];

        $result = [];
        foreach ($cases as [$type, $class, $file]) {
            $result[] = [$type, $class, DS . 'my' . DS . 'path' . DS . 'tests' . DS, 'TestCase' . DS . $file];
            $result[] = [$type, $class, DS . 'my' . DS . 'path' . DS . 'Test' . DS, 'Case' . DS . $file];
        }

        return $result;
    }

    /**
     * Test filename generation for each type, for both the modern (tests/TestCase)
     * and the legacy (Test/Case) layout.
     *
     * @dataProvider caseFileNameProvider
     * @param string $type
     * @param string $class
     * @param string $path
     * @param string $expected
     * @return void
     */
    public function testTestCaseFileName(string $type, string $class, string $path, string $expected): void
    {
        $this->Task->path = $path;

        $result = $this->Task->testCaseFileName($type, $class);
        $expected = $this->Task->path . $expected;
        $this->assertEquals($expected, $result);
    }

    /**
     * Test that an existing Case or TestCase directory decides the layout.
     *
     * @return void
     */
    public function testTestCaseFileNameExistingDirectory(): void
    {
        $base = TMP . 'bake_test_task' . DS;
        $Folder = new Folder($base . 'tests' . DS . 'Case', true);

        $this->Task->path = $base . 'tests' . DS;
        $result = $this->Task->testCaseFileName('Model', 'Post');
        $this->assertEquals($base . 'tests' . DS . 'Case' . DS . 'Model' . DS . 'PostTest.php', $result);

        $Folder->create($base . 'Test' . DS . 'TestCase');
        $this->Task->path = $base . 'Test' . DS;
        $result = $this->Task->testCaseFileName('Model', 'Post');
        $this->assertEquals($base . 'Test' . DS . 'TestCase' . DS . 'Model' . DS . 'PostTest.php', $result);

        $Folder->delete($base);
    }

    /**
     * Test filename generation for plugins.
     *
     * @return void
     */
    public function testTestCaseFileNamePlugin(): void
    {
        $this->Task->path = DS . 'my' . DS . 'path' . DS . 'tests' . DS;

        CakePlugin::load('TestTest', ['path' => APP . 'Plugin' . DS . 'TestTest' . DS]);
        $this->Task->plugin = 'TestTest';
        $result = $this->Task->testCaseFileName('Model', 'Post');
        $expected = APP . 'Plugin' . DS . 'TestTest' . DS . 'Test' . DS . 'Case' . DS . 'Model' . DS . 'PostTest.php';
        $this->assertEquals($expected, $result);
    }

    /**
     * Test filename generation for plugins using the modern layout (tests/TestCase).
     *
     * @return void
     */
    public function testTestCaseFileNameModernPlugin(): void
    {
        $pluginPath = TMP . 'BakeModernPlugin' . DS;
        $Folder = new Folder($pluginPath . 'tests', true);

        CakePlugin::load('BakeModernPlugin', ['path' => $pluginPath]);
        $this->Task->plugin = 'BakeModernPlugin';
        $result = $this->Task->testCaseFileName('Model', 'Post');
        $Folder->delete($pluginPath);

        $expected = $pluginPath . 'tests' . DS . 'TestCase' . DS . 'Model' . DS . 'PostTest.php';
        $this->assertEquals($expected, $result);
    }

    /**
     * test execute with a type defined
     *
     * @return void
     */
    public function testExecuteWithOneArg(): void
    {
        $this->Task->args[0] = 'Model';
        $this->Task->expects($this->once())->method('in')->will($this->returnValue('TestTaskTag'));
        $this->Task->expects($this->once())->method('isLoadableClass')->will($this->returnValue(true));
        $this->Task->expects($this->once())->method('createFile')
            ->with(
                $this->anything(),
                $this->stringContains('class TestTaskTagTest extends CakeTestCase'),
            );
        $this->Task->execute();
    }

    /**
     * test execute with type and class name defined
     *
     * @return void
     */
    public function testExecuteWithTwoArgs(): void
    {
        $this->Task->args = ['Model', 'TestTaskTag'];
        $this->Task->expects($this->exactly(0))
            ->method('in');
        $this->Task->expects($this->once())->method('createFile')
            ->with(
                $this->anything(),
                $this->stringContains('class TestTaskTagTest extends CakeTestCase'),
            );
        $this->Task->expects($this->any())->method('isLoadableClass')->will($this->returnValue(true));
        $this->Task->execute();
    }

    /**
     * test execute with type and class name defined and lower case.
     *
     * @return void
     */
    public function testExecuteWithTwoArgsLowerCase(): void
    {
        $this->Task->args = ['model', 'TestTaskTag'];
        $this->Task->expects($this->exactly(0))
            ->method('in');
        $this->Task->expects($this->once())->method('createFile')
            ->with(
                $this->anything(),
                $this->stringContains('class TestTaskTagTest extends CakeTestCase'),
            );
        $this->Task->expects($this->any())->method('isLoadableClass')->will($this->returnValue(true));
        $this->Task->execute();
    }

    /**
     * Data provider for mapType() tests.
     *
     * @return array
     */
    public static function mapTypeProvider(): array
    {
        return [
            ['controller', null, 'Controller'],
            ['Controller', null, 'Controller'],
            ['component', null, 'Controller/Component'],
            ['Component', null, 'Controller/Component'],
            ['model', null, 'Model'],
            ['Model', null, 'Model'],
            ['behavior', null, 'Model/Behavior'],
            ['Behavior', null, 'Model/Behavior'],
            ['helper', null, 'View/Helper'],
            ['Helper', null, 'View/Helper'],
            ['Helper', 'DebugKit', 'DebugKit.View/Helper'],
        ];
    }

    /**
     * Test that mapType returns the correct package names.
     *
     * @dataProvider mapTypeProvider
     * @return void
     */
    public function testMapType($original, $plugin, $expected): void
    {
        $this->assertEquals($expected, $this->Task->mapType($original, $plugin));
    }
}
