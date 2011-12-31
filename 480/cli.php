#!/usr/local/bin/php
<?php
/**
 * Agavi Front Controller - boots the agavi app for console
 *
 * @package    Core
 * @subpackage Index
 *
 * @author     Library House
 * @copyright  (c) Authors
 * @since      0.1
 *
 * @version    $Id$
 */

error_reporting(E_ALL | E_STRICT);

// +---------------------------------------------------------------------------+
// | An absolute filesystem path to the agavi/agavi.php script.                |
// +---------------------------------------------------------------------------+
require(realpath(dirname(dirname(__FILE__)).'/libs/agavi/agavi.php'));

// +---------------------------------------------------------------------------+
// | An absolute filesystem path to our app/config.php script.                 |
// +---------------------------------------------------------------------------+
require(realpath(dirname(dirname(__FILE__)).'/app/config.php'));


// +---------------------------------------------------------------------------+
// | Initialize the framework. You may pass an environment name to this method.|
// | By default the 'development' environment sets Agavi into a debug mode.    |
// | In debug mode among other things the cache is cleaned on every request.   |
// +---------------------------------------------------------------------------+

if (!empty($_SERVER['AGAVI_ENVIRONEMENT'])) {
	$env = $_SERVER['AGAVI_ENVIRONEMENT'];
} elseif (!empty($_ENV['AGAVI_ENVIRONEMENT'])) {
	$env = $_ENV['AGAVI_ENVIRONEMENT'];
} else {
	$env = 'development';
}

Agavi::bootstrap($env);

// +---------------------------------------------------------------------------+
// | Call the controller's dispatch method on the default context              |
// +---------------------------------------------------------------------------+
AgaviContext::getInstance('console')->getController()->dispatch();

?>
