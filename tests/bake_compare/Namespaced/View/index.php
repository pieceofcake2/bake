<div class="bakeArticles index content">
    <?php echo $this->Html->link(__('New Bake Article'), ['action' => 'add'], ['class' => 'button float-right']); ?>
    <h3><?php echo __('Bake Articles'); ?></h3>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th><?php echo $this->Paginator->sort('id'); ?></th>
                    <th><?php echo $this->Paginator->sort('bake_user_id'); ?></th>
                    <th><?php echo $this->Paginator->sort('title'); ?></th>
                    <th><?php echo $this->Paginator->sort('published'); ?></th>
                    <th><?php echo $this->Paginator->sort('created'); ?></th>
                    <th><?php echo $this->Paginator->sort('updated'); ?></th>
                    <th class="actions"><?php echo __('Actions'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($bakeArticles as $bakeArticle) : ?>
                <tr>
                    <td><?php echo $this->Number->format($bakeArticle['BakeArticle']['id']); ?></td>
                    <td><?php echo $this->Number->format($bakeArticle['BakeArticle']['bake_user_id']); ?></td>
                    <td><?php echo h($bakeArticle['BakeArticle']['title']); ?></td>
                    <td><?php echo h($bakeArticle['BakeArticle']['published']); ?></td>
                    <td><?php echo h($bakeArticle['BakeArticle']['created']); ?></td>
                    <td><?php echo h($bakeArticle['BakeArticle']['updated']); ?></td>
                    <td class="actions">
                        <?php echo $this->Html->link(__('View'), ['action' => 'view', $bakeArticle['BakeArticle']['id']]); ?>
                        <?php echo $this->Html->link(__('Edit'), ['action' => 'edit', $bakeArticle['BakeArticle']['id']]); ?>
                        <?php echo $this->Form->postLink(__('Delete'), ['action' => 'delete', $bakeArticle['BakeArticle']['id']], ['confirm' => __('Are you sure you want to delete # %s?', $bakeArticle['BakeArticle']['id'])]); ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="paginator">
        <ul class="pagination">
            <?php
            echo $this->Paginator->first('<< ' . __('first'), ['tag' => 'li']);
            echo $this->Paginator->prev('< ' . __('previous'), ['tag' => 'li'], null, ['tag' => 'li', 'class' => 'prev disabled', 'disabledTag' => 'a']);
            echo $this->Paginator->numbers(['tag' => 'li', 'separator' => '', 'currentTag' => 'a', 'currentClass' => 'active']);
            echo $this->Paginator->next(__('next') . ' >', ['tag' => 'li'], null, ['tag' => 'li', 'class' => 'next disabled', 'disabledTag' => 'a']);
            echo $this->Paginator->last(__('last') . ' >>', ['tag' => 'li']);
            ?>
        </ul>
        <p><?php echo $this->Paginator->counter(['format' => __('Page {:page} of {:pages}, showing {:current} record(s) out of {:count} total')]); ?></p>
    </div>
</div>
