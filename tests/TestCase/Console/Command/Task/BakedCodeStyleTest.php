<?php
/**
 * BakedCodeStyleTest file
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
App::uses('Controller', 'Controller');
App::uses('Component', 'Controller');
App::uses('Helper', 'View');
App::uses('Model', 'Model');
App::uses('ModelBehavior', 'Model');
App::uses('Folder', 'Utility');
foreach (['ControllerTask', 'DbConfigTask', 'FixtureTask', 'ModelTask', 'PluginTask', 'TemplateTask', 'TestTask', 'ViewTask'] as $task) {
    App::uses($task, 'Bake.Console/Command/Task');
}

/**
 * A model of a global application, for the code style tests
 */
class BakeStyleArticle extends Model
{
    /**
     * @var string
     */
    public $useTable = 'bake_articles';

    /**
     * @var array
     */
    public $hasAndBelongsToMany = ['BakeTag' => ['joinTable' => 'bake_articles_bake_tags']];

    /**
     * @return void
     */
    public function publish(): void
    {
    }
}

/**
 * A controller of a global application, for the code style tests
 */
class BakeStyleArticlesController extends Controller
{
    /**
     * @var array
     */
    public $uses = ['BakeStyleArticle'];

    /**
     * @return void
     */
    public function index(): void
    {
    }
}

/**
 * A component of a global application, for the code style tests
 */
class BakeStyleComponent extends Component
{
    /**
     * @return void
     */
    public function greet(): void
    {
    }
}

/**
 * A helper of a global application, for the code style tests
 */
class BakeStyleHelper extends Helper
{
    /**
     * @return void
     */
    public function format(): void
    {
    }
}

/**
 * A behavior of a global application, for the code style tests
 */
class BakeStyleBehavior extends ModelBehavior
{
    /**
     * @return void
     */
    public function slug(): void
    {
    }
}

/**
 * Checks that the baked code passes phpcs: the CakePHP2 standard of pieceofcake2/phpcs-cakephp2 for
 * global applications, and CakePHP2Namespace for namespaced applications (App.namespace).
 */
class BakedCodeStyleTest extends CakeTestCase
{
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
     * The baked files, by their paths in the application
     *
     * @var array<string, string>
     */
    protected $_baked = [];

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
        $this->_baked = [];
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        $Folder = new Folder(TMP . 'tests' . DS . 'BakeStylePlugin');
        $Folder->delete();
        ClassRegistry::flush();

