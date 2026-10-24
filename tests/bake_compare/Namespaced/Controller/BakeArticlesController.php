<?php

namespace App\Controller;

use App\Model\BakeArticle;
use Cake\Controller\Component\FlashComponent;
use Cake\Controller\Component\PaginatorComponent;
use Cake\Controller\Component\SessionComponent;
use Cake\Error\NotFoundException;
use Cake\Network\CakeResponse;
use Cake\View\Helper\JsHelper;
use Cake\View\Helper\TimeHelper;

/**
 * BakeArticles Controller
 *
 * @property BakeArticle $BakeArticle
 * @property SessionComponent $Session
 * @property FlashComponent $Flash
 * @property BakeMissingComponent $BakeMissing
 * @property PaginatorComponent $Paginator
 */
class BakeArticlesController extends AppController
{
    /**
     * Helpers
     *
     * @var array
     */
    public $helpers = [JsHelper::class, TimeHelper::class];

    /**
     * Components
     *
     * @var array
     */
    public $components = [SessionComponent::class, FlashComponent::class, 'BakeMissing', PaginatorComponent::class];

    /**
     * index method
     *
     * @return void
     */
    public function index()
    {
        $this->BakeArticle->recursive = 0;
        $this->set('bakeArticles', $this->Paginator->paginate());
    }

    /**
     * view method
     *
     * @param string|null $id
     * @return void
     * @throws NotFoundException
     */
    public function view($id = null)
    {
        if (!$this->BakeArticle->exists($id)) {
            throw new NotFoundException(__('Invalid bake article'));
        }
        $options = ['conditions' => ['BakeArticle.' . $this->BakeArticle->primaryKey => $id]];
        $this->set('bakeArticle', $this->BakeArticle->find('first', $options));
    }

    /**
     * add method
     *
     * @return CakeResponse|null|void Redirects on a successful add, renders the view otherwise.
     */
    public function add()
    {
        if ($this->request->is('post')) {
            $this->BakeArticle->create();
            if ($this->BakeArticle->save($this->request->data)) {
                $this->Flash->success(__('The bake article has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The bake article could not be saved. Please, try again.'));
        }
        $bakeTags = $this->BakeArticle->BakeTag->find('list');
        $this->set(compact('bakeTags'));
    }

    /**
     * edit method
     *
     * @param string|null $id
     * @return CakeResponse|null|void Redirects on a successful edit, renders the view otherwise.
     * @throws NotFoundException
     */
    public function edit($id = null)
    {
        if (!$this->BakeArticle->exists($id)) {
            throw new NotFoundException(__('Invalid bake article'));
        }
        if ($this->request->is(['post', 'put'])) {
            if ($this->BakeArticle->save($this->request->data)) {
                $this->Flash->success(__('The bake article has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The bake article could not be saved. Please, try again.'));
        } else {
            $options = ['conditions' => ['BakeArticle.' . $this->BakeArticle->primaryKey => $id]];
            $this->request->data = $this->BakeArticle->find('first', $options);
        }
        $bakeTags = $this->BakeArticle->BakeTag->find('list');
        $this->set(compact('bakeTags'));
    }

    /**
     * delete method
     *
     * @param string|null $id
     * @return CakeResponse|null Redirects to the index.
     * @throws NotFoundException
     */
    public function delete($id = null)
    {
        if (!$this->BakeArticle->exists($id)) {
            throw new NotFoundException(__('Invalid bake article'));
        }
        $this->request->allowMethod('post', 'delete');
        if ($this->BakeArticle->delete($id)) {
            $this->Flash->success(__('The bake article has been deleted.'));
        } else {
            $this->Flash->error(__('The bake article could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
