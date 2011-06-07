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
	 * Add a callback to a path
	 *
	 * @param string $path   The path to respond to
	 * @param function $callback   The function to execute
	 */
	public static function respond($path, $callback)
	{
		//
	}

	/**
	 * Dispatch the request to the matching route
	 */
	public static function dispatch()
	{
		//
	}
}
