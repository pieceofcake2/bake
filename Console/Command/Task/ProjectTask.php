<?php
/**
 * The Project Task handles creating the base application
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
 * @since         CakePHP(tm) v 1.2
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */

App::uses('AppShell', 'Console/Command');
App::uses('File', 'Utility');
App::uses('Folder', 'Utility');
App::uses('CakeText', 'Utility');
App::uses('Security', 'Utility');

/**
 * Task class for creating new project apps and plugins
 *
 * @package       Cake.Console.Command.Task
 */
class ProjectTask extends AppShell
{
    /**
     * configs path (used in testing).
     *
     * @var string
     */
    public $configPath = null;

    /**
     * Checks that given project path does not already exist, and
     * finds the app directory in it. Then it calls bake() with that information.
     *
     * @return mixed
     */
    public function execute()
    {
        $project = null;
        $suggestedPath = null;
        if (isset($this->args[0])) {
            $project = $this->args[0];
        } else {
            // Next to the application that runs bake
            $suggestedPath = dirname(ROOT) . DS . 'myapp';
        }

        while (!$project) {
            $prompt = __d('cake_console', 'What is the path to the project you want to bake?');
            $project = $this->in($prompt, null, $suggestedPath);
        }

        if (!Folder::isAbsolute($project) && isset($_SERVER['PWD'])) {
            $project = $_SERVER['PWD'] . DS . $project;
        }

        $response = false;
        while (!$response && is_dir($project) === true && $this->_coreFile(Folder::slashTerm($project)) !== null) {
            $prompt = __d('cake_console', '<warning>A project already exists in this location:</warning> %s Overwrite?', $project);
            $response = $this->in($prompt, ['y', 'n'], 'n');
            if (strtolower($response) === 'n') {
                $response = $project = false;
            }
        }

        $success = true;
        if ($this->bake($project)) {
            $path = Folder::slashTerm($project);

            if ($this->securitySalt($path) === true) {
                $this->out(__d('cake_console', ' * Random hash key created for \'Security.salt\''));
            } else {
                $this->err(__d('cake_console', 'Unable to generate random hash for \'Security.salt\', you should change it in %s', CONFIG . 'core.php'));
                $success = false;
            }

            if ($this->securityCipherSeed($path) === true) {
                $this->out(__d('cake_console', ' * Random seed created for \'Security.cipherSeed\''));
            } else {
                $this->err(__d('cake_console', 'Unable to generate random seed for \'Security.cipherSeed\', you should change it in %s', CONFIG . 'core.php'));
                $success = false;
            }

            if ($this->cachePrefix($path)) {
                $this->out(__d('cake_console', ' * Cache prefix set'));
            } else {
                $this->err(__d('cake_console', 'The cache prefix was <error>NOT</error> set'));
                $success = false;
            }

            $Folder = new Folder($path);
            foreach (['tmp', 'logs'] as $directory) {
                if (is_dir($path . $directory) && !$Folder->chmod($path . $directory, 0777)) {
                    $this->err(__d('cake_console', 'Could not set permissions on %s', $path . $directory));
                    $this->out('chmod -R 0777 ' . $path . $directory);
                    $success = false;
                }
            }
            if ($success) {
                $this->out(__d('cake_console', '<success>Project baked successfully!</success>'));
                if (file_exists($path . 'composer.json')) {
                    $this->out(__d('cake_console', 'Run `composer install` in %s to install CakePHP and the other packages.', $path));
                }
            } else {
                $this->out(__d('cake_console', 'Project baked but with <warning>some issues.</warning>.'));
            }

            return $path;
        }
    }

    /**
     * Checks PHP's include_path for CakePHP.
     *
     * @return bool Indicates whether or not CakePHP exists on include_path
     * @deprecated 1.0.5 The skeleton dispatchers load CakePHP with Composer, so there is nothing to replace.
     */
    public function cakeOnIncludePath()
    {
        $paths = explode(PATH_SEPARATOR, ini_get('include_path'));
        foreach ($paths as $path) {
            if (file_exists($path . DS . 'Cake' . DS . 'bootstrap.php')) {
                return true;
            }
        }

        return false;
    }

