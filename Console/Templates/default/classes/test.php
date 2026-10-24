<?php
/**
 * Test Case bake template
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
} elseif (!empty($uses)) {
    foreach ($uses as $dependency) {
        echo "App::uses('{$dependency[0]}', '{$dependency[1]}');\n";
    }
    echo "\n";
}
// A method of the test case, indented by 4 spaces. The tests declare their return types, as PHPUnit 9 does.
$method = function ($name, $body) {
    return "    /**\n     * {$name} method\n     *\n     * @return void\n     */\n" .
        "    public function {$name}(): void\n    {\n{$body}    }\n";
};

$members = [];
if (!empty($fixtures)) {
    $list = '';
    foreach ($fixtures as $fixture) {
        $list .= "        '{$fixture}',\n";
    }
    $members[] = "    /**\n     * Fixtures\n     *\n     * @var array\n     */\n    public \$fixtures = [\n{$list}    ];\n";
}
if (!empty($construction)) {
    $setUp = "        parent::setUp();\n";
    if ($preConstruct) {
        $setUp .= '        ' . $preConstruct;
    }
    $setUp .= "        \$this->{$className} = {$construction}";
    if ($postConstruct) {
        $setUp .= '        ' . $postConstruct;
    }
    $members[] = $method('setUp', $setUp);
    $members[] = $method('tearDown', "        unset(\$this->{$className});\n\n        parent::tearDown();\n");
}
foreach ($methods as $testedMethod) {
    $testName = 'test' . Inflector::camelize($testedMethod);
    $members[] = $method($testName, "        \$this->markTestIncomplete('{$testName} not implemented.');\n");
}

$baseClass = $type === 'Controller' ? 'ControllerTestCase' : 'CakeTestCase';
echo "/**\n * {$fullClassName} Test Case\n */\n";
echo "class {$fullClassName}Test extends {$baseClass}\n{\n";
echo implode("\n", $members);
echo "}\n";
