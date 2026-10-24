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
                return $this->flash(__('The bake article has been saved.'), ['action' => 'index']);
            }
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
                return $this->flash(__('The bake article has been saved.'), ['action' => 'index']);
            }
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
            return $this->flash(__('The bake article has been deleted.'), ['action' => 'index']);
        }

        return $this->flash(__('The bake article could not be deleted. Please, try again.'), ['action' => 'index']);
    }
