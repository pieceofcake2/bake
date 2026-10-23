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
            $path = $this->_pluginPath($this->plugin);
            if ($this->_namespace() !== null) {
                $path .= 'src' . DS;
            }
            $path .= $this->name . DS;
        }

        return $path;
    }

    /**
     * Checks whether baked application classes need `App::uses()` to load the classes they use.
     *
     * Applications with the modern layout (`src/`) autoload their own classes and the
     * CakePHP classes with the Composer classmap, so `App::uses()` is only needed in
     * the legacy `app/` layout and for the classes of a plugin. Namespaced classes
     * import the classes they use with `use` statements instead.
     *
     * @return bool
     */
    protected function _needsAppUses()
    {
        if ($this->_namespace() !== null) {
            return false;
        }
        if (!empty($this->plugin)) {
            return true;
        }

        return basename(rtrim(APP, DS)) !== 'src';
    }

    /**
     * Gets the namespace of the baked classes.
     *
     * The classes of the application are in the namespace set in `App.namespace`. The classes
     * of a plugin with the CakePHP 5 layout (a `src/` directory) are in the namespace of the
     * plugin name, as CakePHP 2.13 finds them. Other classes are global.
     *
     * @return string|null The namespace, e.g. `App`, or null for global classes.
     */
    protected function _namespace()
    {
        if (!empty($this->plugin)) {
            return is_dir($this->_pluginPath($this->plugin) . 'src') ? $this->plugin : null;
        }

        $namespace = Configure::read('App.namespace');
        if (!is_string($namespace) || trim($namespace, '\\') === '') {
            return null;
        }

        return trim($namespace, '\\');
    }

    /**
     * Finds a class of the application, or of the plugin being baked.
     *
     * Global classes are declared with App::uses() in the package; for the namespaced classes
     * App::className() looks in the namespace of the application or the plugin.
     *
     * @param string $name The class name without the plugin, e.g. `Post` or `Posts`.
     * @param string $package The package of the class, e.g. `Model`.
     * @param string $suffix The suffix of the class name, e.g. `Controller`.
     * @param bool $plugin Whether the class is looked up in the plugin being baked.
     * @return string|null The class name, or null when the class does not exist.
     */
    protected function _appClass($name, $package, $suffix = '', $plugin = false)
    {
        $pluginPath = $plugin && $this->plugin ? $this->plugin . '.' : '';
        $namespace = $this->_namespace();
        if ($namespace !== null) {
            $className = App::className($pluginPath . $name, $package, $suffix);
            if ($className === null && (!$this->plugin || $plugin)) {
                $className = $namespace . '\\' . str_replace('/', '\\', $package) . '\\' . $name . $suffix;
                $className = $this->_classExists($className) ? $className : null;
            }

            return $className;
        }
        App::uses($name . $suffix, $pluginPath . $package);

        return class_exists($name . $suffix) ? $name . $suffix : null;
    }

    /**
     * Checks whether a class of the namespace of the baked classes exists, also when bake has
     * just written it: Composer remembers the classes that it did not find, so this loads the
     * file of the class from `src/` when Composer does not.
     *
     * @param string $className The fully qualified class name, e.g. `App\Model\Post`.
     * @return bool
     */
    protected function _classExists($className)
    {
        if (class_exists($className)) {
            return true;
        }
        $namespace = $this->_namespace();
        if ($namespace === null || !str_starts_with($className, $namespace . '\\')) {
            return false;
        }
        $path = $this->plugin ? $this->_pluginPath($this->plugin) . 'src' . DS : APP;
        $file = $path . str_replace('\\', DS, substr($className, strlen($namespace) + 1)) . '.php';
        if (is_file($file)) {
            require_once $file;
        }

        return class_exists($className, false);
    }

    /**
     * Gets the namespaced name of a class from the name used in CakePHP, as App::className() finds it,
     * e.g. `Cake\Controller\Component\PaginatorComponent` for `Paginator` in `Controller/Component`.
     *
     * @param string $name The class name, e.g. `Paginator` or `MyPlugin.Post`.
     * @param string $package The package of the class, e.g. `Controller/Component`.
     * @param string $suffix The suffix of the class name, e.g. `Component`.
     * @return string|null The class name, or null when the class does not exist or is not in a namespace.
     */
    protected function _namespacedClass($name, $package, $suffix = '')
    {
        try {
            $className = App::className($name, $package, $suffix);
        } catch (MissingPluginException) {
            // A Plugin.Name of a plugin that is not loaded
            return null;
        }
        if ($className === null) {
            return null;
        }
        // The global names of the CakePHP classes are aliases of the namespaced classes
        $className = (new ReflectionClass($className))->getName();
        if (!str_contains($className, '\\')) {
            return null;
        }

        return $className;
    }

    /**
     * Adds a class to the `use` statements of a baked file and returns the name to write in the code.
     *
     * A class of the namespace of the file is not imported. A class whose short name another
     * class of the file already has is written with its fully qualified name.
     *
     * @param string $className The fully qualified class name.
     * @param string $namespace The namespace of the baked file.
     * @param array<string, string> $imports The imported classes, by short name.
     * @return string The name of the class to write in the code.
     */
    protected function _importClass($className, $namespace, array &$imports)
    {
        $className = ltrim($className, '\\');
        $position = strrpos($className, '\\');
        if ($position === false) {
            return $className;
        }
        $shortName = substr($className, $position + 1);
        if (substr($className, 0, $position) === $namespace) {
            return $shortName;
        }
        if (isset($imports[$shortName]) && $imports[$shortName] !== $className) {
            return '\\' . $className;
        }
        $imports[$shortName] = $className;

        return $shortName;
    }

    /**
     * Sorts the classes of `use` statements as phpcs (SlevomatCodingStandard.Namespaces.AlphabeticallySortedUses)
     * expects them: by the parts of the names, case-insensitively.
     *
     * @param array<string> $imports The fully qualified class names.
     * @return array<string> The sorted class names.
     */
    protected function _sortImports(array $imports)
    {
        $imports = array_values(array_unique($imports));
        usort($imports, function ($a, $b) {
            $aParts = explode('\\', $a);
            $bParts = explode('\\', $b);
            $count = min(count($aParts), count($bParts));
            for ($i = 0; $i < $count; $i++) {
                $comparison = strcasecmp($aParts[$i], $bParts[$i]);
                if ($comparison !== 0) {
                    return $comparison;
                }
            }

            return count($aParts) <=> count($bParts);
        });

        return $imports;
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
        // The namespaced classes of the tests are in tests/ (<Plugin>\Test\), as in CakePHP 5
        if (is_dir($pluginPath . 'tests') || is_dir($pluginPath . 'src')) {
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
