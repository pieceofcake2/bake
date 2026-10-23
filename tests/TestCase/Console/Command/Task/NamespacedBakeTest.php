<?php
/**
 * NamespacedBakeTest file
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */

use PHPUnit\Framework\MockObject\MockObject;

App::uses('ConsoleOutput', 'Console');
App::uses('ConsoleInput', 'Console');
App::uses('Folder', 'Utility');
foreach (['BakeTask', 'ControllerTask', 'FixtureTask', 'ModelTask', 'ProjectTask', 'TemplateTask', 'TestTask', 'ViewTask'] as $task) {
    App::uses($task, 'Bake.Console/Command/Task');
}

/**
 * A bake task that makes the sorting of the use statements public
 */
class NamespacedBakeTestTask extends BakeTask
{
    /**
     * @param array<string> $imports The fully qualified class names.
     * @return array<string>
     */
    public function sortImports(array $imports): array
    {
        return $this->_sortImports($imports);
    }
}

/**
 * Tests baking the classes of a namespaced application (App.namespace) and of a
 * namespaced plugin (a plugin with `src/`).
 *
 * The classes of the application are in tests/test_app/src/, in the App namespace.
 */
class NamespacedBakeTest extends CakeTestCase
{
    /**
     * The output directory of the baked classes
     *
     * @var string
     */
    protected const PATH = '/my/path/';

    /**
     * fixtures
     *
     * @var array
     */
    public $fixtures = [
        'plugin.Bake.bake_article', 'plugin.Bake.bake_articles_bake_tag',
        'plugin.Bake.bake_comment', 'plugin.Bake.bake_tag',
    ];

    /**
     * The directory of a namespaced plugin, made by the tests that use it
     *
     * @var string|null
     */
    protected $_pluginPath = null;

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
        Configure::write('App.namespace', 'App');
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        if ($this->_pluginPath !== null) {
            $Folder = new Folder($this->_pluginPath);
            $Folder->delete();
            $this->_pluginPath = null;
        }
        CakePlugin::unload();
        ClassRegistry::flush();

