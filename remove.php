<?php

/**
 *
 * @package awards mod
 * @version 1.0
 * @author Jessica Gonz�lez <suki@missallsunday.com>
 * @copyright Copyright (c) 2013, Jessica Gonz�lez
 * @license http://www.mozilla.org/MPL/MPL-1.1.html
 */


	if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
		require_once(dirname(__FILE__) . '/SSI.php');

	elseif (!defined('SMF'))
		exit('<b>Error:</b> Cannot install - please verify you put this in the same place as SMF\'s index.php.');

	// Everybody likes hooks
	$hooks = array(
		'integrate_actions' => 'OAward_actions',
		'integrate_admin_areas' => 'OAward_admin_areas',
		'integrate_modify_modifications' => 'OAward_modifications',
		'integrate_pre_include' => '$sourcedir/OAward.php',
	);

	// Uninstall please
	$call = 'remove_integration_function';

	foreach ($hooks as $hook => $function)
		$call($hook, $function);
