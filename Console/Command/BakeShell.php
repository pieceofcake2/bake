<?php
/**
 * Command-line code generation utility to automate programmer chores.
 *
 * Bake is CakePHP's code generation script, which can help you kickstart
 * application development by writing fully functional skeleton controllers,
 * models, and views. Going further, Bake can also write Unit Tests for you.
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
 * @since         CakePHP(tm) v 1.2.0.5012
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */

App::uses('AppShell', 'Console/Command');
App::uses('Model', 'Model');

/**
 * Command-line code generation utility to automate programmer chores.
 *
 * Bake is CakePHP's code generation script, which can help you kickstart
 * application development by writing fully functional skeleton controllers,
 * models, and views. Going further, Bake can also write Unit Tests for you.
 *
 * @package       Cake.Console.Command
 * @link          https://book.cakephp.org/2.0/en/console-and-shells/code-generation-with-bake.html
 * @property ProjectTask $Project
 * @property DbConfigTask $DbConfig
 * @property ModelTask $Model
 * @property ControllerTask $Controller
 * @property ViewTask $View
 * @property PluginTask $Plugin
 * @property FixtureTask $Fixture
 * @property TestTask $Test
 */
class BakeShell extends AppShell
{
    /**
     * Contains tasks to load and instantiate
     *
     * @var array
     */
    public $tasks = ['Bake.Project', 'Bake.DbConfig', 'Bake.Model', 'Bake.Controller', 'Bake.View', 'Bake.Plugin', 'Bake.Fixture', 'Bake.Test'];

    /**
     * The connection being used.
     *
     * @var string|null
     */
    public $connection = 'default';

    /**
     * Assign $this->connection to the active task if a connection param is set.
     *
     * @return void
     */
    public function startup()
    {
        parent::startup();
        Configure::write('debug', 2);
        Configure::write('Cache.disable', 1);

        $task = Inflector::classify($this->command);
        if (isset($this->{$task}) && !in_array($task, ['Project', 'DbConfig'])) {
            if (isset($this->params['connection'])) {
                $this->{$task}->connection = $this->params['connection'];
            }
        }
        if (isset($this->params['connection'])) {
            $this->connection = $this->params['connection'];
        }
    }

    /**
     * Override main() to handle action
     *
     * @return mixed
     */
    public function main()
    {
        if (!is_dir($this->DbConfig->path)) {
            $path = $this->Project->execute();
            if (!empty($path)) {
                $this->DbConfig->path = $path . 'Config' . DS;
            } else {
                return false;
            }
        }

        if (!config('database')) {
            $this->out(__d('cake_console', 'Your database configuration was not found. Take a moment to create one.'));
            $this->args = [];
            $this->DbConfig->execute();

            return null;
        }
        $this->out(__d('cake_console', 'Interactive Bake Shell'));
        $this->hr();
        $this->out(__d('cake_console', '[D]atabase Configuration'));
        $this->out(__d('cake_console', '[M]odel'));
        $this->out(__d('cake_console', '[V]iew'));
        $this->out(__d('cake_console', '[C]ontroller'));
        $this->out(__d('cake_console', '[P]roject'));
        $this->out(__d('cake_console', '[F]ixture'));
        $this->out(__d('cake_console', '[T]est case'));
        $this->out(__d('cake_console', '[Q]uit'));

        $classToBake = strtoupper($this->in(__d('cake_console', 'What would you like to Bake?'), ['D', 'M', 'V', 'C', 'P', 'F', 'T', 'Q']));
        switch ($classToBake) {
            case 'D':
                $this->DbConfig->execute();
                break;
            case 'M':
                $this->Model->execute();
                break;
            case 'V':
                $this->View->execute();
                break;
            case 'C':
                $this->Controller->execute();
                break;
            case 'P':
                $this->Project->execute();
                break;
            case 'F':
                $this->Fixture->execute();
                break;
            case 'T':
                $this->Test->execute();
                break;
            case 'Q':
                $this->_stop();

                return null;
            default:
                $this->out(__d('cake_console', 'You have made an invalid selection. Please choose a type of class to Bake by entering D, M, V, F, T, or C.'));
        }
        $this->hr();
        $this->main();
    }

