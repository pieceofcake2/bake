<?php
/**
 * ProjectTask Test file
 *
 * Test Case for project generation shell task
 *
 * CakePHP : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link          https://cakephp.org CakePHP Project
 * @package       Cake.Test.Case.Console.Command.Task
 * @since         CakePHP v 1.3.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */

App::uses('ShellDispatcher', 'Console');
App::uses('ConsoleOutput', 'Console');
App::uses('ConsoleInput', 'Console');
App::uses('Shell', 'Console');
App::uses('ProjectTask', 'Bake.Console/Command/Task');
App::uses('Folder', 'Utility');
App::uses('File', 'Utility');

/**
 * ProjectTask Test class
 *
 * @package       Cake.Test.Case.Console.Command.Task
 */
class ProjectTaskTest extends CakeTestCase
{
    /**
     * The placeholder file that keeps an empty directory in git
     *
     * @var string
     */
    public const GITKEEP = '.gitkeep';

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
            'ProjectTask',
            ['in', 'err', 'createFile', '_stop'],
            [$out, $out, $in],
        );
        $this->Task->path = TMP . 'tests' . DS;
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        $Folder = new Folder($this->Task->path . 'bake_test_app');
        $Folder->delete();
        unset($this->Task);

        parent::tearDown();
    }

    /**
     * creates a test project that is used for testing project task.
     *
     * @return void
     */
    protected function _setupTestProject(): void
    {
        $skel = CakePlugin::path('Bake') . DS . 'Console' . DS . 'Templates' . DS . 'skel';
        $this->Task->expects($this->once())->method('in')->will($this->returnValue('y'));
        $this->Task->bake($this->Task->path . 'bake_test_app', $skel);
    }

    /**
     * test bake() method and directory creation.
     *
     * @return void
     */
    public function testBake(): void
    {
        $this->_setupTestProject();
        $path = $this->Task->path . 'bake_test_app';

        $this->assertTrue(is_dir($path), 'No project dir %s');
        // The layout of pieceofcake2/app
        $dirs = [
            'bin',
            'config',
            'config' . DS . 'Schema',
            'logs',
            'resources' . DS . 'locales' . DS . 'eng' . DS . 'LC_MESSAGES',
            'src' . DS . 'Console' . DS . 'Command' . DS . 'Task',
            'src' . DS . 'Controller' . DS . 'Component',
            'src' . DS . 'Model' . DS . 'Behavior',
            'src' . DS . 'Model' . DS . 'Datasource',
            'src' . DS . 'View' . DS . 'Helper',
            'templates' . DS . 'Error',
            'templates' . DS . 'Pages',
            'templates' . DS . 'element' . DS . 'Flash',
            'templates' . DS . 'email' . DS . 'html',
            'templates' . DS . 'layout' . DS . 'email' . DS . 'text',
            'tests' . DS . 'Fixture',
            'tests' . DS . 'TestCase' . DS . 'Controller' . DS . 'Component',
            'tests' . DS . 'TestCase' . DS . 'Model' . DS . 'Behavior',
            'tests' . DS . 'TestCase' . DS . 'View' . DS . 'Helper',
            'tmp' . DS . 'cache' . DS . 'models',
            'tmp' . DS . 'cache' . DS . 'persistent',
            'tmp' . DS . 'cache' . DS . 'views',
            'tmp' . DS . 'sessions',
            'tmp' . DS . 'tests',
            'webroot' . DS . 'css',
            'webroot' . DS . 'files',
            'webroot' . DS . 'img',
            'webroot' . DS . 'js',
        ];
        foreach ($dirs as $dir) {
            $this->assertTrue(is_dir($path . DS . $dir), 'Missing ' . $dir);
        }
        $files = [
            'bin' . DS . 'cake',
            'composer.json',
            '.gitignore',
            'config' . DS . 'core.php',
            'config' . DS . '.env',
            'src' . DS . 'Controller' . DS . 'AppController.php',
            'src' . DS . 'Controller' . DS . 'PagesController.php',
            'src' . DS . 'Model' . DS . 'AppModel.php',
            'templates' . DS . 'Pages' . DS . 'home.php',
            'templates' . DS . 'layout' . DS . 'default.php',
            'tests' . DS . 'bootstrap.php',
            'webroot' . DS . '.htaccess',
            'webroot' . DS . 'index.php',
        ];
        foreach ($files as $file) {
            $this->assertFileExists($path . DS . $file);
        }
        $this->assertStringContainsString('namespace App\Controller;', file_get_contents($path . DS . 'src' . DS . 'Controller' . DS . 'AppController.php'));
        // The .gitkeep placeholders, which keep the empty directories in git, are copied
        $this->assertFileExists($path . DS . 'src' . DS . 'Controller' . DS . 'Component' . DS . static::GITKEEP);
        $this->assertFileExists($path . DS . 'webroot' . DS . 'js' . DS . static::GITKEEP);
        $this->assertFileExists($path . DS . 'tmp' . DS . 'cache' . DS . 'models' . DS . static::GITKEEP);
    }

    /**
     * test bake with an absolute path.
     *
     * @return void
     */
    public function testExecuteWithAbsolutePath(): void
    {
        $path = $this->Task->args[0] = TMP . 'tests' . DS . 'bake_test_app';
        $this->Task->params['skel'] = CakePlugin::path('Bake') . DS . 'Console' . DS . 'Templates' . DS . 'skel';
        $this->Task->expects($this->once())->method('in')->will($this->returnValue('y'));
        $this->Task->expects($this->never())->method('err');
        $this->Task->execute();

        $this->assertTrue(is_dir($this->Task->args[0]), 'No project dir');
        $contents = file_get_contents($path . DS . 'config' . DS . 'core.php');
        $this->assertStringNotContainsString('DYhG93b0qyJfIxfs2guVoUubWwvniR2G0FgaC9mi', $contents);
        $this->assertStringNotContainsString("'2659758734'", $contents);
        $this->assertStringContainsString("\$prefix = 'bake_test_app_';", $contents);
    }

    /**
     * The baked dispatchers must load CakePHP through Composer, not through the include_path.
     *
     * @return void
     */
    public function testBakedDispatchersUseComposerAutoload(): void
    {
        $this->_setupTestProject();

        $path = $this->Task->path . 'bake_test_app' . DS;
        $files = [
            'webroot' . DS . 'index.php',
            'webroot' . DS . 'test.php',
            'bin' . DS . 'cake',
        ];
        foreach ($files as $file) {
            $contents = file_get_contents($path . $file);
            $this->assertDoesNotMatchRegularExpression("/require_once\\s+'Cake'/", $contents, $file);
            $this->assertStringContainsString("require_once VENDORS . 'autoload.php';", $contents, $file);
        }
        foreach (['index.php', 'test.php'] as $file) {
            $contents = file_get_contents($path . 'webroot' . DS . $file);
            $this->assertStringContainsString(
                "InstalledVersions::getInstallPath('pieceofcake2/cakephp') . DS . 'src' . DS . 'bootstrap.php'",
                $contents,
                $file,
            );
        }
        $this->assertStringContainsString("define('VENDORS', ROOT . DS . 'vendor' . DS);", file_get_contents($path . 'config' . DS . 'define.php'));
    }

    /**
     * test bake with CakePHP on the include path. The constants should remain commented out.
     *
     * @return void
     */
    public function testExecuteWithCakeOnIncludePath(): void
    {
        if (!function_exists('ini_set')) {
            $this->markTestAsSkipped('Not access to ini_set, cannot proceed.');
        }
        $restore = ini_get('include_path');
        ini_set('include_path', CAKE_CORE_INCLUDE_PATH . PATH_SEPARATOR . $restore);

        $path = $this->Task->args[0] = TMP . 'tests' . DS . 'bake_test_app';
        $this->Task->params['skel'] = CakePlugin::path('Bake') . DS . 'Console' . DS . 'Templates' . DS . 'skel';
        $this->Task->expects($this->once())->method('in')->will($this->returnValue('y'));
        $this->Task->execute();

        $this->assertTrue(is_dir($this->Task->args[0]), 'No project dir');
        $contents = file_get_contents($path . DS . 'webroot' . DS . 'index.php');
        $this->assertStringContainsString("require_once VENDORS . 'autoload.php';", $contents);

        ini_set('include_path', $restore);
    }

    /**
     * test bake() method with -empty flag: every empty directory of the skeleton keeps its
     * .gitkeep placeholder.
     *
     * @return void
     */
    public function testBakeEmptyFlag(): void
    {
        $this->Task->params['empty'] = true;
        $this->_setupTestProject();
        $path = $this->Task->path . 'bake_test_app';

        $dirs = [
            'logs',
            'resources' . DS . 'locales' . DS . 'eng' . DS . 'LC_MESSAGES',
            'src' . DS . 'Console' . DS . 'Command' . DS . 'Task',
            'src' . DS . 'Console' . DS . 'Templates',
            'src' . DS . 'Controller' . DS . 'Component',
            'src' . DS . 'Model' . DS . 'Behavior',
            'src' . DS . 'Model' . DS . 'Datasource',
            'src' . DS . 'View' . DS . 'Scaffolds',
            'tests' . DS . 'Fixture',
            'tests' . DS . 'TestCase' . DS . 'Controller' . DS . 'Component',
            'tests' . DS . 'TestCase' . DS . 'EndToEnd',
            'tests' . DS . 'TestCase' . DS . 'Model' . DS . 'Behavior',
            'tests' . DS . 'TestCase' . DS . 'View' . DS . 'Helper',
            'tmp' . DS . 'cache' . DS . 'models',
            'tmp' . DS . 'cache' . DS . 'persistent',
            'tmp' . DS . 'cache' . DS . 'views',
            'tmp' . DS . 'sessions',
            'tmp' . DS . 'tests',
            'webroot' . DS . 'js',
            'webroot' . DS . 'files',
        ];

        foreach ($dirs as $dir) {
            $this->assertTrue(is_file($path . DS . $dir . DS . static::GITKEEP), sprintf('Missing %s file in %s', static::GITKEEP, $dir));
        }
    }

    /**
     * Creates a skeleton with an "empty" placeholder, as older skeletons have, and a .gitkeep one.
     *
     * @return string The path of the skeleton.
     */
    protected function _setupSkeletonWithEmptyFiles(): string
    {
        $skel = $this->Task->path . 'bake_test_skel';
        $Folder = new Folder();
        $Folder->create($skel . DS . 'logs');
        $Folder->create($skel . DS . 'webroot' . DS . 'js');
        touch($skel . DS . 'logs' . DS . 'empty');
        touch($skel . DS . 'webroot' . DS . 'js' . DS . static::GITKEEP);

        return $skel;
    }

    /**
     * test that the "empty" placeholders of a skeleton are left out, and the .gitkeep ones copied,
     * without the -empty flag.
     *
     * @return void
     */
    public function testBakeSkipsEmptyPlaceholders(): void
    {
        $skel = $this->_setupSkeletonWithEmptyFiles();
        $path = $this->Task->path . 'bake_test_app';
        try {
            $this->Task->expects($this->once())->method('in')->will($this->returnValue('y'));
            $this->Task->bake($path, $skel);

            $this->assertTrue(is_dir($path . DS . 'logs'));
            $this->assertFileDoesNotExist($path . DS . 'logs' . DS . 'empty');
            $this->assertFileExists($path . DS . 'webroot' . DS . 'js' . DS . static::GITKEEP);
        } finally {
            (new Folder($skel))->delete();
        }
    }

    /**
     * test that the -empty flag also copies the "empty" placeholders of a skeleton.
     *
     * @return void
     */
    public function testBakeEmptyFlagCopiesEmptyPlaceholders(): void
    {
        $this->Task->params['empty'] = true;
        $skel = $this->_setupSkeletonWithEmptyFiles();
        $path = $this->Task->path . 'bake_test_app';
        try {
            $this->Task->expects($this->once())->method('in')->will($this->returnValue('y'));
            $this->Task->bake($path, $skel);

            $this->assertFileExists($path . DS . 'logs' . DS . 'empty');
            $this->assertFileExists($path . DS . 'webroot' . DS . 'js' . DS . static::GITKEEP);
        } finally {
            (new Folder($skel))->delete();
        }
    }

    /**
     * test generation of Security.salt
     *
     * @return void
     */
    public function testSecuritySaltGeneration(): void
    {
        $this->_setupTestProject();

        $path = $this->Task->path . 'bake_test_app' . DS;
        $result = $this->Task->securitySalt($path);
        $this->assertTrue($result);

        $File = new File($path . 'config' . DS . 'core.php');
        $contents = $File->read();
        $this->assertDoesNotMatchRegularExpression('/DYhG93b0qyJfIxfs2guVoUubWwvniR2G0FgaC9mi/', $contents, 'Default Salt left behind. %s');
        $this->assertMatchesRegularExpression("/Configure::write\\('Security.salt', env\\('SECURITY_SALT'\\) \\?: '[0-9a-f]{40,}'\\);/", $contents);
    }

    /**
     * test generation of Security.cipherSeed
     *
     * @return void
     */
    public function testSecurityCipherSeedGeneration(): void
    {
        $this->_setupTestProject();

        $path = $this->Task->path . 'bake_test_app' . DS;
        $result = $this->Task->securityCipherSeed($path);
        $this->assertTrue($result);

        $File = new File($path . 'config' . DS . 'core.php');
        $contents = $File->read();
        $this->assertDoesNotMatchRegularExpression("/'2659758734'/", $contents, 'Default CipherSeed left behind. %s');
        $this->assertMatchesRegularExpression("/Configure::write\\('Security.cipherSeed', env\\('SECURITY_CIPHER_SEED'\\) \\?: '[1-9][0-9]{0,9}'\\);/", $contents);
    }

    /**
     * test generation of cache prefix
     *
     * @return void
     */
    public function testCachePrefixGeneration(): void
    {
        $this->_setupTestProject();

        $path = $this->Task->path . 'bake_test_app' . DS;
        $result = $this->Task->cachePrefix($path);
        $this->assertTrue($result);

        $File = new File($path . 'config' . DS . 'core.php');
        $contents = $File->read();
        $this->assertMatchesRegularExpression('/\$prefix = \'.+\';/', $contents, '$prefix is not defined');
        $this->assertDoesNotMatchRegularExpression('/\$prefix = \'myapp_\';/', $contents, 'Default cache prefix left behind. %s');
    }

    /**
     * The dispatchers of the skeleton load CakePHP with Composer, so corePath() replaces nothing.
     *
     * @return void
     */
    public function testIndexPhpGeneration(): void
    {
        $this->_setupTestProject();

        $path = $this->Task->path . 'bake_test_app' . DS;
        $before = file_get_contents($path . 'webroot' . DS . 'index.php');
        $this->assertFalse($this->Task->corePath($path));
        $this->assertSame($before, file_get_contents($path . 'webroot' . DS . 'index.php'));
    }

    /**
     * test getPrefix method, and that it returns Routing.prefix or writes to config file.
     *
     * @return void
     */
    public function testGetPrefix(): void
    {
        Configure::write('Routing.prefixes', ['admin']);
        $result = $this->Task->getPrefix();
        $this->assertEquals('admin_', $result);

        Configure::write('Routing.prefixes', null);

        $this->Task->expects($this->exactly(2))
            ->method('in')
            ->willReturnOnConsecutiveCalls('y', 'super_duper_admin');

        $skel = CakePlugin::path('Bake') . DS . 'Console' . DS . 'Templates' . DS . 'skel';
        $this->Task->bake($this->Task->path . 'bake_test_app', $skel);

        $this->Task->configPath = $this->Task->path . 'bake_test_app' . DS . 'config' . DS;

        $result = $this->Task->getPrefix();
        $this->assertEquals('super_duper_admin_', $result);

        $File = new File($this->Task->configPath . 'core.php');
        $File->delete();
    }

    /**
     * test getPrefix() when core.php has no Routing.prefixes setting to enable
     *
     * @return void
     */
    public function testGetPrefixUnableToWrite(): void
    {
        Configure::write('Routing.prefixes', null);
        $path = TMP . 'tests' . DS . 'core.php';
        file_put_contents($path, "<?php\n");
        $this->Task->configPath = TMP . 'tests' . DS;

        $this->Task->expects($this->once())
            ->method('in')
            ->willReturn('admin');
        $this->Task->expects($this->once())
            ->method('_stop');

        $this->Task->getPrefix();

        $this->assertNull(Configure::read('Routing.prefixes'));
        $this->assertSame("<?php\n", file_get_contents($path));
        unlink($path);
    }

    /**
     * test cakeAdmin() writing core.php
     *
     * @return void
     */
    public function testCakeAdmin(): void
    {
        $File = new File(CONFIG . 'core.php');
        $contents = $File->read();
        $File = new File(TMP . 'tests' . DS . 'core.php');
        $File->write($contents);

        Configure::write('Routing.prefixes', null);
        $this->Task->configPath = TMP . 'tests' . DS;
        $result = $this->Task->cakeAdmin('my_prefix');
        $this->assertTrue($result);

        $this->assertEquals(Configure::read('Routing.prefixes'), ['my_prefix']);
        $this->assertStringContainsString("Configure::write('Routing.prefixes', ['my_prefix']);", $File->read());
        $File->delete();
    }

    /**
     * test cakeAdmin() with core.php files using the long and the short array syntax
     *
     * @return void
     */
    public function testCakeAdminArraySyntax(): void
    {
        $this->Task->configPath = TMP . 'tests' . DS;
        $path = TMP . 'tests' . DS . 'core.php';

        foreach (["array('admin')", "['admin']"] as $value) {
            file_put_contents($path, "<?php\n//Configure::write('Routing.prefixes', {$value});\n");
            Configure::write('Routing.prefixes', null);

            $this->assertTrue($this->Task->cakeAdmin('my_prefix'), $value);
            $this->assertSame(
                "<?php\nConfigure::write('Routing.prefixes', ['my_prefix']);\n",
                file_get_contents($path),
                $value,
            );
        }
        unlink($path);
    }

    /**
     * test getting the prefix with more than one prefix setup
     *
     * @return void
     */
    public function testGetPrefixWithMultiplePrefixes(): void
    {
        Configure::write('Routing.prefixes', ['admin', 'ninja', 'shinobi']);

        $this->Task->expects($this->exactly(2))
            ->method('in')
            ->willReturnOnConsecutiveCalls('y', 2);

        $skel = CakePlugin::path('Bake') . DS . 'Console' . DS . 'Templates' . DS . 'skel';
        $this->Task->bake($this->Task->path . 'bake_test_app', $skel);

        $this->Task->configPath = $this->Task->path . 'bake_test_app' . DS . 'config' . DS;

        $result = $this->Task->getPrefix();
        $this->assertEquals('ninja_', $result);
    }

    /**
     * Test execute method with one param to destination folder.
     *
     * @return void
     */
    public function testExecute(): void
    {
        $this->Task->params['skel'] = CakePlugin::path('Bake') . DS . 'Console' . DS . 'Templates' . DS . 'skel';
        $this->Task->params['working'] = TMP . 'tests' . DS;

        $path = $this->Task->path . 'bake_test_app';

        $this->Task->expects($this->exactly(2))
            ->method('in')
            ->willReturnOnConsecutiveCalls($path, 'y');

        $this->Task->execute();
        $this->assertTrue(is_dir($path), 'No project dir');
        $this->assertTrue(is_dir($path . DS . 'src' . DS . 'Controller'), 'No controllers dir ');
        $this->assertTrue(is_dir($path . DS . 'src' . DS . 'Controller' . DS . 'Component'), 'No components dir ');
        $this->assertTrue(is_dir($path . DS . 'src' . DS . 'Model'), 'No models dir');
        $this->assertTrue(is_dir($path . DS . 'templates'), 'No templates dir');
        $this->assertTrue(is_dir($path . DS . 'src' . DS . 'View' . DS . 'Helper'), 'No helpers dir');
        $this->assertTrue(is_dir($path . DS . 'tests' . DS . 'TestCase'), 'No test cases dir');
        $this->assertTrue(is_dir($path . DS . 'tests' . DS . 'Fixture'), 'No fixtures dir');
    }

    /**
     * test console path
     *
     * @return void
     */
    public function testConsolePath(): void
    {
        $this->_setupTestProject();

        $path = $this->Task->path . 'bake_test_app' . DS;
        // bin/cake loads CakePHP with Composer; there is no Console/cake with a placeholder
        $this->assertFalse($this->Task->consolePath($path));
        $this->assertStringNotContainsString('__CAKE_PATH__', file_get_contents($path . 'bin' . DS . 'cake'));
    }

    /**
     * test corePath() when the project sits directly in the core include path
     *
     * @return void
     */
    public function testCorePathInsideCoreIncludePath(): void
    {
        $this->assertFalse($this->Task->corePath(CAKE_CORE_INCLUDE_PATH . DS . 'app' . DS));
    }
}
