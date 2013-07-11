<?php
/**
 *
 * @package awards mod
 * @version 1.0
 * @author Jessica González <suki@missallsunday.com>
 * @copyright Copyright (c) 2013, Jessica González
 * @license http://www.mozilla.org/MPL/MPL-1.1.html
 */

	if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
		require_once(dirname(__FILE__) . '/SSI.php');

	elseif (!defined('SMF'))
		exit('<b>Error:</b> Cannot install - please verify you put this in the same place as SMF\'s index.php.');

	// Everybody likes hooks
	$hooks = array(
		'integrate_pre_include' => '$sourcedir/OAward.php',
		'integrate_actions' => 'OAward::actions',
		'integrate_admin_areas' => 'OAward::admin_areas',
		'integrate_modify_modifications' => 'OAward::modify_modifications',
	);

	$call = 'add_integration_function';

	foreach ($hooks as $hook => $function)
		$call($hook, $function);
