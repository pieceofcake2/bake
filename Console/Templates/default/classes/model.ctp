<?php
/**
 * Model template file.
 *
 * Used by bake to create new Model files.
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
    // Applications with the modern layout autoload AppModel with the Composer classmap.
    echo "App::uses('{$plugin}AppModel', '{$pluginPath}Model');\n\n";
}
// The class names of the settings: Name::class for the classes that exist, else the name in a string.
$classNames = isset($classNames) ? $classNames : [];
$modelClass = function ($name) use ($classNames) {
    return isset($classNames['model'][$name]) ? $classNames['model'][$name] . '::class' : "'{$name}'";
};
// A property of the class with its docblock, indented by 4 spaces
$property = function ($comment, $type, $name, $value) {
    return "    /**\n     * {$comment}\n     *\n     * @var {$type}\n     */\n    public \${$name} = {$value};\n";
};
// The keys of an association, as an array of 12 spaces of indentation
$association = function ($alias, $keys) {
    $out = "        '{$alias}' => [\n";
    foreach ($keys as $key => $value) {
        $out .= "            '{$key}' => {$value},\n";
    }

    return $out . "        ],\n";
};

$properties = [];
if ($useDbConfig !== 'default') {
    $properties[] = $property('Use database config', 'string', 'useDbConfig', "'{$useDbConfig}'");
}
if ($useTable && $useTable !== Inflector::tableize($name)) {
    $properties[] = $property('Use table', 'mixed False or table name', 'useTable', "'{$useTable}'");
}
if ($primaryKey !== 'id') {
    $properties[] = $property('Primary key field', 'string', 'primaryKey', "'{$primaryKey}'");
}
if ($displayField) {
    $properties[] = $property('Display field', 'string', 'displayField', "'{$displayField}'");
}
if (!empty($actsAs)) {
    $behaviors = "[\n";
    foreach ($actsAs as $behavior) {
        if (is_string($behavior) && isset($classNames['behavior'][$behavior])) {
            $behaviors .= "        {$classNames['behavior'][$behavior]}::class,\n";
        } else {
            $behaviors .= '        ' . var_export($behavior, true) . ",\n";
        }
    }
    $properties[] = $property('Behaviors', 'array', 'actsAs', $behaviors . '    ]');
}
if (!empty($validate)) {
    $rules = "[\n";
    foreach ($validate as $field => $validations) {
        $rules .= "        '{$field}' => [\n";
        foreach ($validations as $key => $validator) {
            $rules .= "            '{$key}' => [\n";
            $rules .= "                'rule' => ['{$validator}'],\n";
            $rules .= "                //'message' => 'Your custom message here',\n";
            $rules .= "                //'allowEmpty' => false,\n";
            $rules .= "                //'required' => false,\n";
            $rules .= "                //'last' => false, // Stop validation after this rule\n";
            $rules .= "                //'on' => 'create', // Limit validation to 'create' or 'update' operations\n";
            $rules .= "            ],\n";
        }
        $rules .= "        ],\n";
    }
    $properties[] = $property('Validation rules', 'array', 'validate', $rules . '    ]');
}

$associationProperties = [];
foreach (['hasOne', 'belongsTo'] as $assocType) {
    if (!empty($associations[$assocType])) {
        $value = "[\n";
        foreach ($associations[$assocType] as $relation) {
            $value .= $association($relation['alias'], [
                'className' => $modelClass($relation['className']),
                'foreignKey' => "'{$relation['foreignKey']}'",
                'conditions' => "''",
                'fields' => "''",
                'order' => "''",
            ]);
        }
        $associationProperties[] = $property("{$assocType} associations", 'array', $assocType, $value . '    ]');
    }
}
if (!empty($associations['hasMany'])) {
    $value = "[\n";
    foreach ($associations['hasMany'] as $relation) {
        $value .= $association($relation['alias'], [
            'className' => $modelClass($relation['className']),
            'foreignKey' => "'{$relation['foreignKey']}'",
            'dependent' => 'false',
            'conditions' => "''",
            'fields' => "''",
            'order' => "''",
            'limit' => "''",
            'offset' => "''",
            'exclusive' => "''",
            'finderQuery' => "''",
            'counterQuery' => "''",
        ]);
    }
    $associationProperties[] = $property('hasMany associations', 'array', 'hasMany', $value . '    ]');
}
if (!empty($associations['hasAndBelongsToMany'])) {
    $value = "[\n";
    foreach ($associations['hasAndBelongsToMany'] as $relation) {
        $value .= $association($relation['alias'], [
            'className' => $modelClass($relation['className']),
            'joinTable' => "'{$relation['joinTable']}'",
            'foreignKey' => "'{$relation['foreignKey']}'",
            'associationForeignKey' => "'{$relation['associationForeignKey']}'",
            'unique' => "'keepExisting'",
            'conditions' => "''",
            'fields' => "''",
            'order' => "''",
            'limit' => "''",
            'offset' => "''",
            'finderQuery' => "''",
        ]);
    }
    $associationProperties[] = $property('hasAndBelongsToMany associations', 'array', 'hasAndBelongsToMany', $value . '    ]');
}
if ($associationProperties) {
    $associationProperties[0] = "    // The Associations below have been created with all possible keys, those that are not needed can be removed\n\n" .
        $associationProperties[0];
    $properties = array_merge($properties, $associationProperties);
}

$docProperties = [];
foreach (['hasOne', 'belongsTo', 'hasMany', 'hasAndBelongsToMany'] as $assocType) {
    if (!empty($associations[$assocType])) {
        foreach ($associations[$assocType] as $relation) {
            $propertyType = isset($classNames['model'][$relation['className']]) ? $classNames['model'][$relation['className']] : $relation['className'];
            $docProperties[] = " * @property {$propertyType} \${$relation['alias']}\n";
        }
    }
}

echo "/**\n * {$name} Model\n";
if ($docProperties) {
    echo " *\n" . implode('', $docProperties);
}
echo " */\n";
echo "class {$name} extends {$plugin}AppModel\n{\n";
echo implode("\n", $properties);
echo "}\n";
