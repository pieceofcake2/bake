<?php
/**
 * Bake Plugin Bootstrap
 *
 * Adds Bake console commands and tasks to the application path
 * so they can be used without the plugin prefix.
 *
 * @package       Bake.Config
 */

App::build([
    'Console/Command' => [
        APP . 'Plugin' . DS . 'Bake' . DS . 'Console' . DS . 'Command' . DS,
    ],
    'Console/Command/Task' => [
        APP . 'Plugin' . DS . 'Bake' . DS . 'Console' . DS . 'Command' . DS . 'Task' . DS,
    ],
], App::PREPEND);