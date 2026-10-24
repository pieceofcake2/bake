<?php
/**
 * The View Tasks handles creating and updating view files.
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

/**
 * Task class for creating and updating view files.
 *
 * @package       Cake.Console.Command.Task
 * @property ProjectTask $Project
 * @property ControllerTask $Controller
 * @property DbConfigTask $DbConfig
 * @property TemplateTask $Template
 */
class ViewTask extends BakeTask
{
    /**
     * Tasks to be loaded by this Task
     *
     * @var array
     */
    public $tasks = ['Bake.Project', 'Bake.Controller', 'Bake.DbConfig', 'Bake.Template'];

    /**
     * path to View directory
     *
     * @var string|null
     */
    public $path = null;

    /**
     * Name of the controller being used
     *
     * @var string
     */
    public $controllerName = null;

    /**
     * The template file to use
     *
     * @var string
     */
    public $template = null;

    /**
     * Actions to use for scaffolding
     *
     * @var array
     */
    public $scaffoldActions = ['index', 'view', 'add', 'edit'];

    /**
     * An array of action names that don't require templates. These
     * actions will not emit errors when doing bakeActions()
     *
     * @var array
     */
    public $noTemplateActions = ['delete'];

    /**
     * Override initialize
     *
     * @return void
     */
    public function initialize()
    {
        $paths = App::path('View');
        $this->path = current($paths);

        // Prefer the top-level templates/ directory used by the modern layout.
        $templates = ROOT . DS . 'templates' . DS;
        if (in_array($templates, $paths, true) && is_dir($templates)) {
            $this->path = $templates;
        }
    }

    /**
     * Execution method always used for tasks
     *
     * @return mixed
     */
    public function execute()
    {
        parent::execute();
        if (empty($this->args)) {
            $this->_interactive();
        }
        if (empty($this->args[0])) {
            return null;
        }
        if (!isset($this->connection)) {
            $this->connection = 'default';
        }
        $action = null;
        $this->controllerName = $this->_controllerName($this->args[0]);

        $this->Project->interactive = false;
        if (strtolower($this->args[0]) === 'all') {
            $this->all();

            return null;
        }

        if (isset($this->args[1])) {
            $this->template = $this->args[1];
        }
        if (isset($this->args[2])) {
            $action = $this->args[2];
        }
        if (!$action) {
            $action = $this->template;
        }
        if ($action) {
            return $this->bake($action, true);
        }

        $vars = $this->_loadController();
        $methods = $this->_methodsToBake();

        foreach ($methods as $method) {
            $content = $this->getContent($method, $vars);
            if ($content) {
                $this->bake($method, $content);
            }
        }
    }

    /**
     * Get a list of actions that can / should have views baked for them.
     *
     * @return array Array of action names that should be baked
     */
    protected function _methodsToBake()
    {
        $methods = array_diff(
            array_map('strtolower', get_class_methods($this->controllerName . 'Controller')),
            array_map('strtolower', get_class_methods('AppController')),
        );
        $scaffoldActions = false;
        if (empty($methods)) {
            $scaffoldActions = true;
            $methods = $this->scaffoldActions;
        }
        $adminRoute = $this->Project->getPrefix();
        foreach ($methods as $i => $method) {
            if ($adminRoute && !empty($this->params['admin'])) {
                if ($scaffoldActions) {
                    $methods[$i] = $adminRoute . $method;
                    continue;
                } elseif (!str_contains($method, $adminRoute)) {
                    unset($methods[$i]);
                }
            }
            if ($method[0] === '_' || $method === strtolower($this->controllerName . 'Controller')) {
                unset($methods[$i]);
            }
        }

        return $methods;
    }

    /**
     * Bake All views for All controllers.
     *
     * @return void
     */
    public function all()
    {
        $this->Controller->interactive = false;
        $tables = $this->Controller->listAll($this->connection);

        $actions = null;
        if (isset($this->args[1])) {
            $actions = [$this->args[1]];
        }
        $this->interactive = false;
        foreach ($tables as $table) {
            $model = $this->_modelName($table);
            $this->controllerName = $this->_controllerName($model);
            if (static::classExists($model, 'Model')) {
                $vars = $this->_loadController();
                if (!$actions) {
                    $actions = $this->_methodsToBake();
                }
                $this->bakeActions($actions, $vars);
                $actions = null;
            }
        }
    }

