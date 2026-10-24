<?php
/**
 * Bootstrap for PHPUnit
 *
 * As in pieceofcake2/app, the tests run with APP_ENV=test by default (an APP_ENV from the environment wins), so
 * config/core.php of pieceofcake2/app loads its config/.env.test. Set the test database with DATABASE_URL.
 */

use Composer\InstalledVersions;

if ((string)getenv('APP_ENV') === '' && empty($_SERVER['APP_ENV'])) {
    putenv('APP_ENV=test');
    $_ENV['APP_ENV'] = 'test';
    $_SERVER['APP_ENV'] = 'test';
}

define('DS', DIRECTORY_SEPARATOR);
define('VENDORS', dirname(__DIR__) . DS . 'vendor' . DS);
define('ROOT', VENDORS . 'pieceofcake2' . DS . 'app');

require_once InstalledVersions::getInstallPath('pieceofcake2/cakephp') . DS . 'tests' . DS . 'bootstrap.php';

CakePlugin::load('Bake', [
    'path' => dirname(__DIR__) . DS,
]);
