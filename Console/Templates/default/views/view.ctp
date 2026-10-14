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
echo "            <?php echo \$this->Html->link(__('Edit {$singularHumanName}'), array('action' => 'edit', {$pk}), array('class' => 'side-nav-item')); ?>\n";
echo "            <?php echo \$this->Form->postLink(__('Delete {$singularHumanName}'), array('action' => 'delete', {$pk}), array('class' => 'side-nav-item', 'confirm' => __('Are you sure you want to delete # %s?', {$pk}))); ?>\n";
echo "            <?php echo \$this->Html->link(__('List {$pluralHumanName}'), array('action' => 'index'), array('class' => 'side-nav-item')); ?>\n";
echo "            <?php echo \$this->Html->link(__('New {$singularHumanName}'), array('action' => 'add'), array('class' => 'side-nav-item')); ?>\n";

$done = array();
foreach ($associations as $type => $data) {
    foreach ($data as $alias => $details) {
        if ($details['controller'] != $this->name && !in_array($details['controller'], $done)) {
            echo "            <?php echo \$this->Html->link(__('List " . Inflector::humanize($details['controller']) . "'), array('controller' => '{$details['controller']}', 'action' => 'index'), array('class' => 'side-nav-item')); ?>\n";
            echo "            <?php echo \$this->Html->link(__('New " . Inflector::humanize(Inflector::underscore($alias)) . "'), array('controller' => '{$details['controller']}', 'action' => 'add'), array('class' => 'side-nav-item')); ?>\n";
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
            <table>
<?php
foreach ($fields as $field) {
    $isKey = false;
    if (!empty($associations['belongsTo'])) {
        foreach ($associations['belongsTo'] as $alias => $details) {
            if ($field === $details['foreignKey']) {
                $isKey = true;
                echo "                <tr>\n";
                echo "                    <th><?php echo __('" . Inflector::humanize(Inflector::underscore($alias)) . "'); ?></th>\n";
                echo "                    <td><?php echo \$this->Html->link(\${$singularVar}['{$alias}']['{$details['displayField']}'], array('controller' => '{$details['controller']}', 'action' => 'view', \${$singularVar}['{$alias}']['{$details['primaryKey']}'])); ?></td>\n";
                echo "                </tr>\n";
                break;
            }
        }
    }
    if ($isKey !== true) {
        echo "                <tr>\n";
        echo "                    <th><?php echo __('" . Inflector::humanize($field) . "'); ?></th>\n";
        echo "                    <td><?php echo h(\${$singularVar}['{$modelClass}']['{$field}']); ?></td>\n";
        echo "                </tr>\n";
    }
}
?>
            </table>
<?php
if (!empty($associations['hasOne'])) :
    foreach ($associations['hasOne'] as $alias => $details): ?>
            <div class="related">
                <?php echo "<?php echo \$this->Html->link(__('Edit " . Inflector::humanize(Inflector::underscore($alias)) . "'), array('controller' => '{$details['controller']}', 'action' => 'edit', \${$singularVar}['{$alias}']['{$details['primaryKey']}']), array('class' => 'button float-right')); ?>\n"; ?>
                <h4><?php echo "<?php echo __('Related " . Inflector::humanize($details['controller']) . "'); ?>"; ?></h4>
                <?php echo "<?php if (!empty(\${$singularVar}['{$alias}'])): ?>\n"; ?>
                <table>
<?php
        foreach ($details['fields'] as $field) {
            echo "                    <tr>\n";
            echo "                        <th><?php echo __('" . Inflector::humanize($field) . "'); ?></th>\n";
            echo "                        <td><?php echo h(\${$singularVar}['{$alias}']['{$field}']); ?></td>\n";
            echo "                    </tr>\n";
        }
?>
                </table>
                <?php echo "<?php endif; ?>\n"; ?>
            </div>
<?php
    endforeach;
endif;
if (empty($associations['hasMany'])) {
    $associations['hasMany'] = array();
}
if (empty($associations['hasAndBelongsToMany'])) {
    $associations['hasAndBelongsToMany'] = array();
}
$relations = array_merge($associations['hasMany'], $associations['hasAndBelongsToMany']);
foreach ($relations as $alias => $details):
    $otherSingularVar = Inflector::variable($alias);
    $otherPluralHumanName = Inflector::humanize($details['controller']);
    $otherPk = "\${$otherSingularVar}['{$details['primaryKey']}']";
    ?>
            <div class="related">
                <?php echo "<?php echo \$this->Html->link(__('New " . Inflector::humanize(Inflector::underscore($alias)) . "'), array('controller' => '{$details['controller']}', 'action' => 'add'), array('class' => 'button float-right')); ?>\n"; ?>
                <h4><?php echo "<?php echo __('Related " . $otherPluralHumanName . "'); ?>"; ?></h4>
                <?php echo "<?php if (!empty(\${$singularVar}['{$alias}'])): ?>\n"; ?>
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
                        <?php echo "<?php foreach (\${$singularVar}['{$alias}'] as \${$otherSingularVar}): ?>\n"; ?>
                        <tr>
<?php
    foreach ($details['fields'] as $field) {
        echo "                            <td><?php echo h(\${$otherSingularVar}['{$field}']); ?></td>\n";
    }
?>
                            <td class="actions">
                                <?php echo "<?php echo \$this->Html->link(__('View'), array('controller' => '{$details['controller']}', 'action' => 'view', {$otherPk})); ?>\n"; ?>
                                <?php echo "<?php echo \$this->Html->link(__('Edit'), array('controller' => '{$details['controller']}', 'action' => 'edit', {$otherPk})); ?>\n"; ?>
                                <?php echo "<?php echo \$this->Form->postLink(__('Delete'), array('controller' => '{$details['controller']}', 'action' => 'delete', {$otherPk}), array('confirm' => __('Are you sure you want to delete # %s?', {$otherPk}))); ?>\n"; ?>
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
