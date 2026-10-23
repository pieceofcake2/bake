<?php
/**
 * Articles Controller
 *
 * @property Article $Article
 * @property AclComponent $Acl
 * @property AuthComponent $Auth
 * @property PaginatorComponent $Paginator
 */
class ArticlesController extends AppController
{
    /**
     * Helpers
     *
     * @var array
     */
    public $helpers = ['Js', 'Time'];

    /**
     * Components
     *
     * @var array
     */
    public $components = ['Acl', 'Auth', 'Paginator'];
}
