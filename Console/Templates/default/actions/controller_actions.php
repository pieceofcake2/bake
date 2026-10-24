<?php
/**
 * Bake Template for Controller action generation.
 *
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link          https://cakephp.org CakePHP(tm) Project
 * @package       Cake.Console.Templates.default.actions
 * @since         CakePHP(tm) v 1.3
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */

// The lists of the associated models for the add and edit forms
$lists = '';
$compact = [];
foreach (['belongsTo', 'hasAndBelongsToMany'] as $assoc) {
    foreach ($modelObj->{$assoc} as $associationName => $relation) {
        if (!empty($associationName)) {
            $otherModelName = $this->_modelName($associationName);
            $otherPluralName = $this->_pluralName($associationName);
            $lists .= "        \${$otherPluralName} = \$this->{$currentModelName}->{$otherModelName}->find('list');\n";
            $compact[] = "'{$otherPluralName}'";
        }
    }
}
if (!empty($compact)) {
    $lists .= "        \$this->set(compact(" . implode(', ', $compact) . "));\n";
}
$notFound = "        if (!\$this->{$currentModelName}->exists(\$id)) {\n" .
    "            throw new NotFoundException(__('Invalid " . strtolower($singularHumanName) . "'));\n" .
    "        }\n";
$saved = strtolower($singularHumanName);
?>
    /**
     * <?php echo $admin ?>index method
     *
     * @return void
     */
    public function <?php echo $admin ?>index()
    {
        $this-><?php echo $currentModelName ?>->recursive = 0;
        $this->set('<?php echo $pluralName ?>', $this->Paginator->paginate());
    }

    /**
     * <?php echo $admin ?>view method
     *
     * @param string|null $id
     * @return void
     * @throws NotFoundException
     */
    public function <?php echo $admin ?>view($id = null)
    {
<?php echo $notFound; ?>
        $options = ['conditions' => ['<?php echo $currentModelName; ?>.' . $this-><?php echo $currentModelName; ?>->primaryKey => $id]];
        $this->set('<?php echo $singularName; ?>', $this-><?php echo $currentModelName; ?>->find('first', $options));
    }

    /**
     * <?php echo $admin ?>add method
     *
     * @return CakeResponse|null|void Redirects on a successful add, renders the view otherwise.
     */
    public function <?php echo $admin ?>add()
    {
        if ($this->request->is('post')) {
            $this-><?php echo $currentModelName; ?>->create();
            if ($this-><?php echo $currentModelName; ?>->save($this->request->data)) {
<?php if ($wannaUseSession): ?>
                $this->Flash->success(__('The <?php echo $saved; ?> has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The <?php echo $saved; ?> could not be saved. Please, try again.'));
<?php else: ?>
                return $this->flash(__('The <?php echo $saved; ?> has been saved.'), ['action' => 'index']);
            }
<?php endif; ?>
        }
<?php echo $lists; ?>
    }

    /**
     * <?php echo $admin ?>edit method
     *
     * @param string|null $id
     * @return CakeResponse|null|void Redirects on a successful edit, renders the view otherwise.
     * @throws NotFoundException
     */
    public function <?php echo $admin; ?>edit($id = null)
    {
<?php echo $notFound; ?>
        if ($this->request->is(['post', 'put'])) {
            if ($this-><?php echo $currentModelName; ?>->save($this->request->data)) {
<?php if ($wannaUseSession): ?>
                $this->Flash->success(__('The <?php echo $saved; ?> has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The <?php echo $saved; ?> could not be saved. Please, try again.'));
<?php else: ?>
                return $this->flash(__('The <?php echo $saved; ?> has been saved.'), ['action' => 'index']);
            }
<?php endif; ?>
        } else {
            $options = ['conditions' => ['<?php echo $currentModelName; ?>.' . $this-><?php echo $currentModelName; ?>->primaryKey => $id]];
            $this->request->data = $this-><?php echo $currentModelName; ?>->find('first', $options);
        }
<?php echo $lists; ?>
    }

    /**
     * <?php echo $admin ?>delete method
     *
     * @param string|null $id
     * @return CakeResponse|null Redirects to the index.
     * @throws NotFoundException
     */
    public function <?php echo $admin; ?>delete($id = null)
    {
<?php echo $notFound; ?>
        $this->request->allowMethod('post', 'delete');
<?php if ($wannaUseSession): ?>
        if ($this-><?php echo $currentModelName; ?>->delete($id)) {
            $this->Flash->success(__('The <?php echo $saved; ?> has been deleted.'));
        } else {
            $this->Flash->error(__('The <?php echo $saved; ?> could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
<?php else: ?>
        if ($this-><?php echo $currentModelName; ?>->delete($id)) {
            return $this->flash(__('The <?php echo $saved; ?> has been deleted.'), ['action' => 'index']);
        }

        return $this->flash(__('The <?php echo $saved; ?> could not be deleted. Please, try again.'), ['action' => 'index']);
<?php endif; ?>
    }
