<?php
/**
 * Web Access Frontend for TestSuite
 *
 * @link          https://book.cakephp.org/2.0/en/development/testing.html
 * @package       app.webroot
 * @since         CakePHP(tm) v 1.2.0.4433
 */

use Composer\InstalledVersions;

set_time_limit(0);
ini_set('display_errors', 1);

/**
 * Use the DS to separate the directories in other defines
 */
if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}

/**
 * These defines should only be edited if you have CakePHP installed in
 * a directory layout other than the way it is distributed.
 * When using custom settings be sure to use the DS and do not add a trailing DS.
 */

/**
 * The full path to the directory which holds "app", WITHOUT a trailing DS.
 */
if (!defined('ROOT')) {
    define('ROOT', dirname(__DIR__, 2));
}

/**
 * The actual directory name for the "app".
 */
if (!defined('APP_DIR')) {
    define('APP_DIR', basename(dirname(__DIR__)));
}

/**
 * Path to the Composer vendor directory, which holds "autoload.php".
 * Change this if "vendor-dir" is configured in composer.json.
 */
if (!defined('VENDORS')) {
    define('VENDORS', ROOT . DS . 'vendor' . DS);
}

/**
 * Editing below this line should not be necessary.
 * Change at your own risk.
 */
if (!defined('WEBROOT_DIR')) {
    define('WEBROOT_DIR', basename(__DIR__));
}
if (!defined('WWW_ROOT')) {
    define('WWW_ROOT', __DIR__ . DS);
}

if (!is_file(VENDORS . 'autoload.php')) {
    throw new RuntimeException(
        'Composer autoload file not found at "' . VENDORS . 'autoload.php". ' .
        'Please run "composer install" in the project root directory to install dependencies.',
    );
}
require_once VENDORS . 'autoload.php';

require_once InstalledVersions::getInstallPath('pieceofcake2/cakephp') . DS . 'src' . DS . 'Cake' . DS . 'bootstrap.php';

if (Configure::read('debug') < 1) {
    throw new NotFoundException(__d('cake_dev', 'Debug setting does not allow access to this URL.'));
}

CakeTestSuiteDispatcher::run();
