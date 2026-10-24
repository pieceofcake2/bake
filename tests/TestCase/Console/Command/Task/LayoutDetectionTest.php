<?php
/**
 * LayoutDetectionTest file
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
foreach (['FixtureTask', 'ModelTask', 'TemplateTask', 'TestTask', 'ViewTask'] as $task) {
    App::uses($task, 'Bake.Console/Command/Task');
}

/**
 * Tests that bake writes the files where the application or the plugin keeps them, in the
 * form that it has: the CakePHP 2 layout (app/), the modern layout with global classes and
 * .ctp views (pieceofcake2/app-legacy), the namespaced layout with .php views
 * (pieceofcake2/app), a namespaced plugin and a global plugin.
 */
class LayoutDetectionTest extends CakeTestCase
{
    /**
     * The view path of the CakePHP 5 layout
     *
     * @var string
     */
    protected const TEMPLATES = 'templates/';

    /**
     * The directory of the application or plugin of a test
     *
     * @var string
     */
    protected $_root;

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
        $this->_root = TMP . 'tests' . DS . 'bake_layout' . DS;
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        $Folder = new Folder($this->_root);
        $Folder->delete();
        CakePlugin::unload('BakeLayoutPlugin');

        parent::tearDown();
    }

    /**
     * Creates the files of an application or a plugin.
     *
     * @param array<string> $files The files, relative to the root.
     * @return void
     */
    protected function _files(array $files): void
    {
        foreach ($files as $file) {
            $path = $this->_root . str_replace('/', DS, $file);
            $Folder = new Folder();
            $Folder->create(dirname($path));
            touch($path);
        }
    }

    /**
     * Creates a task that records the files it bakes.
     *
     * @param string $className The class name of the task.
     * @param array<string, string> $files The baked files, by path.
     * @return BakeTask&MockObject
     */
    protected function _task(string $className, array &$files): MockObject
    {
        $out = $this->getMock('ConsoleOutput', [], [], '', false);
        $in = $this->getMock('ConsoleInput', [], [], '', false);

        $task = $this->getMock($className, ['in', 'out', 'err', 'createFile', '_stop', '_checkUnitTest'], [$out, $out, $in]);
        $task->Template = new TemplateTask($out, $out, $in);
        $task->Template->initialize();
        $task->Template->params['theme'] = 'default';
        $task->expects($this->any())->method('createFile')->willReturnCallback(function ($file, $contents) use (&$files) {
            $files[str_replace($this->_root, '', $file)] = $contents;

            return true;
        });

        return $task;
    }

    /**
     * Bakes a view into a view path.
     *
     * @param string|null $path The view path of the application, relative to the root.
     * @param string|null $plugin The plugin.
     * @return array<string> The baked files.
     */
    protected function _bakeView(?string $path, ?string $plugin = null): array
    {
        $files = [];
        $View = $this->_task('ViewTask', $files);
        $View->name = 'View';
        $View->path = $path === null ? null : $this->_root . $path;
        $View->plugin = $plugin;
        $View->controllerName = 'Posts';
        $View->bake('index', 'contents');

        return array_keys($files);
    }

    /**
     * Bakes a model test case and a fixture into a tests path.
     *
     * @param string|null $path The tests path of the application, relative to the root.
     * @param string|null $plugin The plugin.
     * @return array<string> The baked files.
     */
    protected function _bakeTests(?string $path, ?string $plugin = null): array
    {
        $files = [];
        $Test = $this->_task('TestTask', $files);
        $Test->path = $path === null ? null : $this->_root . $path;
        $Test->plugin = $plugin;
        $Test->bake('Model', 'Post');
        $Fixture = $this->_task('FixtureTask', $files);
        $Fixture->path = $path === null ? null : $this->_root . $path . 'Fixture' . DS;
        $Fixture->plugin = $plugin;
        $Fixture->generateFixtureFile('Post', []);

        return array_keys($files);
    }

    /**
     * Loads a plugin from the root.
     *
     * @return void
     */
    protected function _loadPlugin(): void
    {
        CakePlugin::load('BakeLayoutPlugin', ['path' => $this->_root]);
    }

    /**
     * The CakePHP 2 layout: app/View/ (.ctp) and app/Test/Case/, app/Test/Fixture/.
     *
     * @return void
     */
    public function testCakePhp2Layout(): void
    {
        $this->_files(['app/View/Pages/home.ctp', 'app/Test/Case/.gitkeep', 'app/Test/Fixture/.gitkeep']);

        $this->assertSame(['app' . DS . 'View' . DS . 'Posts' . DS . 'index.ctp'], $this->_bakeView('app/View/'));
        $expected = [
            'app' . DS . 'Test' . DS . 'Case' . DS . 'Model' . DS . 'PostTest.php',
            'app' . DS . 'Test' . DS . 'Fixture' . DS . 'PostFixture.php',
        ];
        $this->assertSame($expected, $this->_bakeTests('app/Test/'));
    }

    /**
     * The modern layout with global classes and .ctp views, as in pieceofcake2/app-legacy:
     * the views stay .ctp files in templates/.
     *
     * @return void
     */
    public function testModernLayoutWithCtpViews(): void
    {
        $this->_files(['templates/Layouts/default.ctp', 'templates/Pages/home.ctp', 'tests/TestCase/.gitkeep', 'tests/Fixture/.gitkeep']);

        $this->assertSame(['templates' . DS . 'Posts' . DS . 'index.ctp'], $this->_bakeView(static::TEMPLATES));
        $expected = [
            'tests' . DS . 'TestCase' . DS . 'Model' . DS . 'PostTest.php',
            'tests' . DS . 'Fixture' . DS . 'PostFixture.php',
        ];
        $this->assertSame($expected, $this->_bakeTests('tests/'));
    }

    /**
     * The namespaced layout with .php views, as in pieceofcake2/app; a new templates/ gets .php views.
     *
     * @return void
     */
    public function testNamespacedLayoutWithPhpViews(): void
    {
        Configure::write('App.namespace', 'App');
        $this->_files(['templates/layout/default.php', 'templates/Pages/home.php']);
        $this->assertSame(['templates' . DS . 'Posts' . DS . 'index.php'], $this->_bakeView(static::TEMPLATES));

        $this->assertSame(['empty' . DS . 'templates' . DS . 'Posts' . DS . 'index.php'], $this->_bakeView('empty/templates/'));
    }

    /**
     * The views of a controller decide the extension before the other views.
     *
     * @return void
     */
    public function testExtensionOfTheControllerViews(): void
    {
        $this->_files(['templates/layout/default.php', 'templates/Posts/view.ctp']);
        $this->assertSame(['templates' . DS . 'Posts' . DS . 'index.ctp'], $this->_bakeView(static::TEMPLATES));

        $this->_files(['templates/Posts/edit.php']);
        $this->assertSame(['templates' . DS . 'Posts' . DS . 'index.php'], $this->_bakeView(static::TEMPLATES));
    }

    /**
     * A namespaced plugin (src/): src/Model/, templates/ (.php) and tests/TestCase/, tests/Fixture/.
     *
     * @return void
     */
    public function testNamespacedPlugin(): void
    {
        $this->_files(['src/Controller/.gitkeep']);
        $this->_loadPlugin();

        $this->assertSame(['templates' . DS . 'Posts' . DS . 'index.php'], $this->_bakeView(null, 'BakeLayoutPlugin'));
        $expected = [
            'tests' . DS . 'TestCase' . DS . 'Model' . DS . 'PostTest.php',
            'tests' . DS . 'Fixture' . DS . 'PostFixture.php',
        ];
        $this->assertSame($expected, $this->_bakeTests(null, 'BakeLayoutPlugin'));

        $files = [];
        $Model = $this->_task('ModelTask', $files);
        $Model->name = 'Model';
        $Model->plugin = 'BakeLayoutPlugin';
        $result = $Model->bake('Post', ['associations' => [], 'validate' => []]);
        $this->assertSame(['src' . DS . 'Model' . DS . 'Post.php'], array_keys($files));
        $this->assertStringContainsString("namespace BakeLayoutPlugin\\Model;\n", $result);
    }

    /**
     * A global plugin with the CakePHP 2 layout: Model/, View/ (.ctp) and Test/Case/, Test/Fixture/.
     *
     * @return void
     */
    public function testGlobalPlugin(): void
    {
        $this->_files(['Controller/.gitkeep', 'View/Elements/.gitkeep']);
        $this->_loadPlugin();

        $this->assertSame(['View' . DS . 'Posts' . DS . 'index.ctp'], $this->_bakeView(null, 'BakeLayoutPlugin'));
        $expected = [
            'Test' . DS . 'Case' . DS . 'Model' . DS . 'PostTest.php',
            'Test' . DS . 'Fixture' . DS . 'PostFixture.php',
        ];
        $this->assertSame($expected, $this->_bakeTests(null, 'BakeLayoutPlugin'));

        $files = [];
        $Model = $this->_task('ModelTask', $files);
        $Model->name = 'Model';
        $Model->plugin = 'BakeLayoutPlugin';
        $result = $Model->bake('Post', ['associations' => [], 'validate' => []]);
        $this->assertSame(['Model' . DS . 'Post.php'], array_keys($files));
        $this->assertStringContainsString("App::uses('BakeLayoutPluginAppModel', 'BakeLayoutPlugin.Model');", $result);
    }

    /**
     * A global plugin with templates/ and tests/ (.ctp views), as a plugin of pieceofcake2/app-legacy may have.
     *
     * @return void
     */
    public function testGlobalPluginWithTemplates(): void
    {
        $this->_files(['Controller/.gitkeep', 'templates/element/box.ctp', 'tests/TestCase/.gitkeep']);
        $this->_loadPlugin();

        $this->assertSame(['templates' . DS . 'Posts' . DS . 'index.ctp'], $this->_bakeView(null, 'BakeLayoutPlugin'));
        $expected = [
            'tests' . DS . 'TestCase' . DS . 'Model' . DS . 'PostTest.php',
            'tests' . DS . 'Fixture' . DS . 'PostFixture.php',
        ];
        $this->assertSame($expected, $this->_bakeTests(null, 'BakeLayoutPlugin'));
    }
}
