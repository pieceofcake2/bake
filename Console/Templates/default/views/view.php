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
 * @package       Cake.Console.Templates.default.views
 * @since         CakePHP(tm) v 1.2.0.5234
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */

$pk = "\${$singularVar}['{$modelClass}']['{$primaryKey}']";
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?php echo "<?php echo __('Actions'); ?>"; ?></h4>
<?php
echo "            <?php echo \$this->Html->link(__('Edit {$singularHumanName}'), ['action' => 'edit', {$pk}], ['class' => 'side-nav-item']); ?>\n";
echo "            <?php echo \$this->Form->postLink(__('Delete {$singularHumanName}'), ['action' => 'delete', {$pk}], ['class' => 'side-nav-item', 'confirm' => __('Are you sure you want to delete # %s?', {$pk})]); ?>\n";
echo "            <?php echo \$this->Html->link(__('List {$pluralHumanName}'), ['action' => 'index'], ['class' => 'side-nav-item']); ?>\n";
echo "            <?php echo \$this->Html->link(__('New {$singularHumanName}'), ['action' => 'add'], ['class' => 'side-nav-item']); ?>\n";

$done = [];
foreach ($associations as $type => $data) {
    foreach ($data as $alias => $details) {
        if ($details['controller'] != $this->name && !in_array($details['controller'], $done)) {
            echo "            <?php echo \$this->Html->link(__('List " . Inflector::humanize($details['controller']) . "'), ['controller' => '{$details['controller']}', 'action' => 'index'], ['class' => 'side-nav-item']); ?>\n";
            echo "            <?php echo \$this->Html->link(__('New " . Inflector::humanize(Inflector::underscore($alias)) . "'), ['controller' => '{$details['controller']}', 'action' => 'add'], ['class' => 'side-nav-item']); ?>\n";
            $done[] = $details['controller'];
        }
    }
}
?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="<?php echo $pluralVar; ?> view content">
            <h3><?php echo "<?php echo h(\${$singularVar}['{$modelClass}']['{$displayField}']); ?>"; ?></h3>
<?php
// Group the fields like cakephp/bake: strings and belongsTo keys first, then hasOne
// associations, numbers, dates and booleans in the table, and text columns below it.
$groupedFields = ['string' => [], 'number' => [], 'date' => [], 'boolean' => [], 'text' => []];
$associationFields = [];
foreach ($fields as $field) {
    if (!empty($associations['belongsTo'])) {
        foreach ($associations['belongsTo'] as $alias => $details) {
            if ($field === $details['foreignKey']) {
                $associationFields[$field] = ['alias' => $alias] + $details;
                break;
            }
        }
    }
    $type = isset($schema[$field]['type']) ? $schema[$field]['type'] : null;
    if ($type === 'binary') {
        continue;
    }
    if (isset($associationFields[$field])) {
        $group = 'string';
    } elseif (in_array($type, ['integer', 'biginteger', 'smallinteger', 'tinyinteger', 'float', 'decimal'], true)) {
        $group = 'number';
    } elseif (in_array($type, ['date', 'time', 'datetime', 'timestamp'], true)) {
        $group = 'date';
    } elseif (in_array($type, ['boolean', 'text'], true)) {
        $group = $type;
    } else {
        $group = 'string';
    }
    $groupedFields[$group][] = $field;
}
?>
            <table>
