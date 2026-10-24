<?php
/**
 * A model of a namespaced application, for the tests of bake.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */

namespace App\Model;

use Cake\Model\Model;

/**
 * BakeArticle
 */
class BakeArticle extends Model
{
    /**
     * @var array
     */
    public $hasMany = ['BakeComment'];

    /**
     * @var array
     */
    public $hasAndBelongsToMany = ['BakeTag'];

    /**
     * A method that the baked test case tests
     *
     * @return void
     */
    public function publish()
    {
    }
}
