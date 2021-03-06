<?php
/**
 *
 * @package OAwards mod
 * @version 1.0
 * @author Jessica Gonz�lez <suki@missallsunday.com>
 * @copyright Copyright (c) 2013, Jessica Gonz�lez
 * @license http://www.mozilla.org/MPL/ MPL 2.0
 */

if (!defined('SMF'))
	die('No direct access...');

function OAward_actions(&$actions)
{
	global $sourcedir;

	// A whole new action just for some ajax calls...
	$actions['oaward'] = array('OAward.php', 'OAward::ajax');
}

function OAward_modifications(&$sub_actions)
{
	global $context;

	$sub_actions['oaward'] = 'OAward_settings';
	$context[$context['admin_menu_name']]['tab_data']['tabs']['oaward'] = array();
}

function OAward_admin_areas(&$areas)
{
	global $txt;

	if (!isset($txt['OAward_main']))
		loadLanguage(OAward::$name);

	$areas['config']['areas']['oaward'] = array(
		'label' => $txt['OAward_main'],
		'file' => 'OAwardHooks.php',
		'function' => 'OAward_index',
		'icon' => 'administration.gif',
		'subsections' => array(
			'general' => array($txt['OAward_admin_title_general']),
			'manageAwards' => array($txt['OAward_admin_manageAwards_title']),
			'manageImages' => array($txt['OAward_admin_manageImages_title']),
		),
	);
}

function OAward_index()
{
	global $txt, $scripturl, $context, $sourcedir;

	require_once($sourcedir . '/ManageSettings.php');
	loadLanguage(OAward::$name);
	$context['page_title'] = $txt['OAward_main'];

	$subActions = array(
		'general' => 'OAward_settings',
		'manageAwards' => 'OAward_manage_awards',
		'manageImages' => 'OAward_manage_images',
	);

	// Time to overheat the server...
	$context['OAward']['object'] = new OAward();
	$context['imagePath'] = $context['OAward']['object']->imagesPath;
	$context['imageUrl'] = $context['OAward']['object']->imagesUrl;

	loadGeneralSettingParameters($subActions, 'general');

	if (is_callable($subActions[$_REQUEST['sa']]))
		call_user_func($subActions[$_REQUEST['sa']]);

	else
		redirectexit('action=admin;area=oaward');
}

function OAward_settings(&$return_config = false)
{
	global $scripturl, $context, $sourcedir, $settings, $txt, $modSettings;

	loadtemplate('Admin');
	loadLanguage(OAward::$name);

	// Extra check
	if (empty($context['OAward']['object']) || !is_object($context['OAward']['object']))
	{
		$context['OAward']['object'] = new OAward();
		$context['imagePath'] = $context['OAward']['object']->imagesPath;
		$context['imageUrl'] = $context['OAward']['object']->imagesUrl;
	}

	// Load stuff
	$context['sub_template'] = 'show_settings';
	$context['page_title'] = $txt['OAward_admin_title_general'];
	$context[$context['admin_menu_name']]['tab_data'] = array(
		'title' => $txt['OAward_admin_title_general'],
		'description' => $txt['OAward_admin_desc'],
	);

	require_once($sourcedir . '/ManageServer.php');

	// Set a nice message in case there is no images directory...
	if (!$context['OAward']['object']->checkDir())
		$context['settings_insert_above'] = '<div class="errorbox">' . $txt['OAward_error_no_valid_path'] . '</div>';

	// A bunch of config settings here...
	$config_vars = array(
		array('desc', 'OAward_admin_desc'),
		array('check', 'OAward_admin_enable', 'subtext' => $txt['OAward_admin_enable_sub']),
		array('int', 'OAward_admin_images_display_size', 'size'=> 3, 'subtext' => $txt['OAward_admin_images_display_size_sub']),
		array('int', 'OAward_admin_images_profile_size', 'size'=> 3, 'subtext' => $txt['OAward_admin_images_profile_size_sub']),
	);

	if ($return_config)
		return $config_vars;

	$context['post_url'] = $scripturl . '?action=admin;area=oaward;save;sa=general';
	$context['settings_title'] = $txt['OAward_main'];

	if (empty($config_vars))
	{
		$context['settings_save_dont_show'] = true;
		$context['settings_message'] = '<div align="center">' . $txt['modification_no_misc_settings'] . '</div>';

		return prepareDBSettingContext($config_vars);
	}

	if (isset($_GET['save']))
	{
		// Gotta set a default value if the setting is empty
		if (empty($_POST['OAward_admin_images_display_size']))
			$_POST['OAward_admin_images_display_size'] = 15;

		if (empty($_POST['OAward_admin_images_profile_size']))
			$_POST['OAward_admin_images_profile_size'] = 40;

		checkSession();
		$save_vars = $config_vars;
		saveDBSettings($save_vars);
		redirectexit('action=admin;area=oaward;sa=general');
	}

	prepareDBSettingContext($config_vars);
}

