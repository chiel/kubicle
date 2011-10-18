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
	$label = !empty($label) ? ' '.$label.' ' : '';
	echo '<pre style="background:#fff;color:#000;font:11px monaco,courier,monospace;">';
	echo '+++++++++++++++++++'.$label.'+++++++++++++++++++'."\n";
	print_r($var);
	echo "\n".'-------------------'.$label.'-------------------';
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
	} else {
		throw new Exception('Failed to find a class with name '.$class);
	}
}

function path()
{
	static $path;

	if (!isset($path)) {
		if (isset($_SERVER['REQUEST_URI'])) {
			$uri = strtok($_SERVER['REQUEST_URI'], '?');
			$ln = strlen(rtrim(dirname($_SERVER['SCRIPT_NAME']), '\/'));
			$path = substr(urldecode($uri), $ln + 1);
		} else {
			$path = '';
		}
		$path = trim($path, '/');
	}

	return $path;
}

/**
 * Custom uncaught exception handler
 */
function _exception()
{
	echo '_exception handler';
}

/**
 * Convert an underscored string into a camelcase one
 */
function underscoreToCamel($string)
{
	$parts = explode('_', $string);
	foreach ($parts as $part) {
		$fl = substr($part, 0, 1);
		$rest = substr($part, 1, strlen($part) - 1);
		return strtoupper($fl).$rest;
	}
}
