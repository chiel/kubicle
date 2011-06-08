<?php if (!defined('WWW')) exit('No direct scritp access allowed.');
/**
 * @author Chiel Kunkels <hello@chielkunkels.com>
 */
/**
 * Dump a variable to the output buffer in a readable format
 *
 * @param mixed   The variable to dump
 * @param string   An optional debug label
 */
function dump($var, $label = '')
{
	echo '<pre style="background:#fff;color:#000;font:9px monaco,courier,monospace;">'."\n";
	echo '=============== BEGIN DUMP: '.$label.' ==============='."\n";
	print_r($var);
	echo "\n".'================ END DUMP: '.$label.' ================';
	echo '</pre>'."\n";
}

/**
 * Auto-loader for classes
 */
function __autoload($class)
{
	if (file_exists(MOD.'/'.$class.'.php')) {
		include_once(MOD.'/'.$class.'.php');
	} elseif (file_exists(LIB.'/'.$class.'.php')) {
		include_once(LIB.'/'.$class.'.php');
	}
}

function path()
{
	static $path;

	// Check if it's not been fetched already
	if (!isset($path)) {
		if (isset($_SERVER['REQUEST_URI'])) {
			$uri = strtok($_SERVER['REQUEST_URI'], '?');
			$ln = strlen(rtrim(dirname($_SERVER['SCRIPT_NAME']), '\/'));
			$path = substr(urldecode($uri), $ln + 1);
		}

		// This is the front page
		else {
			$path = '';
		}

		// Make sure there's no slashes at start/end
		$path = trim($path, '/');
	}

	return $path;
}

/**
 * Custom exception handler
 */
function _exception()
{
	echo '_exception handler';
}
