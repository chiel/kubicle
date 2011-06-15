<?php if (!defined('WWW')) exit('No direct scritp access allowed.');
/**
 * @author Chiel Kunkels <hello@chielkunkels.com>
 */
require LIB.'/Common.php';

// Admin paths
Route::respond('admin/[:controller]?/[:action]?', function($controller = null, $action = 'list') {
	dump($controller, 'controller');
	dump($action, 'action');
});

// Catch-all
Route::respond('*', function() {
	dump('match all');
});

// See what got caught in the net
Route::dispatch();
