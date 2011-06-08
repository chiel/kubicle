<?php if (!defined('WWW')) exit('No direct scritp access allowed.');
/**
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
	public static function respond($path, $callback)
	{
		if (is_callable($callback)) {
			self::$routes[$path] = $callback;
		}
	}

	/**
	 * Dispatch the request to the matching route
	 */
	public static function dispatch()
	{
		$uri = path();

		foreach (self::$routes as $route => $callback) {
			// ! negates a match
			if ('!' === $route{0}) {
				dump($route, 'negated');
				$negate = true;
				$i = 1;
			} else {
				$negate = false;
				$i = 0;
			}

			// * matches all
			if ('*' === $route) {
				$match = true;
			}

			// @ is for a custom regex
			elseif('@' === $route{$i}) {
				$match = preg_match('`'.substr($route, $i + 1).'`', $uri, $params);
			}

			//
			else {
				$regex = self::regex($route);
				dump($regex);
				$match = preg_match($regex, $uri, $params);
				dump($params);
			}

			if ($negate ^ $match) {
				dump($route, 'we have a match!');
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
