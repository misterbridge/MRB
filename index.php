<?php
/**
 * This is the bootstrap of the application, every single request will go through this file.
 */

/**
 * Activate / unactivate errors displaying on live
 */
ini_set('display_errors','On');
error_reporting(E_ALL);

/**
 * Change the Directory Separator here
 */
define('DS', '/');

/**
 * The base path to the root directory of the application
 * @var String
 */
// $basePath = dirname(__DIR__);
$basePath = DS . 'Developments' . DS . 'tamou.net' . DS;
// $basePath = DS . 'dev' . DS . 'v2' . DS;

/**
 * Global variables for
 * - Application tier path
 * - Base root path
 */
define('APP_PATH', 'app' . DS); // with trailing slash pls
define('BASE_PATH', $basePath); // with trailing slash pls

session_start();

// setlocale(LC_ALL, "FR");
setlocale(LC_ALL, 'fr_FR', 'fra');
date_default_timezone_set('Europe/Paris');

/**
 * Load Models (and Helpers) when they are needed
 * @param  string $class
 * @return void
 * @example
 *     new Http()
 *     -> MRB/Http.php needs to be required
 */
function __autoload($class)
{
    // Replace backslashes or underscores by the directory separator on the name of the class
    $path = str_replace(array('\\', '_'), DS, $class);

    if(file_exists('MRB' . DS . $path . '.php')) {
        // Direct MRB class is called
        $path  = 'MRB' . DS . $path;
    }

    require_once($path . '.php');
}


$http = new Http();
$http->startMvc();