    /**
     * Handles interactive baking
     *
     * @return void
     */
    protected function _interactive()
    {
        $this->hr();
        $this->out(sprintf("Bake View\nPath: %s", $this->getPath()));
        $this->hr();

        $this->DbConfig->interactive = $this->Controller->interactive = $this->interactive = true;

        if (empty($this->connection)) {
            $this->connection = $this->DbConfig->getConfig();
        }

        $this->Controller->connection = $this->connection;
        $this->controllerName = $this->Controller->getName();

        $prompt = __d('cake_console', "Would you like bake to build your views interactively?\nWarning: Choosing no will overwrite %s views if they exist.", $this->controllerName);
        $interactive = $this->in($prompt, ['y', 'n'], 'n');

        if (strtolower($interactive) === 'n') {
            $this->interactive = false;
        }

        $prompt = __d('cake_console', "Would you like to create some CRUD views\n(index, add, view, edit) for this controller?\nNOTE: Before doing so, you'll need to create your controller\nand model classes (including associated models).");
        $wannaDoScaffold = $this->in($prompt, ['y', 'n'], 'y');

        $wannaDoAdmin = $this->in(__d('cake_console', 'Would you like to create the views for admin routing?'), ['y', 'n'], 'n');

        if (strtolower($wannaDoScaffold) === 'y' || strtolower($wannaDoAdmin) === 'y') {
            $vars = $this->_loadController();
            if (strtolower($wannaDoScaffold) === 'y') {
                $actions = $this->scaffoldActions;
                $this->bakeActions($actions, $vars);
            }
            if (strtolower($wannaDoAdmin) === 'y') {
                $admin = $this->Project->getPrefix();
                $regularActions = $this->scaffoldActions;
                $adminActions = [];
                foreach ($regularActions as $action) {
                    $adminActions[] = $admin . $action;
                }
                $this->bakeActions($adminActions, $vars);
            }
            $this->hr();
            $this->out();
            $this->out(__d('cake_console', "View Scaffolding Complete.\n"));
        } else {
            $this->customAction();
        }
    }

    /**
     * Loads Controller and sets variables for the template
     * Available template variables
     *  'modelClass', 'primaryKey', 'displayField', 'singularVar', 'pluralVar',
     *  'singularHumanName', 'pluralHumanName', 'fields', 'foreignKeys',
     *  'belongsTo', 'hasOne', 'hasMany', 'hasAndBelongsToMany'
     *
     * @return array|null Returns a variables to be made available to a view template, or null when the controller does not exist
     */
    protected function _loadController()
    {
        if (!$this->controllerName) {
            $this->err(__d('cake_console', 'Controller not found'));
        }

        $plugin = null;
        if ($this->plugin) {
            $plugin = $this->plugin . '.';
        }

        $controllerClassName = $this->controllerName . 'Controller';
        if (!static::classExists($controllerClassName, $plugin . 'Controller')) {
            $file = $controllerClassName . '.php';
            $this->err(__d('cake_console', "The file '%s' could not be found.\nIn order to bake a view, you'll need to first create the controller.", $file));
            $this->_stop();

            return null;
        }
        $controllerObj = new $controllerClassName();
        $controllerObj->plugin = $this->plugin;
        $controllerObj->constructClasses();
        $modelClass = $controllerObj->modelClass;
        $modelObj = $controllerObj->{$controllerObj->modelClass};

        if ($modelObj) {
            $primaryKey = $modelObj->primaryKey;
            $displayField = $modelObj->displayField;
            $singularVar = Inflector::variable($modelClass);
            $singularHumanName = $this->_singularHumanName($this->controllerName);
            $schema = $modelObj->schema(true);
            $fields = array_keys($schema);
            $associations = $this->_associations($modelObj);
        } else {
            $primaryKey = $displayField = null;
            $singularVar = Inflector::variable(Inflector::singularize($this->controllerName));
            $singularHumanName = $this->_singularHumanName($this->controllerName);
            $fields = $schema = $associations = [];
        }
        $pluralVar = Inflector::variable($this->controllerName);
        $pluralHumanName = $this->_pluralHumanName($this->controllerName);

        return compact(
            'modelClass',
            'schema',
            'primaryKey',
            'displayField',
            'singularVar',
            'pluralVar',
            'singularHumanName',
            'pluralHumanName',
            'fields',
            'associations',
        );
    }

    /**
     * Bake a view file for each of the supplied actions
     *
     * @param array $actions Array of actions to make files for.
     * @param array $vars The template variables.
     * @return void
     */
    public function bakeActions($actions, $vars)
    {
        foreach ($actions as $action) {
            $content = $this->getContent($action, $vars);
            $this->bake($action, $content);
        }
    }

    /**
     * handle creation of baking a custom action view file
     *
     * @return void
     */
    public function customAction()
    {
        $action = '';
        while (!$action) {
            $action = $this->in(__d('cake_console', 'Action Name? (use lowercase_underscored function name)'));
            if (!$action) {
                $this->out(__d('cake_console', 'The action name you supplied was empty. Please try again.'));
            }
        }
        $this->out();
        $this->hr();
        $this->out(__d('cake_console', 'The following view will be created:'));
        $this->hr();
        $this->out(__d('cake_console', 'Controller Name: %s', $this->controllerName));
        $this->out(__d('cake_console', 'Action Name:     %s', $action));
        $this->out(__d('cake_console', 'Path:            %s', $this->getPath() . $this->controllerName . DS . Inflector::underscore($action) . '.ctp'));
        $this->hr();
        $looksGood = $this->in(__d('cake_console', 'Look okay?'), ['y', 'n'], 'y');
        if (strtolower($looksGood) === 'y') {
            $this->bake($action, ' ');
            $this->_stop();

            return;
        }
        $this->out(__d('cake_console', 'Bake Aborted.'));
    }

