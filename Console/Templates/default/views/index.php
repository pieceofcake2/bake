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

// Like cakephp/bake, text and binary columns are not listed in the index table.
$fields = array_values(array_filter($fields, function ($field) use ($schema) {
    return !isset($schema[$field]['type']) || !in_array($schema[$field]['type'], ['text', 'binary'], true);
}));
?>
<div class="<?php echo $pluralVar; ?> index content">
    <?php echo "<?php echo \$this->Html->link(__('New {$singularHumanName}'), ['action' => 'add'], ['class' => 'button float-right']); ?>\n"; ?>
    <h3><?php echo "<?php echo __('{$pluralHumanName}'); ?>"; ?></h3>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
<?php foreach ($fields as $field) : ?>
                    <th><?php echo "<?php echo \$this->Paginator->sort('{$field}'); ?>"; ?></th>
<?php endforeach; ?>
                    <th class="actions"><?php echo "<?php echo __('Actions'); ?>"; ?></th>
                </tr>
            </thead>
            <tbody>
                <?php echo "<?php foreach (\${$pluralVar} as \${$singularVar}) : ?>\n"; ?>
                <tr>
<?php
foreach ($fields as $field) {
    $isKey = false;
    if (!empty($associations['belongsTo'])) {
        foreach ($associations['belongsTo'] as $alias => $details) {
            if ($field === $details['foreignKey']) {
                $isKey = true;
                echo "                    <td><?php echo !empty(\${$singularVar}['{$alias}']['{$details['primaryKey']}']) ? \$this->Html->link(\${$singularVar}['{$alias}']['{$details['displayField']}'], ['controller' => '{$details['controller']}', 'action' => 'view', \${$singularVar}['{$alias}']['{$details['primaryKey']}']]) : ''; ?></td>\n";
                break;
            }
        }
    }
    if ($isKey !== true) {
        $value = "\${$singularVar}['{$modelClass}']['{$field}']";
        $type = isset($schema[$field]['type']) ? $schema[$field]['type'] : null;
        if (in_array($type, ['integer', 'biginteger', 'smallinteger', 'tinyinteger'], true)) {
            if (!empty($schema[$field]['null'])) {
                echo "                    <td><?php echo {$value} === null ? '' : \$this->Number->format({$value}); ?></td>\n";
            } else {
                echo "                    <td><?php echo \$this->Number->format({$value}); ?></td>\n";
            }
        } elseif ($type === 'boolean') {
            echo "                    <td><?php echo {$value} ? '1' : ''; ?></td>\n";
        } else {
            echo "                    <td><?php echo h({$value}); ?></td>\n";
        }
    }
}
?>
                    <td class="actions">
                        <?php echo "<?php echo \$this->Html->link(__('View'), ['action' => 'view', {$pk}]); ?>\n"; ?>
                        <?php echo "<?php echo \$this->Html->link(__('Edit'), ['action' => 'edit', {$pk}]); ?>\n"; ?>
                        <?php echo "<?php echo \$this->Form->postLink(__('Delete'), ['action' => 'delete', {$pk}], ['confirm' => __('Are you sure you want to delete # %s?', {$pk})]); ?>\n"; ?>
                    </td>
                </tr>
                <?php echo "<?php endforeach; ?>\n"; ?>
            </tbody>
        </table>
    </div>
    <div class="paginator">
        <ul class="pagination">
            <?php echo "<?php\n"; ?>
            echo $this->Paginator->first('<< ' . __('first'), ['tag' => 'li']);
            echo $this->Paginator->prev('< ' . __('previous'), ['tag' => 'li'], null, ['tag' => 'li', 'class' => 'prev disabled', 'disabledTag' => 'a']);
            echo $this->Paginator->numbers(['tag' => 'li', 'separator' => '', 'currentTag' => 'a', 'currentClass' => 'active']);
            echo $this->Paginator->next(__('next') . ' >', ['tag' => 'li'], null, ['tag' => 'li', 'class' => 'next disabled', 'disabledTag' => 'a']);
            echo $this->Paginator->last(__('last') . ' >>', ['tag' => 'li']);
            ?>
        </ul>
        <p><?php echo "<?php echo \$this->Paginator->counter(['format' => __('Page {:page} of {:pages}, showing {:current} record(s) out of {:count} total')]); ?>"; ?></p>
    </div>
</div>