function OAward_manage_images()
{
	global $context, $txt, $scripturl, $settings, $memberContext;

	OAward::setHeaders();
	loadTemplate(OAward::$name);
	$context['sub_template'] = 'manage_images';
	$context['OAward']['deleteImage'] = $scripturl . '?action=admin;area=oaward;sa=manageImages;deleteImage';
	$context['page_title'] = $txt['OAward_admin_manageImages_title'];
	$context[$context['admin_menu_name']]['tab_data'] = array(
		'title' => $context['page_title'],
		'description' => $txt['OAward_admin_manageImages_desc'],
	);

	// Extra check
	if (empty($context['OAward']['object']) || !is_object($context['OAward']['object']))
	{
		$context['OAward']['object'] = new OAward();
		$context['imagePath'] = $context['OAward']['object']->imagesPath;
		$context['imageUrl'] = $context['OAward']['object']->imagesUrl;
	}

	// Get all the awards!
	$allAwards = $context['OAward']['object']->readAll();
	$tempUsersIDs = array();
	$associated_ids = array();

	// Scan the dir
	if (is_dir($context['imagePath']) && $context['OAward']['object']->isDirWritable())
		if ($openDir = opendir($context['imagePath']))
		{
			while (($file = readdir($openDir)) !== false)
			{
				// Associate the file name with a record in the DB
				foreach ($allAwards as $a)
					if ($a['award_image'] == $file)
					{
						$context['OAward']['images'][$file] = array(
							'image_info' => pathinfo($context['imagePath'] .'/'. $file),
						);

						$associated_ids[$file][$a['award_id']] = array(
							'name' => $a['award_name'],
							'id' => $a['award_id'],
							'desc' => $a['award_description'],
							'user' => $a['award_user_id'],
						);

						// While we're at it, collect the users IDs...
						$tempUsersIDs[] = $a['award_user_id'];
					}

				// Fill out the unassigned ones...
					else
						$context['OAward']['unassigned_images'][$file] = pathinfo($context['imagePath'] .'/'. $file);

				if (!empty($associated_ids[$file]))
					$context['OAward']['images'][$file]['associated_ids'] = $associated_ids[$file];
			}

			closedir($openDir);

			// Get rid of the dots...
			unset($context['OAward']['unassigned_images']['.']);
			unset($context['OAward']['unassigned_images']['..']);

			// Load the users data
			$loaded_ids = loadMemberData(array_unique($tempUsersIDs), false, 'profile');

			// Set the context var
			foreach ($tempUsersIDs as $u)
			{
				// Avoid SMF showing an awful error message
				if (in_array($u, $loaded_ids))
				{
					loadMemberContext($u);

					// Normal context var
					$context['OAward']['usersData'][$u] = array(
						'name' => $memberContext[$u]['name'],
						'id' => $memberContext[$u]['id'],
						'link' => '<a href="'. $scripturl .'?action=profile;u='. $memberContext[$u]['id'] .'">'. $memberContext[$u]['name'] .'</a>',
					);
				}

				// Award receiver is a guest...
				else
					$context['OAward']['usersData'][$u] = array(
						'name' => $txt['guest_title'],
						'id' => 0,
						'link' => $txt['guest_title'],
					);
			}
		}

		// Couldn't connect to the dir, theres no images to show
		else
			$context['OAward']['images'] = false;

	// Handle deletion, each subaction sholud have its own separate function but I'm lazy...
	if (isset($_GET['deleteImage']))
	{
		$context['OAward']['object']->sanitize('image');
		$image = $context['OAward']['object']->getData('image');

		// Get the file and the ext
		if (empty($image))
			redirectexit('action=admin;area=oaward;sa=manageImages');

		// All nice and dandy... call the method
		if (OAward::deleteImage($context['imagePath'], urldecode($image)))
		{
			// Get all associated awards, if there are some of course
			if (!empty($context['OAward']['images'][$image]['associated_ids']))
			$toRemove = array_keys($context['OAward']['images'][$image]['associated_ids']);

			redirectexit('action=admin;area=oaward;sa=manageImages;response=success');
		}

		else
			redirectexit('action=admin;area=oaward;sa=manageImages;response=error');
	}
}

function OAward_manage_awards()
{
	global $context, $txt, $scripturl, $settings, $memberContext;

	OAward::setHeaders();
	loadTemplate(OAward::$name);
	$context['sub_template'] = 'manage_awards';
	$context['page_title'] = $txt['OAward_admin_manageAwards_title'];
	$context[$context['admin_menu_name']]['tab_data'] = array(
		'title' => $context['page_title'],
		'description' => $txt['OAward_admin_manageAwards_desc'],
	);

	// Extra check
	if (empty($context['OAward']['object']) || !is_object($context['OAward']['object']))
	{
		$context['OAward']['object'] = new OAward();
		$context['imagePath'] = $context['OAward']['object']->imagesPath;
		$context['imageUrl'] = $context['OAward']['object']->imagesUrl;
	}

	// Create awards
	if (isset($_GET['newAward']) && !empty($_POST['award_user_id']))
	{
		// Get the data
		$data = array('award_name','award_image','award_description',);
		$users = $_POST['award_user_id'];
		$emptyField = array();

		$check = $context['OAward']['object']->getData($data);

		// User must fill all fields
		foreach ($check as $c)
			if (empty($c))
				fatal_lang_error('OAward_error_multiple_empty_values', 0);

		// Check if the image field has a valid extention
		if (!$context['OAward']['object']->checkExt($check['award_image']))
			fatal_lang_error('OAward_error_no_image_ext', 0);

		// Does the file exists?
		if (!$context['OAward']['object']->checkImage($check['award_image']))
			fatal_lang_error('OAward_error_no_image_in_server', 0, $check['award_image']);

		// Finally, create the award
		$context['OAward']['object']->createMulti($users, $data);
		redirectexit('action=admin;area=oaward;sa=manageAwards;innerType=success;innerMessage=addAward');
	}

	// Delete awards
	if (isset($_GET['deleteAward']) && !empty($_POST['delete_id']))
	{
		$context['OAward']['object']->deleteBy($_POST['delete_id'], 'award_user_id');
		redirectexit('action=admin;area=oaward;sa=manageAwards;innerType=success;innerMessage=deleteAward');
	}
}
