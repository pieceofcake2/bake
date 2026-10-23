<?php
/**
 * Fixture Template file
 *
 * Fixture Template used when baking fixtures with bake
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
}
// A property of the class with its docblock, indented by 4 spaces
$property = function ($comment, $type, $name, $value) {
    return "    /**\n     * {$comment}\n     *\n     * @var {$type}\n     */\n    public \${$name} = {$value};\n";
};

$properties = [];
if ($table) {
    $properties[] = $property('Table name', 'string', 'table', "'{$table}'");
}
if ($import) {
    $properties[] = $property('Import', 'array', 'import', $import);
}
if ($schema) {
    $properties[] = $property('Fields', 'array', 'fields', $schema);
}
if ($records) {
    $properties[] = $property('Records', 'array', 'records', $records);
}

echo "/**\n * {$model} Fixture\n */\n";
echo "class {$model}Fixture extends CakeTestFixture\n{\n";
echo implode("\n", $properties);
echo "}\n";