    /**
     * Quickly bake the MVC
     *
     * @return void
     */
    public function all()
    {
        $this->out('Bake All');
        $this->hr();

        if (!isset($this->params['connection']) && empty($this->connection)) {
            $this->connection = $this->DbConfig->getConfig();
        }

        if (!empty($this->args[0])) {
            $name = $this->args[0];
        } else {
            $this->Model->interactive = true;
            $name = $this->Model->getName($this->connection);
        }

        foreach (['Model', 'Controller', 'View'] as $task) {
            $this->{$task}->connection = $this->connection;
            $this->{$task}->interactive = false;
        }

        $modelExists = false;
        $model = $this->_modelName($name);

        // The classes of a namespaced application are in App.namespace, which App::className() looks in
        $namespaced = (bool)Configure::read('App.namespace');
        if ($namespaced) {
            $modelClass = App::className($model, 'Model');
        } else {
            App::uses('AppModel', 'Model');
            App::uses($model, 'Model');
            $modelClass = class_exists($model) ? $model : null;
        }
        if ($modelClass !== null) {
            $object = new $modelClass();
            $modelExists = true;
        } else {
            $object = new Model(['name' => $name, 'ds' => $this->connection]);
        }

        $modelBaked = $this->Model->bake($object, false);

        if ($modelBaked && $modelExists === false) {
            if ($this->_checkUnitTest()) {
                $this->Model->bakeFixture($model);
                $this->Model->bakeTest($model);
            }
            $modelExists = true;
        }

        if ($modelExists === true) {
            $controller = $this->_controllerName($name);
            if ($this->Controller->bake($controller, $this->Controller->bakeActions($controller))) {
                if ($this->_checkUnitTest()) {
                    $this->Controller->bakeTest($controller);
                }
            }
            if ($namespaced) {
                $controllerExists = App::className($controller, 'Controller', 'Controller') !== null;
            } else {
                App::uses($controller . 'Controller', 'Controller');
                $controllerExists = class_exists($controller . 'Controller');
            }
            if ($controllerExists) {
                $this->View->args = [$name];
                $this->View->execute();
            }
            $this->out('', 1, Shell::QUIET);
            $this->out(__d('cake_console', '<success>Bake All complete</success>'), 1, Shell::QUIET);
            array_shift($this->args);
        } else {
            $this->error(__d('cake_console', 'Bake All could not continue without a valid model'));
        }

        $this->_stop();
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
            __d('cake_console', 'The Bake script generates controllers, views and models for your application.' .
            ' If run with no command line arguments, Bake guides the user through the class creation process.' .
            ' You can customize the generation process by telling Bake where different parts of your application are using command line arguments.'),
        )->addSubcommand('all', [
            'help' => __d('cake_console', 'Bake a complete MVC. optional <name> of a Model'),
        ])->addSubcommand('project', [
            'help' => __d('cake_console', 'Bake a new app folder in the path supplied or in current directory if no path is specified'),
            'parser' => $this->Project->getOptionParser(),
        ])->addSubcommand('plugin', [
            'help' => __d('cake_console', 'Bake a new plugin folder in the path supplied or in current directory if no path is specified.'),
            'parser' => $this->Plugin->getOptionParser(),
        ])->addSubcommand('db_config', [
            'help' => __d('cake_console', 'Bake a database.php file in config directory.'),
            'parser' => $this->DbConfig->getOptionParser(),
        ])->addSubcommand('model', [
            'help' => __d('cake_console', 'Bake a model.'),
            'parser' => $this->Model->getOptionParser(),
        ])->addSubcommand('view', [
            'help' => __d('cake_console', 'Bake views for controllers.'),
            'parser' => $this->View->getOptionParser(),
        ])->addSubcommand('controller', [
            'help' => __d('cake_console', 'Bake a controller.'),
            'parser' => $this->Controller->getOptionParser(),
        ])->addSubcommand('fixture', [
            'help' => __d('cake_console', 'Bake a fixture.'),
            'parser' => $this->Fixture->getOptionParser(),
        ])->addSubcommand('test', [
            'help' => __d('cake_console', 'Bake a unit test.'),
            'parser' => $this->Test->getOptionParser(),
        ])->addOption('connection', [
            'help' => __d('cake_console', 'Database connection to use in conjunction with `bake all`.'),
            'short' => 'c',
            'default' => 'default',
        ])->addOption('theme', [
            'short' => 't',
            'help' => __d('cake_console', 'Theme to use when baking code.'),
        ]);

        return $parser;
    }
}
