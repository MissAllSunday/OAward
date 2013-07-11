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
		loadLanguage(self::$name);

	$areas['config']['areas']['modsettings']['subsections']['oaward'] = array($txt['OAward_main']);
}

function OAward_modifications(&$sub_actions)
{
	global $context;

	$sub_actions['faq'] = 'OAward_settings';
	$context[$context['admin_menu_name']]['tab_data']['tabs']['oaward'] = array();
}

function OAward_settings(&$return_config = false)
{
	global $context, $scripturl, $txt;

	// A bunch of config settings here...
	$config_vars = array(
		array('desc', 'OAward_admin_desc'),
		array('check', 'OAward_admin_enable', 'subtext' => $txt['OAward_admin_enable_sub']),
		array('text', 'OAward_admin_images_ext', 'subtext' => $txt['OAward_admin_images_ext_sub']),
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
		checkSession();
		$save_vars = $config_vars;
		saveDBSettings($save_vars);
		redirectexit('action=admin;area=modsettings;sa=faq');
	}

	prepareDBSettingContext($config_vars);
}