    /**
     * Assembles and writes bakes the view file.
     *
     * @param string $action Action to bake
     * @param string|bool $content Content to write, or true to generate it from the template
     * @return bool Success
     */
    public function bake($action, $content = '')
    {
        if ($content === true) {
            $content = $this->getContent($action);
        }
        if (empty($content)) {
            return false;
        }
        $this->out("\n" . __d('cake_console', 'Baking `%s` view file...', $action), 1, Shell::QUIET);
        $path = $this->getPath();
        $filename = $path . $this->controllerName . DS . Inflector::underscore($action) . '.ctp';

        return $this->createFile($filename, $content);
    }

    /**
     * Builds content from template and variables
     *
     * @param string $action name to generate content to
     * @param array $vars passed for use in templates
     * @return string|false content from template, or false when there is no template
     */
    public function getContent($action, $vars = null)
    {
        if (!$vars) {
            $vars = $this->_loadController();
        }

        $this->Template->set('action', $action);
        $this->Template->set('plugin', $this->plugin);
        $this->Template->set($vars);
        $template = $this->getTemplate($action);
        if ($template) {
            return $this->Template->generate('views', $template);
        }

        return false;
    }

    /**
     * Gets the template name based on the action name
     *
     * @param string $action name
     * @return string|false template name, or false when the action needs no template
     */
    public function getTemplate($action)
    {
        if ($action != $this->template && in_array($action, $this->noTemplateActions)) {
            return false;
        }
        if (!empty($this->template) && $action != $this->template) {
            return $this->template;
        }
        $themePath = $this->Template->getThemePath();
        if (file_exists($themePath . 'views' . DS . $action . '.ctp')) {
            return $action;
        }
        $template = $action;
        $prefixes = Configure::read('Routing.prefixes');
        foreach ((array)$prefixes as $prefix) {
            if (str_contains($template, $prefix)) {
                $template = str_replace($prefix . '_', '', $template);
            }
        }
        if (in_array($template, ['add', 'edit'])) {
            $template = 'form';
        } elseif (preg_match('@(_add|_edit)$@', $template)) {
            $template = str_replace(['_add', '_edit'], '_form', $template);
        }

        return $template;
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
            __d('cake_console', 'Bake views for a controller, using built-in or custom templates.'),
        )->addArgument('controller', [
            'help' => __d('cake_console', 'Name of the controller views to bake. Can be Plugin.name as a shortcut for plugin baking.'),
        ])->addArgument('action', [
            'help' => __d('cake_console', "Will bake a single action's file. core templates are (index, add, edit, view)"),
        ])->addArgument('alias', [
            'help' => __d('cake_console', 'Will bake the template in <action> but create the filename after <alias>.'),
        ])->addOption('plugin', [
            'short' => 'p',
            'help' => __d('cake_console', 'Plugin to bake the view into.'),
        ])->addOption('admin', [
            'help' => __d('cake_console', 'Set to only bake views for a prefix in Routing.prefixes'),
            'boolean' => true,
        ])->addOption('theme', [
            'short' => 't',
            'help' => __d('cake_console', 'Theme to use when baking code.'),
        ])->addOption('connection', [
            'short' => 'c',
            'help' => __d('cake_console', 'The connection the connected model is on.'),
        ])->addOption('force', [
            'short' => 'f',
            'help' => __d('cake_console', 'Force overwriting existing files without prompting.'),
        ])->addSubcommand('all', [
            'help' => __d('cake_console', 'Bake all CRUD action views for all controllers. Requires models and controllers to exist.'),
        ])->epilog(
            __d('cake_console', 'Omitting all arguments and options will enter into an interactive mode.'),
        );

        return $parser;
    }

    /**
     * Returns associations for controllers models.
     *
     * @param Model $model The Model instance.
     * @return array associations
     */
    protected function _associations(Model $model)
    {
        $keys = ['belongsTo', 'hasOne', 'hasMany', 'hasAndBelongsToMany'];
        $associations = [];

        foreach ($keys as $type) {
            foreach ($model->{$type} as $assocKey => $assocData) {
                [, $modelClass] = pluginSplit($assocData['className']);
                $associations[$type][$assocKey]['primaryKey'] = $model->{$assocKey}->primaryKey;
                $associations[$type][$assocKey]['displayField'] = $model->{$assocKey}->displayField;
                $associations[$type][$assocKey]['foreignKey'] = $assocData['foreignKey'];
                $associations[$type][$assocKey]['controller'] = Inflector::pluralize(Inflector::underscore($modelClass));
                $associations[$type][$assocKey]['fields'] = array_keys($model->{$assocKey}->schema(true));
            }
        }

        return $associations;
    }
}