    /**
     * Looks for a skeleton template of a Cake application,
     * and if not found asks the user for a path. When there is a path
     * this method will make a deep copy of the skeleton to the project directory.
     *
     * @param string $path Project path
     * @param string $skel Path to copy from
     * @param array $skip array of directories to skip when copying
     * @return mixed
     */
    public function bake($path, $skel = null, $skip = ['empty'])
    {
        if (!$skel && !empty($this->params['skel'])) {
            $skel = $this->params['skel'];
        }
        while (!$skel) {
            $skel = $this->in(
                __d('cake_console', 'What is the path to the directory layout you wish to copy?'),
                null,
                CakePlugin::path('Bake') . DS . 'Console' . DS . 'Templates' . DS . 'skel',
            );
            if (!$skel) {
                $this->err(__d('cake_console', 'The directory path you supplied was empty. Please try again.'));
            } else {
                while (is_dir($skel) === false) {
                    $skel = $this->in(
                        __d('cake_console', 'Directory path does not exist please choose another:'),
                        null,
                        CakePlugin::path('Bake') . DS . 'Console' . DS . 'Templates' . DS . 'skel',
                    );
                }
            }
        }

        $app = basename($path);

        $this->out(__d('cake_console', '<info>Skel Directory</info>: ') . $skel);
        $this->out(__d('cake_console', '<info>Will be copied to</info>: ') . $path);
        $this->hr();

        $looksGood = $this->in(__d('cake_console', 'Look okay?'), ['y', 'n', 'q'], 'y');

        switch (strtolower($looksGood)) {
            case 'y':
                $Folder = new Folder($skel);
                if (!empty($this->params['empty'])) {
                    $skip = [];
                }

                if ($Folder->copy(['to' => $path, 'skip' => $skip])) {
                    $this->hr();
                    $this->out(__d('cake_console', '<success>Created:</success> %s in %s', $app, $path));
                    $this->hr();
                } else {
                    $this->err(__d('cake_console', "<error>Could not create</error> '%s' properly.", $app));

                    return false;
                }

                foreach ($Folder->messages() as $message) {
                    $this->out(CakeText::wrap(' * ' . $message), 1, Shell::VERBOSE);
                }

                return true;
            case 'n':
                unset($this->args[0]);
                $this->execute();

                return false;
            case 'q':
                $this->out(__d('cake_console', '<error>Bake Aborted.</error>'));

                return false;
        }
    }

    /**
     * Generates the correct path to the CakePHP libs that are generating the project
     * and points app/console/cake to the right place
     *
     * @param string $path Project path.
     * @return bool success
     * @deprecated 1.0.5 The skeleton dispatchers load CakePHP with Composer, so there is nothing to replace.
     */
    public function consolePath($path)
    {
        if (!is_file($path . 'Console' . DS . 'cake')) {
            return false;
        }
        $File = new File($path . 'Console' . DS . 'cake');
        $contents = $File->read();
        if (preg_match('/(__CAKE_PATH__)/', $contents, $match)) {
            $root = str_starts_with(CAKE_CORE_INCLUDE_PATH, '/') ? " DS . '" : "'";
            $replacement = $root . str_replace(DS, "' . DS . '", trim(CAKE_CORE_INCLUDE_PATH, DS)) . "'";
            $result = str_replace($match[0], $replacement, $contents);
            if ($File->write($result)) {
                return true;
            }

            return false;
        }

        return false;
    }

    /**
     * Gets the core.php of a project: `config/core.php` of the CakePHP 5 layout, or
     * `Config/core.php` of a skeleton with the CakePHP 2 layout.
     *
     * @param string $path Project path
     * @return string|null The file, or null when there is none.
     */
    protected function _coreFile($path)
    {
        foreach (['config', 'Config'] as $directory) {
            if (is_file($path . $directory . DS . 'core.php')) {
                return $path . $directory . DS . 'core.php';
            }
        }

        return null;
    }

