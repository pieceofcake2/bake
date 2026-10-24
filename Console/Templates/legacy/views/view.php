<?php
/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link          https://cakephp.org CakePHP(tm) Project
 * @package       Cake.Console.Templates.legacy.views
 * @since         CakePHP(tm) v 1.2.0.5234
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */
?>
<div class="<?php echo $pluralVar; ?> view">
<h2><?php echo "<?php echo __('{$singularHumanName}'); ?>"; ?></h2>
    <dl>
<?php
foreach ($fields as $field) {
    $isKey = false;
    if (!empty($associations['belongsTo'])) {
        foreach ($associations['belongsTo'] as $alias => $details) {
            if ($field === $details['foreignKey']) {
                $isKey = true;
                echo "        <dt><?php echo __('" . Inflector::humanize(Inflector::underscore($alias)) . "'); ?></dt>\n";
                echo "        <dd>\n            <?php echo \$this->Html->link(\${$singularVar}['{$alias}']['{$details['displayField']}'], ['controller' => '{$details['controller']}', 'action' => 'view', \${$singularVar}['{$alias}']['{$details['primaryKey']}']]); ?>\n            &nbsp;\n        </dd>\n";
                break;
            }
        }
    }
    if ($isKey !== true) {
        echo "        <dt><?php echo __('" . Inflector::humanize($field) . "'); ?></dt>\n";
        echo "        <dd>\n            <?php echo h(\${$singularVar}['{$modelClass}']['{$field}']); ?>\n            &nbsp;\n        </dd>\n";
    }
}
?>
    </dl>
</div>
<div class="actions">
    <h3><?php echo "<?php echo __('Actions'); ?>"; ?></h3>
    <ul>
<?php
    echo "        <li><?php echo \$this->Html->link(__('Edit " . $singularHumanName ."'), ['action' => 'edit', \${$singularVar}['{$modelClass}']['{$primaryKey}']]); ?> </li>\n";
    echo "        <li><?php echo \$this->Form->postLink(__('Delete " . $singularHumanName . "'), ['action' => 'delete', \${$singularVar}['{$modelClass}']['{$primaryKey}']], ['confirm' => __('Are you sure you want to delete # %s?', \${$singularVar}['{$modelClass}']['{$primaryKey}'])]); ?> </li>\n";
    echo "        <li><?php echo \$this->Html->link(__('List " . $pluralHumanName . "'), ['action' => 'index']); ?> </li>\n";
    echo "        <li><?php echo \$this->Html->link(__('New " . $singularHumanName . "'), ['action' => 'add']); ?> </li>\n";

    $done = [];
    foreach ($associations as $type => $data) {
        foreach ($data as $alias => $details) {
            if ($details['controller'] != $this->name && !in_array($details['controller'], $done)) {
                echo "        <li><?php echo \$this->Html->link(__('List " . Inflector::humanize($details['controller']) . "'), ['controller' => '{$details['controller']}', 'action' => 'index']); ?> </li>\n";
                echo "        <li><?php echo \$this->Html->link(__('New " . Inflector::humanize(Inflector::underscore($alias)) . "'), ['controller' => '{$details['controller']}', 'action' => 'add']); ?> </li>\n";
                $done[] = $details['controller'];
            }
        }
    }
?>
    </ul>
</div>
<?php
if (!empty($associations['hasOne'])) :
    foreach ($associations['hasOne'] as $alias => $details) : ?>
    <div class="related">
        <h3><?php echo "<?php echo __('Related " . Inflector::humanize($details['controller']) . "'); ?>"; ?></h3>
    <?php echo "<?php if (!empty(\${$singularVar}['{$alias}'])) : ?>\n"; ?>
        <dl>
    <?php
            foreach ($details['fields'] as $field) {
                echo "        <dt><?php echo __('" . Inflector::humanize($field) . "'); ?></dt>\n";
                echo "        <dd>\n    <?php echo \${$singularVar}['{$alias}']['{$field}']; ?>\n&nbsp;</dd>\n";
            }
    ?>
        </dl>
    <?php echo "<?php endif; ?>\n"; ?>
        <div class="actions">
            <ul>
                <li><?php echo "<?php echo \$this->Html->link(__('Edit " . Inflector::humanize(Inflector::underscore($alias)) . "'), ['controller' => '{$details['controller']}', 'action' => 'edit', \${$singularVar}['{$alias}']['{$details['primaryKey']}']]); ?></li>\n"; ?>
            </ul>
        </div>
    </div>
    <?php
    endforeach;
endif;
if (empty($associations['hasMany'])) {
    $associations['hasMany'] = [];
}
if (empty($associations['hasAndBelongsToMany'])) {
    $associations['hasAndBelongsToMany'] = [];
}
$relations = array_merge($associations['hasMany'], $associations['hasAndBelongsToMany']);
foreach ($relations as $alias => $details):
    $otherSingularVar = Inflector::variable($alias);
    $otherPluralHumanName = Inflector::humanize($details['controller']);
    ?>
<div class="related">
    <h3><?php echo "<?php echo __('Related " . $otherPluralHumanName . "'); ?>"; ?></h3>
    <?php echo "<?php if (!empty(\${$singularVar}['{$alias}'])) : ?>\n"; ?>
    <table cellpadding = "0" cellspacing = "0">
    <tr>
<?php
            foreach ($details['fields'] as $field) {
                echo "        <th><?php echo __('" . Inflector::humanize($field) . "'); ?></th>\n";
            }
?>
        <th class="actions"><?php echo "<?php echo __('Actions'); ?>"; ?></th>
    </tr>
<?php
echo "        <?php foreach (\${$singularVar}['{$alias}'] as \${$otherSingularVar}) : ?>\n";
        echo "        <tr>\n";
            foreach ($details['fields'] as $field) {
                echo "            <td><?php echo \${$otherSingularVar}['{$field}']; ?></td>\n";
            }

            echo "            <td class=\"actions\">\n";
            echo "                <?php echo \$this->Html->link(__('View'), ['controller' => '{$details['controller']}', 'action' => 'view', \${$otherSingularVar}['{$details['primaryKey']}']]); ?>\n";
            echo "                <?php echo \$this->Html->link(__('Edit'), ['controller' => '{$details['controller']}', 'action' => 'edit', \${$otherSingularVar}['{$details['primaryKey']}']]); ?>\n";
            echo "                <?php echo \$this->Form->postLink(__('Delete'), ['controller' => '{$details['controller']}', 'action' => 'delete', \${$otherSingularVar}['{$details['primaryKey']}']], ['confirm' => __('Are you sure you want to delete # %s?', \${$otherSingularVar}['{$details['primaryKey']}'])]); ?>\n";
            echo "            </td>\n";
        echo "        </tr>\n";

echo "        <?php endforeach; ?>\n";
?>
    </table>
    <?php echo "<?php endif; ?>\n\n"; ?>
    <div class="actions">
        <ul>
            <li><?php echo "<?php echo \$this->Html->link(__('New " . Inflector::humanize(Inflector::underscore($alias)) . "'), ['controller' => '{$details['controller']}', 'action' => 'add']); ?>"; ?> </li>
        </ul>
    </div>
</div>
<?php
endforeach;
?>