        parent::tearDown();
    }

    /**
     * The code of a global application passes the CakePHP2 standard.
     *
     * @return void
     */
    public function testGlobalApplication(): void
    {
        $this->_bakeAll('BakeStyleArticle', 'BakeStyle');

        // The legacy layout (app/), whose classes load the classes they use with App::uses()
        $Model = $this->_task('ModelTask', ['in', 'out', 'err', 'createFile', '_stop', '_checkUnitTest', '_needsAppUses'], 'app/Model/');
        $Model->expects($this->any())->method('_needsAppUses')->will($this->returnValue(true));
        $Model->bake('BakeLegacy', ['associations' => [], 'validate' => [], 'displayField' => 'title']);
        $Controller = $this->_task('ControllerTask', ['in', 'out', 'err', 'hr', 'createFile', '_stop', '_checkUnitTest', '_needsAppUses'], 'app/Controller/');
        $Controller->expects($this->any())->method('_needsAppUses')->will($this->returnValue(true));
        $Controller->bake('BakeStyleArticles', $Controller->bakeActions('BakeStyleArticles'));
        $Test = $this->_task('TestTask', ['in', 'out', 'err', 'createFile', '_stop', '_needsAppUses'], 'app/Test/');
        $Test->expects($this->any())->method('_needsAppUses')->will($this->returnValue(true));
        $Test->bake('Component', 'BakeStyle');
        $Test->bake('Model', 'BakeStyleArticle');

        $DbConfig = $this->_task('DbConfigTask', ['in', 'out', 'err', 'createFile', '_stop']);
        $DbConfig->path = TMP;
        $DbConfig->expects($this->any())->method('createFile')->willReturnCallback(function ($path, $contents) {
            $this->_baked['config/' . basename($path)] = $contents;

            return true;
        });
        $DbConfig->bake([
            ['name' => 'default', 'datasource' => 'Mysql', 'port' => 3306, 'login' => 'root', 'prefix' => 'app_', 'encoding' => 'utf8'],
            ['name' => 'test', 'datasource' => 'Postgres', 'schema' => 'public'],
        ]);

        $Plugin = $this->_task('PluginTask', ['in', 'err', 'createFile', '_stop', 'clear', 'findPath', '_modifyBootstrap']);
        $Plugin->path = TMP . 'tests' . DS;
        $Plugin->expects($this->any())->method('in')->will($this->returnValue('y'));
        $Plugin->expects($this->any())->method('createFile')->willReturnCallback(function ($path, $contents) {
            $this->_baked['plugins/' . str_replace(TMP . 'tests' . DS, '', $path)] = $contents;

            return true;
        });
        $Plugin->bake('BakeStylePlugin');

        $this->_assertCodeStyle('CakePHP2');
    }

    /**
     * The code of a namespaced application passes the CakePHP2Namespace standard.
     *
     * @return void
     */
    public function testNamespacedApplication(): void
    {
        Configure::write('App.namespace', 'App');

        $this->_bakeAll('BakeArticle', 'BakeExample');

        $this->_assertCodeStyle('CakePHP2Namespace');
    }

    /**
     * Bakes a model, its controller (with the actions, as a scaffold and with prefixed actions),
     * its views (with the default and the legacy theme), its fixture and the test cases of each type.
     *
     * @param string $model The model.
     * @param string $name The name of the component, helper and behavior.
     * @return void
     */
    protected function _bakeAll(string $model, string $name): void
    {
        $controller = Inflector::pluralize($model);

        $Model = $this->_task('ModelTask', ['in', 'out', 'err', 'createFile', '_stop', '_checkUnitTest'], 'src/Model/');
        $Model->bake($model, [
            'associations' => [
                'belongsTo' => [['alias' => 'BakeUser', 'className' => 'BakeUser', 'foreignKey' => 'bake_user_id']],
                'hasOne' => [['alias' => 'BakeProfile', 'className' => 'BakeProfile', 'foreignKey' => 'bake_article_id']],
                'hasMany' => [['alias' => 'BakeComment', 'className' => 'BakeComment', 'foreignKey' => 'bake_article_id']],
                'hasAndBelongsToMany' => [[
                    'alias' => 'BakeTag',
                    'className' => 'BakeTag',
                    'foreignKey' => 'bake_article_id',
                    'associationForeignKey' => 'bake_tag_id',
                    'joinTable' => 'bake_articles_bake_tags',
                ]],
            ],
            'actsAs' => ['Tree'],
            'validate' => ['title' => ['notBlank' => 'notBlank'], 'bake_user_id' => ['numeric' => 'numeric']],
            'primaryKey' => 'article_id',
            'useTable' => 'articles',
            'useDbConfig' => 'other',
            'displayField' => 'title',
        ]);
        $Model->bake('BakeEmpty', ['associations' => [], 'validate' => []]);

        $Controller = $this->_task('ControllerTask', ['in', 'out', 'err', 'hr', 'createFile', '_stop', '_checkUnitTest'], 'src/Controller/');
        $actions = $Controller->bakeActions($controller) . "\n" . $Controller->bakeActions($controller, 'admin_', false);
        $Controller->bake($controller, $actions, ['Js', 'Time'], ['Session', 'Flash', 'BakeMissing']);
        $Controller->bake('BakeScaffolds', 'scaffold');

        $Fixture = $this->_task('FixtureTask', ['in', 'err', 'createFile', '_stop', 'clear'], 'tests/Fixture/');
        $Fixture->connection = 'test';
        $Fixture->bake('BakeArticle', 'bake_articles');
        $Fixture->bake('BakeComment', 'bake_comments', ['schema' => 'BakeComment', 'records' => true]);

        $Test = $this->_task('TestTask', ['in', 'out', 'err', 'createFile', '_stop'], 'tests/');
        $Test->path = '/baked/tests/';
        $Test->bake('Model', $model);
        $Test->bake('Controller', $controller);
        $Test->bake('Component', $name);
        $Test->bake('Helper', $name);
        $Test->bake('Behavior', $name);

        foreach (['default', 'legacy'] as $theme) {
            $View = $this->_task('ViewTask', ['in', 'err', 'createFile', '_stop'], "templates/{$theme}/");
            $View->Template->params['theme'] = $theme;
            $View->Template->templatePaths['legacy'] = CakePlugin::path('Bake') . 'Console' . DS . 'Templates' . DS . 'legacy' . DS;
            $View->controllerName = $controller;
            $vars = [
                'modelClass' => $model,
                'schema' => [
                    'id' => ['type' => 'integer', 'null' => false],
                    'bake_user_id' => ['type' => 'integer', 'null' => false],
                    'title' => ['type' => 'string', 'null' => false],
                    'body' => ['type' => 'text', 'null' => true],
                    'rating' => ['type' => 'integer', 'null' => true],
                    'published' => ['type' => 'boolean', 'null' => false],
                    'created' => ['type' => 'datetime', 'null' => true],
                ],
                'primaryKey' => 'id',
                'displayField' => 'title',
                'singularVar' => 'bakeArticle',
                'pluralVar' => 'bakeArticles',
                'singularHumanName' => 'Bake Article',
                'pluralHumanName' => 'Bake Articles',
                'fields' => ['id', 'bake_user_id', 'title', 'body', 'rating', 'published', 'created'],
                'associations' => [
                    'belongsTo' => ['BakeUser' => [
                        'primaryKey' => 'id', 'displayField' => 'name', 'foreignKey' => 'bake_user_id',
                        'controller' => 'bake_users', 'fields' => ['id', 'name'],
                    ]],
                    'hasMany' => ['BakeComment' => [
                        'primaryKey' => 'id', 'displayField' => 'comment', 'foreignKey' => 'bake_article_id',
                        'controller' => 'bake_comments', 'fields' => ['id', 'bake_article_id', 'comment'],
                    ]],
                    'hasAndBelongsToMany' => ['BakeTag' => [
                        'primaryKey' => 'id', 'displayField' => 'tag', 'foreignKey' => 'bake_article_id',
                        'controller' => 'bake_tags', 'fields' => ['id', 'tag'],
                    ]],
                ],
            ];
            foreach (['index', 'view', 'add', 'edit'] as $action) {
                $View->bake($action, $View->getContent($action, $vars));
            }
        }
    }

    /**
     * Creates a task that records the files it bakes.
     *
     * @param string $className The class name of the task.
     * @param array<string> $methods The methods to mock.
     * @param string $path The directory of the baked files in the application.
     * @return BakeTask&MockObject
     */
    protected function _task(string $className, array $methods, string $path = ''): MockObject
    {
        $out = $this->getMock('ConsoleOutput', [], [], '', false);
        $in = $this->getMock('ConsoleInput', [], [], '', false);

        $task = $this->getMock($className, $methods, [$out, $out, $in]);
        $task->Template = new TemplateTask($out, $out, $in);
        $task->Template->initialize();
        $task->Template->params['theme'] = 'default';
        $task->path = '/baked/' . $path;
        if ($path !== '') {
            $task->expects($this->any())->method('createFile')->willReturnCallback(function ($file, $contents) {
                $this->_baked[str_replace('/baked/', '', $file)] = $contents;

                return true;
            });
        }

        return $task;
    }

    /**
     * Runs phpcs on code.
     *
     * @param string $root The root directory of the plugin.
     * @param string $phpcs The phpcs command.
     * @param string $standard The standard.
     * @param string $path The path of the code.
     * @param string $contents The code.
     * @return string The output of phpcs.
     */
    protected function _phpcs(string $root, string $phpcs, string $standard, string $path, string $contents): string
    {
        $command = [PHP_BINARY, $phpcs, '-q', '--report=emacs', '--standard=' . $standard, '--stdin-path=' . $path, '-'];
        $process = proc_open($command, [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, $root);
        fwrite($pipes[0], $contents);
        fclose($pipes[0]);
        $output = stream_get_contents($pipes[1]) . stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        proc_close($process);

        return $output;
    }

    /**
     * Asserts that all baked files pass phpcs with a standard.
     *
     * @param string $standard The standard, CakePHP2 or CakePHP2Namespace.
     * @return void
     */
    protected function _assertCodeStyle(string $standard): void
    {
        $root = dirname(__DIR__, 5);
        $phpcs = $root . DS . 'vendor' . DS . 'bin' . DS . 'phpcs';
        $this->assertFileExists($phpcs);
        $this->assertGreaterThan(15, count($this->_baked));

        $messages = [];
        // The coding standards need the dependencies that Composer installs for a recent PHP
        $check = $this->_phpcs($root, $phpcs, $standard, '/baked-app/src/Check.php', "<?php\n");
        if (str_contains($check, 'ERROR:')) {
            $this->markTestSkipped('phpcs cannot load the standard on PHP ' . PHP_VERSION . ': ' . trim($check));
        }
        // phpcs checks the files in parallel, one process for each file
        $processes = [];
        foreach ($this->_baked as $file => $contents) {
            $this->assertStringNotContainsString("\t", $contents, $file);
            // A path outside tmp/, which the standards exclude, and with the extension of PHP
            $path = '/baked-app/' . preg_replace('/\.ctp$/', '.php', $file);
            $command = [PHP_BINARY, $phpcs, '-q', '--report=emacs', '--standard=' . $standard, '--stdin-path=' . $path, '-'];
            $process = proc_open($command, [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, $root);
            fwrite($pipes[0], $contents);
            fclose($pipes[0]);
            $processes[] = [$process, $pipes];
        }
        foreach ($processes as [$process, $pipes]) {
            $output = stream_get_contents($pipes[1]) . stream_get_contents($pipes[2]);
            fclose($pipes[1]);
            fclose($pipes[2]);
            proc_close($process);
            if (trim($output) !== '') {
                $messages[] = trim($output);
            }
        }

        $this->assertSame([], $messages, implode("\n", $messages));
    }
}