    /**
     * Replaces the value of a setting of core.php: the default after `env('NAME') ?:` of the
     * skeleton, or the value of `Configure::write()` of a skeleton without environment variables.
     *
     * @param string $path Project path
     * @param string $name The name of the setting, e.g. `Security.salt`.
     * @param string $value The new value.
     * @return bool Success
     */
    protected function _writeCoreValue($path, $name, $value)
    {
        $file = $this->_coreFile($path);
        if ($file === null) {
            return false;
        }
        $File = new File($file);
        $contents = $File->read();
        $pattern = '/(Configure::write\(\'' . preg_quote($name, '/') . '\',\s*(?:env\(\'[A-Z_]+\'\)\s*\?:\s*)?)\'[A-Za-z0-9]*\'(\);)/';
        $result = preg_replace($pattern, '${1}\'' . $value . '\'${2}', $contents, 1, $count);
        if (!$count) {
            return false;
        }

        return $File->write($result);
    }

    /**
     * Generates and writes 'Security.salt'
     *
     * @param string $path Project path
     * @return bool Success
     */
    public function securitySalt($path)
    {
        return $this->_writeCoreValue($path, 'Security.salt', Security::generateAuthKey());
    }

    /**
     * Generates and writes 'Security.cipherSeed': a number from 1 to 4294967295, which
     * Security::cipher() uses as a seed.
     *
     * @param string $path Project path
     * @return bool Success
     */
    public function securityCipherSeed($path)
    {
        return $this->_writeCoreValue($path, 'Security.cipherSeed', (string)random_int(1, 4294967295));
    }

    /**
     * Writes cache prefix using app's name
     *
     * @param string $dir Path to project
     * @return bool Success
     */
    public function cachePrefix($dir)
    {
        $app = basename($dir);
        $file = $this->_coreFile($dir);
        if ($file === null) {
            return false;
        }
        $File = new File($file);
        $contents = $File->read();
        if (preg_match('/(\$prefix = \'myapp_\';)/', $contents, $match)) {
            $result = str_replace($match[0], '$prefix = \'' . $app . '_\';', $contents);

            return $File->write($result);
        }

        return false;
    }

    /**
     * Generates and writes CAKE_CORE_INCLUDE_PATH
     *
     * @param string $path Project path
     * @param bool $hardCode Whether or not define calls should be hardcoded.
     * @return bool Success
     * @deprecated 1.0.5 The skeleton dispatchers load CakePHP with Composer, so there is nothing to replace.
     */
    public function corePath($path, $hardCode = true)
    {
        if (dirname($path) !== CAKE_CORE_INCLUDE_PATH) {
            $filename = $path . 'webroot' . DS . 'index.php';
            if (!$this->_replaceCorePath($filename, $hardCode)) {
                return false;
            }
            $filename = $path . 'webroot' . DS . 'test.php';
            if (!$this->_replaceCorePath($filename, $hardCode)) {
                return false;
            }

            return true;
        }

        return false;
    }

    /**
     * Replaces the __CAKE_PATH__ placeholder in the template files.
     *
     * @param string $filename The filename to operate on.
     * @param bool $hardCode Whether or not the define should be uncommented.
     * @return bool Success
     */
    protected function _replaceCorePath($filename, $hardCode)
    {
        $contents = file_get_contents($filename);

        $root = str_starts_with(CAKE_CORE_INCLUDE_PATH, '/') ? " DS . '" : "'";
        $corePath = $root . str_replace(DS, "' . DS . '", trim(CAKE_CORE_INCLUDE_PATH, DS)) . "'";

        $composer = ROOT . DS . 'vendor' . DS . 'pieceofcake2' . DS . 'cakephp' . DS . 'lib';
        if (file_exists($composer)) {
            $corePath = " ROOT . DS . 'vendor' . DS . 'pieceofcake2' . DS . 'cakephp' . DS . 'lib'";
        }

        $result = str_replace('__CAKE_PATH__', $corePath, $contents, $count);
        if ($hardCode) {
            $result = str_replace('//define(\'CAKE_CORE', 'define(\'CAKE_CORE', $result);
        }
        if (!file_put_contents($filename, $result)) {
            return false;
        }

        return (bool)$count;
    }

