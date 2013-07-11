<?php
/**
 *
 * @package OAwards mod
 * @version 1.0
 * @author Jessica González <suki@missallsunday.com>
 * @copyright Copyright (c) 2013, Jessica González
 * @license http://www.mozilla.org/MPL/MPL-1.1.html
 */

global $txt, $settings;

$txt['OAward_main'] = 'O Award mod';

// UI
$txt['OAward_ui_add_new_award'] = 'Add a new award';
$txt['OAward_ui_cancel'] = 'Cancel';
$txt['OAward_ui_name'] = 'Name: ';
$txt['OAward_ui_image'] = 'Image: ';
$txt['OAward_ui_desc'] = 'Desc: ';

// Admin
$txt['OAward_admin_desc'] = 'This is the main O Award admin panel.';
$txt['OAward_admin_enable'] = 'Enable the OAward mod';
$txt['OAward_admin_enable_sub'] = 'The master setting, check it to enable the mod.';
$txt['OAward_admin_images_ext'] = 'The image extention/file format ';
$txt['OAward_admin_images_ext_sub'] = 'All images needs to be in the same file format. Just type the extension, without the dot. If left empty the mod will use png as the file format.';
$txt['OAward_admin_folder_url'] = 'The url to the image\'s folder';
$txt['OAward_admin_folder_url_sub'] = 'This gotta be a valid folder url, if left empty the mod will use the default value: '. $settings['default_images_url'] . '/medals/'. $award['award_image'];
$txt['OAward_admin_'] = '';
$txt['OAward_admin_'] = '';
$txt['OAward_admin_'] = '';
$txt['OAward_admin_'] = '';
$txt['OAward_admin_'] = '';
$txt['OAward_admin_'] = '';

// Errors
$txt['OAward_error_multiple_empty_values'] = 'The following fields were left empty: %s';
$txt['OAward_error_no_valid_action'] = 'There\'s no such action';