        parent::tearDown();
    }

    /**
     * Creates a task with the given methods mocked, and a template task with the default theme.
     *
     * @param string $className The class name of the task.
     * @param array<string> $methods The methods to mock.
     * @return BakeTask&MockObject
     */
    protected function _task(string $className, array $methods): MockObject
    {
        $out = $this->getMock('ConsoleOutput', [], [], '', false);
        $in = $this->getMock('ConsoleInput', [], [], '', false);

        $task = $this->getMock($className, $methods, [$out, $out, $in]);
        $task->Template = new TemplateTask($out, $out, $in);
        $task->Template->initialize();
        $task->Template->params['theme'] = 'default';

        return $task;
    }

    /**
     * Records the files that createFile() of a task is called with.
     *
     * @param BakeTask&MockObject $task The task.
     * @param array<string, string> $files The contents of the files, by path.
     * @return void
     */
    protected function _recordFiles(MockObject $task, array &$files): void
    {
        $task->expects($this->any())
            ->method('createFile')
            ->willReturnCallback(function ($path, $contents) use (&$files) {
                $files[$path] = $contents;

                return true;
            });
    }

    /**
     * Asserts that baked code matches a file of tests/bake_compare/Namespaced/.
     *
     * @param string $file The file, e.g. Model/BakeArticle.ctp.
     * @param string $result The baked code.
     * @return void
     */
    protected function _assertBaked(string $file, string $result): void
    {
        $path = dirname(__DIR__, 4) . DS . 'bake_compare' . DS . 'Namespaced' . DS . str_replace('/', DS, $file);
        $this->assertTextEquals(file_get_contents($path), $result);
    }

    /**
     * Loads a namespaced plugin, a plugin with the CakePHP 5 layout (`src/`).
     *
     * @param string $plugin The plugin name.
     * @return string The path to the plugin.
     */
    protected function _loadNamespacedPlugin(string $plugin): string
    {
        $this->_pluginPath = TMP . 'tests' . DS . $plugin . DS;
        $Folder = new Folder();
        $Folder->create($this->_pluginPath . 'src');
        CakePlugin::load($plugin, ['path' => $this->_pluginPath]);

        return $this->_pluginPath;
    }

    /**
     * A model is in App\Model and names the classes that exist with ::class.
     *
     * @return void
     */
    public function testBakeModel(): void
    {
        $Task = $this->_task('ModelTask', ['in', 'out', 'err', 'createFile', '_stop', '_checkUnitTest']);
        $Task->path = static::PATH;
        $files = [];
        $this->_recordFiles($Task, $files);

        $result = $Task->bake('BakeArticle', [
            'associations' => [
                // BakeUser has no class: the name stays a string
                'belongsTo' => [
                    ['alias' => 'BakeUser', 'className' => 'BakeUser', 'foreignKey' => 'bake_user_id'],
                ],
                'hasMany' => [
                    ['alias' => 'BakeComment', 'className' => 'BakeComment', 'foreignKey' => 'bake_article_id'],
                ],
                'hasAndBelongsToMany' => [
                    [
                        'alias' => 'BakeTag',
                        'className' => 'BakeTag',
                        'foreignKey' => 'bake_article_id',
                        'associationForeignKey' => 'bake_tag_id',
                        'joinTable' => 'bake_articles_bake_tags',
                    ],
                ],
            ],
            'actsAs' => ['Tree'],
            'validate' => ['title' => ['notBlank' => 'notBlank']],
            'displayField' => 'title',
        ]);

        $this->_assertBaked('Model/BakeArticle.ctp', $result);
        $this->assertSame([static::PATH . 'BakeArticle.php'], array_keys($files));
    }

    /**
     * A model of a namespaced plugin is in src/Model/ and in the namespace of the plugin.
     *
     * @return void
     */
    public function testBakeModelNamespacedPlugin(): void
    {
        $path = $this->_loadNamespacedPlugin('BakeNsPlugin');
        $Task = $this->_task('ModelTask', ['in', 'out', 'err', 'createFile', '_stop', '_checkUnitTest']);
        $Task->name = 'Model';
        $Task->plugin = 'BakeNsPlugin';
        $files = [];
        $this->_recordFiles($Task, $files);

        $result = $Task->bake('BakeArticle', ['associations' => [], 'validate' => []]);

        $this->assertSame([$path . 'src' . DS . 'Model' . DS . 'BakeArticle.php'], array_keys($files));
        $this->assertStringStartsWith("<?php\n\nnamespace BakeNsPlugin\\Model;\n\n/**\n", $result);
        $this->assertStringContainsString('class BakeArticle extends BakeNsPluginAppModel {', $result);
        $this->assertStringNotContainsString('App::uses(', $result);
    }

    /**
     * A controller is in App\Controller and imports its model, components, helpers
     * and exceptions.
     *
     * @return void
     */
    public function testBakeController(): void
    {
        $Task = $this->_task('ControllerTask', ['in', 'out', 'err', 'hr', 'createFile', '_stop', '_checkUnitTest']);
        $Task->name = 'Controller';
        $Task->path = static::PATH;
        $files = [];
        $this->_recordFiles($Task, $files);

        $actions = $Task->bakeActions('BakeArticles');
        // BakeMissing has no class: the name stays a string
        $result = $Task->bake('BakeArticles', $actions, ['Js', 'Time'], ['Session', 'Flash', 'BakeMissing']);

        $this->_assertBaked('Controller/BakeArticlesController.ctp', $result);
        $this->assertSame([static::PATH . 'BakeArticlesController.php'], array_keys($files));
    }

    /**
     * The components of a plugin that is not loaded keep their names in strings.
     *
     * @return void
     */
    public function testBakeControllerComponentOfPluginNotLoaded(): void
    {
        $Task = $this->_task('ControllerTask', ['in', 'out', 'err', 'hr', 'createFile', '_stop', '_checkUnitTest']);
        $Task->name = 'Controller';
        $Task->expects($this->any())->method('createFile')->will($this->returnValue(true));

        $result = $Task->bake('BakeArticles', '', [], ['BakeNotLoaded.Toolbar']);

        $this->assertStringContainsString("public \$components = array('BakeNotLoaded.Toolbar', PaginatorComponent::class);", $result);
    }

    /**
     * A scaffold controller imports nothing.
     *
     * @return void
     */
    public function testBakeControllerScaffold(): void
    {
        $Task = $this->_task('ControllerTask', ['in', 'out', 'err', 'hr', 'createFile', '_stop', '_checkUnitTest']);
        $Task->name = 'Controller';
        $Task->expects($this->any())->method('createFile')->will($this->returnValue(true));

        $result = $Task->bake('BakeArticles', 'scaffold');

        $this->_assertBaked('Controller/Scaffold.ctp', $result);
    }

    /**
     * A controller of a namespaced plugin is in src/Controller/, in the namespace of the plugin.
     *
     * @return void
     */
    public function testBakeControllerNamespacedPlugin(): void
    {
        $path = $this->_loadNamespacedPlugin('BakeNsPlugin');
        $Task = $this->_task('ControllerTask', ['in', 'out', 'err', 'hr', 'createFile', '_stop', '_checkUnitTest']);
        $Task->name = 'Controller';
        $Task->plugin = 'BakeNsPlugin';
        $files = [];
        $this->_recordFiles($Task, $files);

        $result = $Task->bake('BakeArticles', '');

        $this->assertSame([$path . 'src' . DS . 'Controller' . DS . 'BakeArticlesController.php'], array_keys($files));
        $expected = "<?php\n\nnamespace BakeNsPlugin\\Controller;\n\n" .
            "use BakeNsPlugin\\Model\\BakeArticle;\n" .
            "use Cake\\Controller\\Component\\PaginatorComponent;\n\n";
        $this->assertStringStartsWith($expected, $result);
        $this->assertStringContainsString('class BakeArticlesController extends BakeNsPluginAppController {', $result);
    }

    /**
     * A fixture is in App\Test\Fixture.
     *
     * @return void
     */
    public function testBakeFixture(): void
    {
        $Task = $this->_task('FixtureTask', ['in', 'err', 'createFile', '_stop', 'clear']);
        $Task->path = static::PATH;
        $files = [];
        $this->_recordFiles($Task, $files);

        $result = $Task->generateFixtureFile('BakeArticle', [
            'import' => "array('model' => 'BakeArticle', 'records' => true)",
            'table' => 'articles',
        ]);

        $this->_assertBaked('Fixture/BakeArticleFixture.ctp', $result);
        $this->assertSame([static::PATH . 'BakeArticleFixture.php'], array_keys($files));
    }

    /**
     * A fixture of a namespaced plugin is in tests/Fixture/, in the namespace of the plugin.
     *
     * @return void
     */
    public function testBakeFixtureNamespacedPlugin(): void
    {
        $path = $this->_loadNamespacedPlugin('BakeNsPlugin');
        $Task = $this->_task('FixtureTask', ['in', 'err', 'createFile', '_stop', 'clear']);
        $Task->plugin = 'BakeNsPlugin';
        $files = [];
        $this->_recordFiles($Task, $files);

        $result = $Task->generateFixtureFile('BakeArticle', []);

        $this->assertSame([$path . 'tests' . DS . 'Fixture' . DS . 'BakeArticleFixture.php'], array_keys($files));
        $expected = "<?php\n\nnamespace BakeNsPlugin\\Test\\Fixture;\n\n" .
            "use Cake\\TestSuite\\Fixture\\CakeTestFixture;\n\n";
        $this->assertStringStartsWith($expected, $result);
    }

    /**
     * The types of classes, their test cases and the expected files
     *
     * @return array<string, array<string>>
     */
    public static function bakeTestProvider(): array
    {
        return [
            'model' => ['Model', 'BakeArticle', 'Model' . DS . 'BakeArticleTest.php', 'Test/BakeArticleTest.ctp'],
            'controller' => [
                'Controller',
                'BakeArticles',
                'Controller' . DS . 'BakeArticlesControllerTest.php',
                'Test/BakeArticlesControllerTest.ctp',
            ],
            'component' => [
                'Component',
                'BakeExample',
                'Controller' . DS . 'Component' . DS . 'BakeExampleComponentTest.php',
                'Test/BakeExampleComponentTest.ctp',
            ],
            'helper' => [
                'Helper',
                'BakeExample',
                'View' . DS . 'Helper' . DS . 'BakeExampleHelperTest.php',
                'Test/BakeExampleHelperTest.ctp',
            ],
            'behavior' => [
                'Behavior',
                'BakeExample',
                'Model' . DS . 'Behavior' . DS . 'BakeExampleBehaviorTest.php',
                'Test/BakeExampleBehaviorTest.ctp',
            ],
        ];
    }

    /**
     * The test cases are in App\Test\TestCase, import the classes they use and find
     * the fixtures of the models.
     *
     * @param string $type The type of the class.
     * @param string $className The class name.
     * @param string $file The file of the test case in tests/TestCase/.
     * @param string $expected The expected file in tests/bake_compare/Namespaced/.
     * @return void
     * @dataProvider bakeTestProvider
     */
    public function testBakeTest(string $type, string $className, string $file, string $expected): void
    {
        $Task = $this->_task('TestTask', ['in', 'out', 'err', 'createFile', '_stop']);
        $Task->path = '/my/tests/';
        $files = [];
        $this->_recordFiles($Task, $files);

        $result = $Task->bake($type, $className);

        $this->_assertBaked($expected, $result);
        $this->assertSame(['/my/tests/TestCase/' . $file], array_keys($files));
    }

    /**
     * The test cases of a namespaced plugin are in tests/TestCase/, in the namespace of the plugin.
     *
     * @return void
     */
    public function testBakeTestNamespacedPlugin(): void
    {
        $path = $this->_loadNamespacedPlugin('BakeNsPlugin');
        $Task = $this->_task('TestTask', ['in', 'out', 'err', 'createFile', '_stop']);
        $Task->plugin = 'BakeNsPlugin';
        $files = [];
        $this->_recordFiles($Task, $files);

        $result = $Task->bake('Helper', 'BakeExample');

        $this->assertSame(
            [$path . 'tests' . DS . 'TestCase' . DS . 'View' . DS . 'Helper' . DS . 'BakeExampleHelperTest.php'],
            array_keys($files),
        );
        $expected = "<?php\n\nnamespace BakeNsPlugin\\Test\\TestCase\\View\\Helper;\n\n" .
            "use BakeNsPlugin\\View\\Helper\\BakeExampleHelper;\n" .
            "use Cake\\TestSuite\\CakeTestCase;\n" .
            "use Cake\\View\\View;\n\n";
        $this->assertStringStartsWith($expected, $result);
    }

    /**
     * The views are .php files in templates/.
     *
     * @return void
     */
    public function testBakeViews(): void
    {
        $Task = $this->_task('ViewTask', ['in', 'err', 'createFile', '_stop']);
        $Task->Project = $this->getMock('ProjectTask', [], [], '', false);
        $Task->path = '/my/templates/';
        $Task->args = ['BakeArticles'];
        $files = [];
        $this->_recordFiles($Task, $files);

        $Task->execute();

        // The actions of App\Controller\BakeArticlesController
        $this->assertSame(
            ['/my/templates/BakeArticles/index.php', '/my/templates/BakeArticles/add.php'],
            array_keys($files),
        );
        $this->_assertBaked('View/index.ctp', $files['/my/templates/BakeArticles/index.php']);
    }

    /**
     * The views of a namespaced plugin are .php files in templates/ of the plugin.
     *
     * @return void
     */
    public function testBakeViewNamespacedPlugin(): void
    {
        $path = $this->_loadNamespacedPlugin('BakeNsPlugin');
        $Task = $this->_task('ViewTask', ['in', 'err', 'createFile', '_stop']);
        $Task->plugin = 'BakeNsPlugin';
        $Task->controllerName = 'BakeArticles';
        $files = [];
        $this->_recordFiles($Task, $files);

        $Task->bake('index', 'contents');

        $this->assertSame([$path . 'templates' . DS . 'BakeArticles' . DS . 'index.php'], array_keys($files));
    }

    /**
     * The use statements are sorted as phpcs expects them.
     *
     * @return void
     */
    public function testSortImports(): void
    {
        $Task = new NamespacedBakeTestTask();
        $post = 'App\\Model\\Post';

        $result = $Task->sortImports([
            'Cake\\Controller\\ComponentCollection',
            'Cake\\Controller\\Component\\PaginatorComponent',
            $post,
            'Cake\\Controller\\Component',
            'cake\\Error\\NotFoundException',
            $post,
        ]);

        $expected = [
            $post,
            'Cake\\Controller\\Component',
            'Cake\\Controller\\Component\\PaginatorComponent',
            'Cake\\Controller\\ComponentCollection',
            'cake\\Error\\NotFoundException',
        ];
        $this->assertSame($expected, $result);
    }
}