    /**
     * Enables Configure::read('Routing.prefixes') in core.php of the application's config directory
     *
     * @param string $name Name to use as admin routing
     * @return bool Success
     */
    public function cakeAdmin($name)
    {
        $path = empty($this->configPath) ? CONFIG : $this->configPath;
        $File = new File($path . 'core.php');
        $contents = $File->read();
        if (preg_match('%(\s*[/]*Configure::write\(\'Routing.prefixes\',[\s\'a-z,\)\(\[\]]*\);)%', $contents, $match)) {
            $result = str_replace($match[0], "\n" . 'Configure::write(\'Routing.prefixes\', [\'' . $name . '\']);', $contents);
            if ($File->write($result)) {
                Configure::write('Routing.prefixes', [$name]);

                return true;
            }
        }

        return false;
    }

    /**
     * Checks for Configure::read('Routing.prefixes') and forces user to input it if not enabled
     *
     * @return string|null Admin route to use, or null when the prefix could not be written.
     */
    public function getPrefix()
    {
        $admin = '';
        $prefixes = Configure::read('Routing.prefixes');
        if (!empty($prefixes)) {
            if (count($prefixes) === 1) {
                return $prefixes[0] . '_';
            }
            if ($this->interactive) {
                $this->out();
                $this->out(__d('cake_console', 'You have more than one routing prefix configured'));
            }
            $options = [];
            foreach ($prefixes as $i => $prefix) {
                $options[] = $i + 1;
                if ($this->interactive) {
                    $this->out($i + 1 . '. ' . $prefix);
                }
            }
            $selection = $this->in(__d('cake_console', 'Please choose a prefix to bake with.'), $options, '1');

            return $prefixes[$selection - 1] . '_';
        }
        if ($this->interactive) {
            $this->hr();
            $this->out(__d(
                'cake_console',
                'You need to enable %s in %s to use prefix routing.',
                'Configure::write(\'Routing.prefixes\', [\'admin\'])',
                (empty($this->configPath) ? CONFIG : $this->configPath) . 'core.php',
            ));
            $this->out(__d('cake_console', 'What would you like the prefix route to be?'));
            $this->out(__d('cake_console', 'Example: %s', 'www.example.com/admin/controller'));
            while (!$admin) {
                $admin = $this->in(__d('cake_console', 'Enter a routing prefix:'), null, 'admin');
            }
            if ($this->cakeAdmin($admin) !== true) {
                $this->out(__d('cake_console', '<error>Unable to write to</error> %s.', (empty($this->configPath) ? CONFIG : $this->configPath) . 'core.php'));
                $this->out(__d(
                    'cake_console',
                    'You need to enable %s in %s to use prefix routing.',
                    'Configure::write(\'Routing.prefixes\', [\'admin\'])',
                    (empty($this->configPath) ? CONFIG : $this->configPath) . 'core.php',
                ));
                $this->_stop();

                return null;
            }

            return $admin . '_';
        }

        return '';
    }

    /**
     * Gets the option parser instance and configures it.
     *
     * @return ConsoleOptionParser
     */
    public function getOptionParser()
    {
        $parser = parent::getOptionParser();

        $parser->description(
            __d('cake_console', 'Generate a new CakePHP project skeleton.'),
        )->addArgument('name', [
            'help' => __d('cake_console', 'Application directory to make, if it starts with "/" the path is absolute.'),
        ])->addOption('empty', [
            'boolean' => true,
            'help' => __d('cake_console', 'Create empty files in each of the directories. Good if you are using git'),
        ])->addOption('theme', [
            'short' => 't',
            'help' => __d('cake_console', 'Theme to use when baking code.'),
        ])->addOption('skel', [
            'default' => CakePlugin::path('Bake') . DS . 'Console' . DS . 'Templates' . DS . 'skel',
            'help' => __d('cake_console', 'The directory layout to use for the new application skeleton.' .
                ' Defaults to Console/Templates/skel of the Bake plugin, the skeleton of pieceofcake2/app.'),
        ]);

        return $parser;
    }
}
