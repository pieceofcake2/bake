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
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?php echo "<?php echo __('Actions'); ?>"; ?></h4>
<?php if (strpos($action, 'add') === false) : ?>
            <?php echo "<?php echo \$this->Form->postLink(__('Delete'), ['action' => 'delete', \$this->Form->value('{$modelClass}.{$primaryKey}')], ['class' => 'side-nav-item', 'confirm' => __('Are you sure you want to delete # %s?', \$this->Form->value('{$modelClass}.{$primaryKey}'))]); ?>\n"; ?>
<?php endif; ?>
            <?php echo "<?php echo \$this->Html->link(__('List {$pluralHumanName}'), ['action' => 'index'], ['class' => 'side-nav-item']); ?>\n"; ?>
<?php
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
        <div class="<?php echo $pluralVar; ?> form content">
            <?php echo "<?php echo \$this->Form->create('{$modelClass}'); ?>\n"; ?>
            <fieldset>
                <legend><?php printf("<?php echo __('%s %s'); ?>", Inflector::humanize($action), $singularHumanName); ?></legend>
                <?php echo "<?php\n"; ?>
<?php
foreach ($fields as $field) {
    if (strpos($action, 'add') !== false && $field === $primaryKey) {
        continue;
    } elseif (!in_array($field, ['created', 'modified', 'updated'])) {
        echo "                echo \$this->Form->input('{$field}');\n";
    }
}
if (!empty($associations['hasAndBelongsToMany'])) {
    foreach ($associations['hasAndBelongsToMany'] as $assocName => $assocData) {
        echo "                echo \$this->Form->input('{$assocName}');\n";
    }
}
?>
                ?>
            </fieldset>
            <?php echo "<?php echo \$this->Form->button(__('Submit')); ?>\n"; ?>
            <?php echo "<?php echo \$this->Form->end(); ?>\n"; ?>
        </div>
    </div>
</div>
