<?php
/**
 *
 * @package OAwards mod
 * @version 1.0
 * @author Jessica González <suki@missallsunday.com>
 * @copyright Copyright (c) 2013, Jessica González
 * @license http://www.mozilla.org/MPL/MPL-1.1.html
 */

if (!defined('SMF'))
	die('No direct access...');

function OAward_actions(&$actions)
{
	global $sourcedir;

	// A whole new action just for some ajax calls...
	$actions['oaward'] = array('OAward.php', 'OAward::ajax');
}

function OAward_admin_areas(&$areas)
{
	global $txt;

	if (!isset($txt['OAward_main']))
		loadLanguage(OAward::$name);

	$areas['config']['areas']['modsettings']['subsections']['oaward'] = array($txt['OAward_main']);
}

function OAward_modifications(&$sub_actions)
{
	global $context;

	$sub_actions['oaward'] = 'OAward_settings';
	$context[$context['admin_menu_name']]['tab_data']['tabs']['oaward'] = array();
}

function OAward_admin($admin_menu)
{
	global $txt;

	if (!isset($txt['OAward_main']))
		loadLanguage(OAward::$name);

	$admin_menu['config']['areas']['oaward'] = array(
		'label' => $txt['OAward_main'],
		'file' => 'OAwardHooks.php',
		'function' => 'OAward_index',
		'icon' => 'administration.gif',
		'subsections' => array(
			'general' => $txt['OAward_admin_title_general'],
			'awards' => $txt['OAward_admin_title_edit'],
		),
	);
}

function OAward_index()
{
	global $txt, $scripturl, $context, $sourcedir;

	require_once($sourcedir . '/ManageSettings.php');
	loadLanguage(OAward::$name);
	$context['page_title'] = $txt['Breeze_admin_settings_admin_panel'];

	$subActions = array(
		'general' => 'OAward_settings',
		'awards' => 'OAward_settings_awards',
	);

	loadGeneralSettingParameters($subActions, 'general');

	$context[$context['admin_menu_name']]['tab_data'] = array(
		'tabs' => array(
			'general' => array(),
			'awards' => array(),
		),
	);

	call_user_func($subActions[$_REQUEST['sa']]);
}

function OAward_settings(&$return_config = false)
{
	global $scripturl, $context, $sourcedir, $settings, $txt;

	loadtemplate('Admin');

	// Load stuff
	$context['sub_template'] = 'show_settings';
	$context['page_title'] = $text->getText('admin_settings_main');
	$context[$context['admin_menu_name']]['tab_data'] = array(
		'title' => Breeze::$name .' - '. $text->getText('admin_settings_settings'),
		'description' => $text->getText('admin_settings_settings_desc'),
	);

	require_once($sourcedir . '/ManageServer.php');

	// Set a nice message in case there is no images folder...
	if (!file_get_contents($modSettings['OAward_admin_folder_url']))
		$context['settings_insert_above'] = '<div class="errorbox">' . $txt['OAward_error_no_valid_path'] . '</div>';

	// A bunch of config settings here...
	$config_vars = array(
		array('desc', 'OAward_admin_desc'),
		array('check', 'OAward_admin_enable', 'subtext' => $txt['OAward_admin_enable_sub']),
		array('text', 'OAward_admin_images_ext', 'subtext' => $txt['OAward_admin_images_ext_sub']),
		array('text', 'OAward_admin_folder_url', 'size'=> 45, 'subtext' => $txt['OAward_admin_folder_url_sub']),
	);

	if ($return_config)
		return $config_vars;

	$context['post_url'] = $scripturl . '?action=admin;area=modsettings;save;sa=oaward';
	$context['settings_title'] = $txt['OAward_main'];

	if (empty($config_vars))
	{
		$context['settings_save_dont_show'] = true;
		$context['settings_message'] = '<div align="center">' . $txt['modification_no_misc_settings'] . '</div>';

		return prepareDBSettingContext($config_vars);
	}

	if (isset($_GET['save']))
	{
		// Sorry, but this is mandatory...
		if (empty($_POST['OAward_admin_images_ext']))
			$_POST['OAward_admin_images_ext'] = 'png';

		// Gotta check if the user typed a dot...
		if (strstr($_POST['OAward_admin_images_ext'], '.') !== false)
			$_POST['OAward_admin_images_ext'] = str_replace('.', '', $_POST['OAward_admin_images_ext']);

		// If there isn't a custom folder path, set the default one
		if (empty($_POST['OAward_admin_folder_url']))
			$_POST['OAward_admin_folder_url'] = $settings['default_images_url'] . '/medals/';

		checkSession();
		$save_vars = $config_vars;
		saveDBSettings($save_vars);
		redirectexit('action=admin;area=modsettings;sa=oaward');
	}

	prepareDBSettingContext($config_vars);
}