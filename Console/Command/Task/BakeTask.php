<?php
/**
 * Base class for Bake Tasks.
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
 * @since         CakePHP(tm) v 1.3
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */

App::uses('AppShell', 'Console/Command');

/**
 * Base class for Bake Tasks.
 *
 * @package       Cake.Console.Command.Task
 */
class BakeTask extends AppShell
{
    /**
     * Name of plugin
     *
     * @var string
     */
    public $plugin = null;

    /**
     * The db connection being used for baking
     *
     * @var string|null
     */
    public $connection = null;

    /**
     * Path to the output directory
     *
     * @var string|null
     */
    public $path = null;

    /**
     * Flag for interactive mode
     *
     * @var bool
     */
    public $interactive = false;

    /**
     * Disable caching and enable debug for baking.
     * This forces the most current database schema to be used.
     *
     * @return void
     */
    public function startup()
    {
        Configure::write('debug', 2);
        Configure::write('Cache.disable', 1);
        parent::startup();
    }

    /**
     * Gets the path for output. Checks the plugin property
     * and returns the correct path.
     *
     * @return string Path to output.
     */
    public function getPath()
    {
        $path = $this->path;
        if (!empty($this->plugin)) {
            $path = $this->_pluginPath($this->plugin) . $this->name . DS;
        }

        return $path;
    }

    /**
     * Checks whether baked application classes need `App::uses()` to load the classes they use.
     *
     * Applications with the modern layout (`src/`) autoload their own classes and the
     * CakePHP classes with the Composer classmap, so `App::uses()` is only needed in
     * the legacy `app/` layout and for the classes of a plugin.
     *
     * @return bool
     */
    protected function _needsAppUses()
    {
        if (!empty($this->plugin)) {
            return true;
        }

        return basename(rtrim(APP, DS)) !== 'src';
    }

    /**
     * Gets the tests directory of a plugin.
     *
     * Plugins using the modern layout keep their tests in `tests/`,
     * while legacy plugins use `Test/`.
     *
     * @param string $pluginName The plugin name.
     * @return string Path to the plugin's tests directory.
     */
    protected function _pluginTestsPath($pluginName)
    {
        $pluginPath = $this->_pluginPath($pluginName);
        if (is_dir($pluginPath . 'tests')) {
            return $pluginPath . 'tests' . DS;
        }

        return $pluginPath . 'Test' . DS;
    }

    /**
     * Checks whether the given tests directory uses the modern layout
     * (`tests/TestCase`) or the legacy one (`Test/Case`).
     *
     * An existing `TestCase` or `Case` directory wins. Otherwise a directory
     * named `tests` is treated as the modern layout.
     *
     * @param string $testsPath Path to the tests directory.
     * @return bool
     */
    protected function _isModernTestsPath($testsPath)
    {
        if (is_dir($testsPath . 'TestCase')) {
            return true;
        }
        if (is_dir($testsPath . 'Case')) {
            return false;
        }

        return basename(rtrim($testsPath, DS)) === 'tests';
    }

    /**
     * Base execute method parses some parameters and sets some properties on the bake tasks.
     * call when overriding execute()
     *
     * @return void
     */
    public function execute()
    {
        foreach ($this->args as $i => $arg) {
            if (strpos($arg, '.')) {
                [$this->params['plugin'], $this->args[$i]] = pluginSplit($arg);
                break;
            }
        }
        if (isset($this->params['plugin'])) {
            $this->plugin = $this->params['plugin'];
        }
    }
}
