<?php if (!defined('WWW')) exit('No direct scritp access allowed.');
/**
 * Maps requests to callbacks.
 *
 * Heavily inspired by klein.php.
 * - https://github.com/chriso/klein.php
 *
 * @author Chiel Kunkels <hello@chielkunkels.com>
 */
class Route {
	/**
	 * @var array   Array of available routes
	 */
	private static $routes = array();
	/**
	 * @var array   Allowed types for matching
	 */
	private static $types = array(
		'' => '[^/]++',
		'i' => '[0-9]++',
		'a' => '[0-9A-Za-z]++',
		'h' => '[0-9A-Fa-f]++',
		'*' => '.+?',
		'**' => '.++'
	);

	/**
	 * Add a callback to a path
	 *
	 * @param string $path   The path to respond to
	 * @param function $callback   The function to execute
	 */
	public static function respond($method, $path, $callback = null)
	{
		if (null === $callback) {
			$callback = $path;
			$path = $method;
			$method = 'GET';
		}

		if (is_callable($callback)) {
			if (!is_array($method)) {
				$method = array($method);
			}
			$method = explode(',',strtoupper(implode(',',$method)));
			self::$routes[] = array($method, $path, $callback);
		} else {
			echo 'Can\'t call provided callback.<br>'."\n";
		}
	}

	/**
	 * Dispatch the request to the matching route
	 */
	public static function dispatch()
	{
		$req_method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';
		$req_path = path();

		foreach (self::$routes as $route) {
			list($method, $path, $callback) = $route;

			// Check if method matches first
			if (!in_array($req_method, $method)) {
				continue;
			}

			// ! negates a match
			if ('!' === $path{0}) {
				$negate = true;
				$i = 1;
			} else {
				$negate = false;
				$i = 0;
			}

			$params = array();

			// * matches all
			if ('*' === $path) {
				$match = true;
			}

			// @ is for a custom regex
			elseif('@' === $path{$i}) {
				$match = preg_match('`'.substr($path, $i + 1).'`', $req_path, $params);
			}

			// everything else
			else {
				$regex = self::regex($path);
				$match = preg_match($regex, $req_path, $params);
				array_shift($params);
			}

			if ($negate ^ $match) {
				try {
					call_user_func_array($callback, $params);
				} catch (Exception $e) {
					dump($e);
				}
				break;
			}
		}
	}

	/**
	 * Put together a regex to match a route
	 */
	private static function regex($route)
	{
		if (preg_match_all('`(/|\.|)\[([^:\]]*+)(?::([^:\]]*+))?\](\?|)`', $route, $matches, PREG_SET_ORDER)) {
			foreach ($matches as $match) {
				list($block, $pre, $type, $param, $optional) = $match;

				if (isset(self::$types[$type])) {
					$type = self::$types[$type];
				}
				if ('.' === $pre) {
					$pre = '\.';
				}
				$pattern = '(?:'
					.($pre !== '' ? $pre : '')
					.'('
					/*.($param !== '' ? '?P<'.$param.'>' : '')*/
					.$type
					.'))'
					.($optional !== '' ? '?' : '');
				$route = str_replace($block, $pattern, $route);
			}
			$route = $route.'/?';
		}
		return '`^'.$route.'$`';
	}
}
