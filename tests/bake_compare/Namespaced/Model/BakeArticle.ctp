<?php

namespace App\Model;

use Cake\Model\Behavior\TreeBehavior;

/**
 * BakeArticle Model
 *
 * @property BakeUser $BakeUser
 * @property BakeComment $BakeComment
 * @property BakeTag $BakeTag
 */
class BakeArticle extends AppModel {

/**
 * Display field
 *
 * @var string
 */
	public $displayField = 'title';

/**
 * Behaviors
 *
 * @var array
 */
	public $actsAs = array(
		TreeBehavior::class,
	);

/**
 * Validation rules
 *
 * @var array
 */
	public $validate = array(
		'title' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
	);

	// The Associations below have been created with all possible keys, those that are not needed can be removed

/**
 * belongsTo associations
 *
 * @var array
 */
	public $belongsTo = array(
		'BakeUser' => array(
			'className' => 'BakeUser',
			'foreignKey' => 'bake_user_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);

/**
 * hasMany associations
 *
 * @var array
 */
	public $hasMany = array(
		'BakeComment' => array(
			'className' => BakeComment::class,
			'foreignKey' => 'bake_article_id',
			'dependent' => false,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		)
	);


/**
 * hasAndBelongsToMany associations
 *
 * @var array
 */
	public $hasAndBelongsToMany = array(
		'BakeTag' => array(
			'className' => 'BakeTag',
			'joinTable' => 'bake_articles_bake_tags',
			'foreignKey' => 'bake_article_id',
			'associationForeignKey' => 'bake_tag_id',
			'unique' => 'keepExisting',
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'finderQuery' => '',
		)
	);

}
