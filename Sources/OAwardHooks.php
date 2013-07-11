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

function actions(&$actions)
{
	global $sourcedir;

	// A whole new action just for some ajax calls...
	$actions['oaward'] = array('OAward.php', 'OAward::ajax');
}

function admin_areas(&$areas)
{
	global $txt;

	if (!isset($txt['OAward_main']))
		loadLanguage(self::$name);

	$areas['config']['areas']['modsettings']['subsections']['oaward'] = array($txt['OAward_main']);
}

function faq_modify_modifications(&$sub_actions)
{
	global $context;

	$sub_actions['faq'] = 'modify_faq_post_settings';
	$context[$context['admin_menu_name']]['tab_data']['tabs']['faq'] = array();
}

function modify_faq_post_settings(&$return_config = false)
{
	global $context, $scripturl, $txt;

	// A bunch of config settings here...
	$config_vars = array(
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