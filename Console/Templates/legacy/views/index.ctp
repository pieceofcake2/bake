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
<div class="<?php echo $pluralVar; ?> index">
    <h2><?php echo "<?php echo __('{$pluralHumanName}'); ?>"; ?></h2>
    <table cellpadding="0" cellspacing="0">
    <thead>
    <tr>
    <?php foreach ($fields as $field) : ?>
        <th><?php echo "<?php echo \$this->Paginator->sort('{$field}'); ?>"; ?></th>
    <?php endforeach; ?>
        <th class="actions"><?php echo "<?php echo __('Actions'); ?>"; ?></th>
    </tr>
    </thead>
    <tbody>
    <?php
    echo "<?php foreach (\${$pluralVar} as \${$singularVar}) : ?>\n";
    echo "    <tr>\n";
        foreach ($fields as $field) {
            $isKey = false;
            if (!empty($associations['belongsTo'])) {
                foreach ($associations['belongsTo'] as $alias => $details) {
                    if ($field === $details['foreignKey']) {
                        $isKey = true;
                        echo "        <td>\n            <?php echo \$this->Html->link(\${$singularVar}['{$alias}']['{$details['displayField']}'], ['controller' => '{$details['controller']}', 'action' => 'view', \${$singularVar}['{$alias}']['{$details['primaryKey']}']]); ?>\n        </td>\n";
                        break;
                    }
                }
            }
            if ($isKey !== true) {
                echo "        <td><?php echo h(\${$singularVar}['{$modelClass}']['{$field}']); ?>&nbsp;</td>\n";
            }
        }

        echo "        <td class=\"actions\">\n";
        echo "            <?php echo \$this->Html->link(__('View'), ['action' => 'view', \${$singularVar}['{$modelClass}']['{$primaryKey}']]); ?>\n";
        echo "            <?php echo \$this->Html->link(__('Edit'), ['action' => 'edit', \${$singularVar}['{$modelClass}']['{$primaryKey}']]); ?>\n";
        echo "            <?php echo \$this->Form->postLink(__('Delete'), ['action' => 'delete', \${$singularVar}['{$modelClass}']['{$primaryKey}']], ['confirm' => __('Are you sure you want to delete # %s?', \${$singularVar}['{$modelClass}']['{$primaryKey}'])]); ?>\n";
        echo "        </td>\n";
    echo "    </tr>\n";

    echo "    <?php endforeach; ?>\n";
    ?>
    </tbody>
    </table>
    <p>
    <?php echo "<?php
    echo \$this->Paginator->counter([
        'format' => __('Page {:page} of {:pages}, showing {:current} records out of {:count} total, starting on record {:start}, ending on {:end}'),
    ]);
    ?>\n"; ?>
    </p>
    <div class="paging">
    <?php
        echo "<?php\n";
        echo "        echo \$this->Paginator->prev('< ' . __('previous'), [], null, ['class' => 'prev disabled']);\n";
        echo "        echo \$this->Paginator->numbers(['separator' => '']);\n";
        echo "        echo \$this->Paginator->next(__('next') . ' >', [], null, ['class' => 'next disabled']);\n";
        echo "    ?>\n";
    ?>
    </div>
</div>
<div class="actions">
    <h3><?php echo "<?php echo __('Actions'); ?>"; ?></h3>
    <ul>
        <li><?php echo "<?php echo \$this->Html->link(__('New " . $singularHumanName . "'), ['action' => 'add']); ?>"; ?></li>
<?php
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