<?php
foreach ($groupedFields['string'] as $field) {
    if (isset($associationFields[$field])) {
        $details = $associationFields[$field];
        $alias = $details['alias'];
        echo "                <tr>\n";
        echo "                    <th><?php echo __('" . Inflector::humanize(Inflector::underscore($alias)) . "'); ?></th>\n";
        echo "                    <td><?php echo !empty(\${$singularVar}['{$alias}']['{$details['primaryKey']}']) ? \$this->Html->link(\${$singularVar}['{$alias}']['{$details['displayField']}'], ['controller' => '{$details['controller']}', 'action' => 'view', \${$singularVar}['{$alias}']['{$details['primaryKey']}']]) : ''; ?></td>\n";
        echo "                </tr>\n";
    } else {
        echo "                <tr>\n";
        echo "                    <th><?php echo __('" . Inflector::humanize($field) . "'); ?></th>\n";
        echo "                    <td><?php echo h(\${$singularVar}['{$modelClass}']['{$field}']); ?></td>\n";
        echo "                </tr>\n";
    }
}
if (!empty($associations['hasOne'])) {
    foreach ($associations['hasOne'] as $alias => $details) {
        echo "                <tr>\n";
        echo "                    <th><?php echo __('" . Inflector::humanize(Inflector::underscore($alias)) . "'); ?></th>\n";
        echo "                    <td><?php echo !empty(\${$singularVar}['{$alias}']['{$details['primaryKey']}']) ? \$this->Html->link(\${$singularVar}['{$alias}']['{$details['displayField']}'], ['controller' => '{$details['controller']}', 'action' => 'view', \${$singularVar}['{$alias}']['{$details['primaryKey']}']]) : ''; ?></td>\n";
        echo "                </tr>\n";
    }
}
foreach ($groupedFields['number'] as $field) {
    $value = "\${$singularVar}['{$modelClass}']['{$field}']";
    if (in_array($schema[$field]['type'], ['float', 'decimal'], true)) {
        // CakeNumber::format() rounds to whole numbers by default, so print fractions as they are
        $out = "h({$value})";
    } elseif (!empty($schema[$field]['null'])) {
        $out = "{$value} === null ? '' : \$this->Number->format({$value})";
    } else {
        $out = "\$this->Number->format({$value})";
    }
    echo "                <tr>\n";
    echo "                    <th><?php echo __('" . Inflector::humanize($field) . "'); ?></th>\n";
    echo "                    <td><?php echo {$out}; ?></td>\n";
    echo "                </tr>\n";
}
foreach ($groupedFields['date'] as $field) {
    echo "                <tr>\n";
    echo "                    <th><?php echo __('" . Inflector::humanize($field) . "'); ?></th>\n";
    echo "                    <td><?php echo h(\${$singularVar}['{$modelClass}']['{$field}']); ?></td>\n";
    echo "                </tr>\n";
}
foreach ($groupedFields['boolean'] as $field) {
    echo "                <tr>\n";
    echo "                    <th><?php echo __('" . Inflector::humanize($field) . "'); ?></th>\n";
    echo "                    <td><?php echo \${$singularVar}['{$modelClass}']['{$field}'] ? __('Yes') : __('No'); ?></td>\n";
    echo "                </tr>\n";
}
?>
            </table>
<?php foreach ($groupedFields['text'] as $field) : ?>
            <div class="text">
                <strong><?php echo "<?php echo __('" . Inflector::humanize($field) . "'); ?>"; ?></strong>
                <blockquote>
                    <?php echo "<?php echo \$this->Text->autoParagraph(h(\${$singularVar}['{$modelClass}']['{$field}'])); ?>\n"; ?>
                </blockquote>
            </div>
<?php
endforeach;
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
    $otherPk = "\${$otherSingularVar}['{$details['primaryKey']}']";
    ?>
            <div class="related">
                <h4><?php echo "<?php echo __('Related " . $otherPluralHumanName . "'); ?>"; ?></h4>
                <?php echo "<?php if (!empty(\${$singularVar}['{$alias}'])) : ?>\n"; ?>
                <div class="table-responsive">
                    <table>
                        <tr>
<?php
    foreach ($details['fields'] as $field) {
        echo "                            <th><?php echo __('" . Inflector::humanize($field) . "'); ?></th>\n";
    }
?>
                            <th class="actions"><?php echo "<?php echo __('Actions'); ?>"; ?></th>
                        </tr>
                        <?php echo "<?php foreach (\${$singularVar}['{$alias}'] as \${$otherSingularVar}) : ?>\n"; ?>
                        <tr>
<?php
    foreach ($details['fields'] as $field) {
        echo "                            <td><?php echo h(\${$otherSingularVar}['{$field}']); ?></td>\n";
    }
?>
                            <td class="actions">
                                <?php echo "<?php echo \$this->Html->link(__('View'), ['controller' => '{$details['controller']}', 'action' => 'view', {$otherPk}]); ?>\n"; ?>
                                <?php echo "<?php echo \$this->Html->link(__('Edit'), ['controller' => '{$details['controller']}', 'action' => 'edit', {$otherPk}]); ?>\n"; ?>
                                <?php echo "<?php echo \$this->Form->postLink(__('Delete'), ['controller' => '{$details['controller']}', 'action' => 'delete', {$otherPk}], ['confirm' => __('Are you sure you want to delete # %s?', {$otherPk})]); ?>\n"; ?>
                            </td>
                        </tr>
                        <?php echo "<?php endforeach; ?>\n"; ?>
                    </table>
                </div>
                <?php echo "<?php endif; ?>\n"; ?>
            </div>
<?php endforeach; ?>
        </div>
    </div>
</div>
