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
	echo "App::uses('{$plugin}AppController', '{$pluginPath}Controller');\n";
}
// The class names of the settings: Name::class for the classes that exist, else the name in a string.
$classNames = isset($classNames) ? $classNames : array();
$classValue = function ($type, $name) use ($classNames) {
	return isset($classNames[$type][$name]) ? $classNames[$type][$name] . '::class' : "'{$name}'";
};
?>
/**
 * <?php echo $controllerName; ?> Controller
<?php
if (!$isScaffold) {
	$defaultModel = Inflector::singularize($controllerName);
	echo " *\n";
	echo " * @property {$defaultModel} \${$defaultModel}\n";
	if (!empty($components)) {
		foreach ($components as $component) {
			$componentType = isset($classNames['component'][Inflector::camelize($component)]) ? $classNames['component'][Inflector::camelize($component)] : "{$component}Component";
			echo " * @property {$componentType} \${$component}\n";
		}
	}
}
?>
 */
class <?php echo $controllerName; ?>Controller extends <?php echo $plugin; ?>AppController {

<?php if ($isScaffold): ?>
/**
 * Scaffold
 *
 * @var mixed
 */
	public $scaffold;

<?php else:

	if (count($helpers)):
		echo "/**\n * Helpers\n *\n * @var array\n */\n";
		echo "\tpublic \$helpers = array(";
		for ($i = 0, $len = count($helpers); $i < $len; $i++):
			if ($i != $len - 1):
				echo $classValue('helper', Inflector::camelize($helpers[$i])) . ", ";
			else:
				echo $classValue('helper', Inflector::camelize($helpers[$i]));
			endif;
		endfor;
		echo ");\n\n";
	endif;

	if (count($components)):
		echo "/**\n * Components\n *\n * @var array\n */\n";
		echo "\tpublic \$components = array(";
		for ($i = 0, $len = count($components); $i < $len; $i++):
			if ($i != $len - 1):
				echo $classValue('component', Inflector::camelize($components[$i])) . ", ";
			else:
				echo $classValue('component', Inflector::camelize($components[$i]));
			endif;
		endfor;
		echo ");\n\n";
	endif;

	if (!empty($actions)) {
		echo trim($actions) . "\n";
	}

endif; ?>
}
