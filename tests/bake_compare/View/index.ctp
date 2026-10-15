<div class="viewTaskComments index content">
    <?php echo $this->Html->link(__('New View Task Comment'), array('action' => 'add'), array('class' => 'button float-right')); ?>
    <h3><?php echo __('View Task Comments'); ?></h3>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th><?php echo $this->Paginator->sort('id'); ?></th>
                    <th><?php echo $this->Paginator->sort('article_id'); ?></th>
                    <th><?php echo $this->Paginator->sort('user_id'); ?></th>
                    <th><?php echo $this->Paginator->sort('published'); ?></th>
                    <th><?php echo $this->Paginator->sort('created'); ?></th>
                    <th><?php echo $this->Paginator->sort('updated'); ?></th>
                    <th class="actions"><?php echo __('Actions'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($viewTaskComments as $viewTaskComment): ?>
                <tr>
                    <td><?php echo $this->Number->format($viewTaskComment['ViewTaskComment']['id']); ?></td>
                    <td><?php echo !empty($viewTaskComment['Article']['id']) ? $this->Html->link($viewTaskComment['Article']['title'], array('controller' => 'view_task_articles', 'action' => 'view', $viewTaskComment['Article']['id'])) : ''; ?></td>
                    <td><?php echo $this->Number->format($viewTaskComment['ViewTaskComment']['user_id']); ?></td>
                    <td><?php echo h($viewTaskComment['ViewTaskComment']['published']); ?></td>
                    <td><?php echo h($viewTaskComment['ViewTaskComment']['created']); ?></td>
                    <td><?php echo h($viewTaskComment['ViewTaskComment']['updated']); ?></td>
                    <td class="actions">
                        <?php echo $this->Html->link(__('View'), array('action' => 'view', $viewTaskComment['ViewTaskComment']['id'])); ?>
                        <?php echo $this->Html->link(__('Edit'), array('action' => 'edit', $viewTaskComment['ViewTaskComment']['id'])); ?>
                        <?php echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $viewTaskComment['ViewTaskComment']['id']), array('confirm' => __('Are you sure you want to delete # %s?', $viewTaskComment['ViewTaskComment']['id']))); ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="paginator">
        <ul class="pagination">
            <?php
            echo $this->Paginator->first('<< ' . __('first'), array('tag' => 'li'));
            echo $this->Paginator->prev('< ' . __('previous'), array('tag' => 'li'), null, array('tag' => 'li', 'class' => 'prev disabled', 'disabledTag' => 'a'));
            echo $this->Paginator->numbers(array('tag' => 'li', 'separator' => '', 'currentTag' => 'a', 'currentClass' => 'active'));
            echo $this->Paginator->next(__('next') . ' >', array('tag' => 'li'), null, array('tag' => 'li', 'class' => 'next disabled', 'disabledTag' => 'a'));
            echo $this->Paginator->last(__('last') . ' >>', array('tag' => 'li'));
            ?>
        </ul>
        <p><?php echo $this->Paginator->counter(array('format' => __('Page {:page} of {:pages}, showing {:current} record(s) out of {:count} total'))); ?></p>
    </div>
</div>
