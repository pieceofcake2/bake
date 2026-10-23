<?php
/**
 * Controller bake template file
 *
 * Allows templating of Controllers generated from bake.
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
 * @package       Cake.Console.Templates.default.classes
 * @since         CakePHP(tm) v 1.3
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */

echo "<?php\n";
if (!empty($namespace)) {
    // Namespaced classes: Composer loads the classes, which the use statements import.
    echo "\nnamespace {$namespace};\n\n";
    if (!empty($imports)) {
        foreach ($imports as $importedClass) {
            echo "use {$importedClass};\n";
        }
        echo "\n";
    }
} elseif (!isset($appUses) || $appUses) {
    // Applications with the modern layout autoload AppController with the Composer classmap.
    echo "App::uses('{$plugin}AppController', '{$pluginPath}Controller');\n\n";
}
// The class names of the settings: Name::class for the classes that exist, else the name in a string.
$classNames = isset($classNames) ? $classNames : [];
$classValues = function ($type, $names) use ($classNames) {
    $values = [];
    foreach ($names as $name) {
        $name = Inflector::camelize($name);
        $values[] = isset($classNames[$type][$name]) ? $classNames[$type][$name] . '::class' : "'{$name}'";
    }

    return '[' . implode(', ', $values) . ']';
};

echo "/**\n * {$controllerName} Controller\n";
if (!$isScaffold) {
    $defaultModel = Inflector::singularize($controllerName);
    echo " *\n";
    echo " * @property {$defaultModel} \${$defaultModel}\n";
    foreach ($components as $component) {
        $componentType = isset($classNames['component'][Inflector::camelize($component)]) ? $classNames['component'][Inflector::camelize($component)] : "{$component}Component";
        echo " * @property {$componentType} \${$component}\n";
    }
}
echo " */\n";
echo "class {$controllerName}Controller extends {$plugin}AppController\n{\n";

$members = [];
if ($isScaffold) {
    $members[] = "    /**\n     * Scaffold\n     *\n     * @var mixed\n     */\n    public \$scaffold;\n";
} else {
    if (count($helpers)) {
        $members[] = "    /**\n     * Helpers\n     *\n     * @var array\n     */\n    public \$helpers = " . $classValues('helper', $helpers) . ";\n";
    }
    if (count($components)) {
        $members[] = "    /**\n     * Components\n     *\n     * @var array\n     */\n    public \$components = " . $classValues('component', $components) . ";\n";
    }
    if (trim((string)$actions) !== '') {
        // One blank line between the methods, also between the actions of a prefix and the others
        $actions = preg_replace("/\n{3,}/", "\n\n", trim($actions, "\n"));
        $members[] = preg_replace("/\}\n( *\/\*\*)/", "}\n\n\$1", $actions) . "\n";
    }
}
echo implode("\n", $members);
echo "}\n";
