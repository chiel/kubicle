<?php if (!defined('WWW')) exit('No direct scritp access allowed.');
/**
 * @author Chiel Kunkels <hello@chielkunkels.com>
 */
require LIB.'/Common.php';

// Admin paths
Route::respond('admin/[:controller]?/[:action]?/[i:id]?', function($controller = null, $action = 'index') {
	if (empty($controller)) {
		echo 'No controller found, means dashboard';
	} else {
		$controller = underscoreToCamel($controller);
		call_user_func($controller.'::'.$action);
	}
});

// Catch-all
Route::respond('*', function() {
	dump('match all');
});

// See what got caught in the net
Route::dispatch();
