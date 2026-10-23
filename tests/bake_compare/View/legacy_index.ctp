<div class="viewTaskComments index">
    <h2><?php echo __('View Task Comments'); ?></h2>
    <table cellpadding="0" cellspacing="0">
    <thead>
    <tr>
            <th><?php echo $this->Paginator->sort('id'); ?></th>
            <th><?php echo $this->Paginator->sort('article_id'); ?></th>
            <th><?php echo $this->Paginator->sort('user_id'); ?></th>
            <th><?php echo $this->Paginator->sort('comment'); ?></th>
            <th><?php echo $this->Paginator->sort('published'); ?></th>
            <th><?php echo $this->Paginator->sort('created'); ?></th>
            <th><?php echo $this->Paginator->sort('updated'); ?></th>
            <th class="actions"><?php echo __('Actions'); ?></th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($viewTaskComments as $viewTaskComment) : ?>
    <tr>
        <td><?php echo h($viewTaskComment['ViewTaskComment']['id']); ?>&nbsp;</td>
        <td>
            <?php echo $this->Html->link($viewTaskComment['Article']['title'], ['controller' => 'view_task_articles', 'action' => 'view', $viewTaskComment['Article']['id']]); ?>
        </td>
        <td><?php echo h($viewTaskComment['ViewTaskComment']['user_id']); ?>&nbsp;</td>
        <td><?php echo h($viewTaskComment['ViewTaskComment']['comment']); ?>&nbsp;</td>
        <td><?php echo h($viewTaskComment['ViewTaskComment']['published']); ?>&nbsp;</td>
        <td><?php echo h($viewTaskComment['ViewTaskComment']['created']); ?>&nbsp;</td>
        <td><?php echo h($viewTaskComment['ViewTaskComment']['updated']); ?>&nbsp;</td>
        <td class="actions">
            <?php echo $this->Html->link(__('View'), ['action' => 'view', $viewTaskComment['ViewTaskComment']['id']]); ?>
            <?php echo $this->Html->link(__('Edit'), ['action' => 'edit', $viewTaskComment['ViewTaskComment']['id']]); ?>
            <?php echo $this->Form->postLink(__('Delete'), ['action' => 'delete', $viewTaskComment['ViewTaskComment']['id']], ['confirm' => __('Are you sure you want to delete # %s?', $viewTaskComment['ViewTaskComment']['id'])]); ?>
        </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
    </table>
    <p>
    <?php
    echo $this->Paginator->counter([
        'format' => __('Page {:page} of {:pages}, showing {:current} records out of {:count} total, starting on record {:start}, ending on {:end}'),
    ]);
    ?>
    </p>
    <div class="paging">
    <?php
        echo $this->Paginator->prev('< ' . __('previous'), [], null, ['class' => 'prev disabled']);
        echo $this->Paginator->numbers(['separator' => '']);
        echo $this->Paginator->next(__('next') . ' >', [], null, ['class' => 'next disabled']);
    ?>
    </div>
</div>
<div class="actions">
    <h3><?php echo __('Actions'); ?></h3>
    <ul>
        <li><?php echo $this->Html->link(__('New View Task Comment'), ['action' => 'add']); ?></li>
        <li><?php echo $this->Html->link(__('List View Task Articles'), ['controller' => 'view_task_articles', 'action' => 'index']); ?> </li>
        <li><?php echo $this->Html->link(__('New Article'), ['controller' => 'view_task_articles', 'action' => 'add']); ?> </li>
    </ul>
</div>
