<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?php echo __('Actions'); ?></h4>
            <?php echo $this->Html->link(__('Edit View Task Comment'), ['action' => 'edit', $viewTaskComment['ViewTaskComment']['id']], ['class' => 'side-nav-item']); ?>
            <?php echo $this->Form->postLink(__('Delete View Task Comment'), ['action' => 'delete', $viewTaskComment['ViewTaskComment']['id']], ['class' => 'side-nav-item', 'confirm' => __('Are you sure you want to delete # %s?', $viewTaskComment['ViewTaskComment']['id'])]); ?>
            <?php echo $this->Html->link(__('List View Task Comments'), ['action' => 'index'], ['class' => 'side-nav-item']); ?>
            <?php echo $this->Html->link(__('New View Task Comment'), ['action' => 'add'], ['class' => 'side-nav-item']); ?>
            <?php echo $this->Html->link(__('List View Task Articles'), ['controller' => 'view_task_articles', 'action' => 'index'], ['class' => 'side-nav-item']); ?>
            <?php echo $this->Html->link(__('New Article'), ['controller' => 'view_task_articles', 'action' => 'add'], ['class' => 'side-nav-item']); ?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="viewTaskComments view content">
            <h3><?php echo h($viewTaskComment['ViewTaskComment']['id']); ?></h3>
            <table>
                <tr>
                    <th><?php echo __('Article'); ?></th>
                    <td><?php echo !empty($viewTaskComment['Article']['id']) ? $this->Html->link($viewTaskComment['Article']['title'], ['controller' => 'view_task_articles', 'action' => 'view', $viewTaskComment['Article']['id']]) : ''; ?></td>
                </tr>
                <tr>
                    <th><?php echo __('Published'); ?></th>
                    <td><?php echo h($viewTaskComment['ViewTaskComment']['published']); ?></td>
                </tr>
                <tr>
                    <th><?php echo __('Id'); ?></th>
                    <td><?php echo $this->Number->format($viewTaskComment['ViewTaskComment']['id']); ?></td>
                </tr>
                <tr>
                    <th><?php echo __('User Id'); ?></th>
                    <td><?php echo $this->Number->format($viewTaskComment['ViewTaskComment']['user_id']); ?></td>
                </tr>
                <tr>
                    <th><?php echo __('Created'); ?></th>
                    <td><?php echo h($viewTaskComment['ViewTaskComment']['created']); ?></td>
                </tr>
                <tr>
                    <th><?php echo __('Updated'); ?></th>
                    <td><?php echo h($viewTaskComment['ViewTaskComment']['updated']); ?></td>
                </tr>
            </table>
            <div class="text">
                <strong><?php echo __('Comment'); ?></strong>
                <blockquote>
                    <?php echo $this->Text->autoParagraph(h($viewTaskComment['ViewTaskComment']['comment'])); ?>
                </blockquote>
            </div>
        </div>
    </div>
</div>
