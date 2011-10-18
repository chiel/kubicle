<?php if (!defined('WWW')) exit('No direct scritp access allowed.');
/**
 * @author Chiel Kunkels <hello@chielkunkels.com>
 */
require LIB.'/Common.php';

// Admin paths
Route::respond('admin/[:controller]?/[:action]?/[i:id]?', '_routeAdmin');
function _routeAdmin($controller = null, $action = 'index', $id = 0) {
	if (empty($controller)) {
		$controller = 'dashboard';
	}
	$controllerCamel = underscoreToCamel($controller);

	try {
		require MOD.'/'.$controllerCamel.'.php';
		call_user_func($controllerCamel.'Controller::index', $action);
	} catch (ArgumentException $e) {
		dump($e);
	}
}

// Catch-all
Route::respond('*', '_routeAll');
function _routeAll() {
	dump('match all');
}

// See what got caught in the net
Route::dispatch();
