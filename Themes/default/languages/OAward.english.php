<?php
/**
 *
 * @package OAwards mod
 * @version 1.0
 * @author Jessica González <suki@missallsunday.com>
 * @copyright Copyright (c) 2013, Jessica González
 * @license http://www.mozilla.org/MPL/ MPL 2.0
 */

global $txt, $settings;

$txt['OAward_main'] = 'O Award mod';
$txt['OAward_name'] = 'Awards received';

// UI
$txt['OAward_ui_add_new_award'] = 'Add a new award';
$txt['OAward_ui_cancel'] = 'Cancel';
$txt['OAward_ui_user'] = 'User(s): ';
$txt['OAward_ui_user_desc'] = ' - You can assign the same award to multiple users by including their names on thie field.';
$txt['OAward_ui_name'] = 'Name: ';
$txt['OAward_ui_image'] = 'Image: ';
$txt['OAward_ui_desc'] = 'Desc: ';
$txt['OAward_ui_confirm'] = 'Are you sure you wan to delete this?';
$txt['OAward_ui_mass_delete_byUsers'] = 'Delete multiple awards by users';
$txt['OAward_ui_mass_delete_desc'] = ' - You can delete all awards assigned to multiple users';
$txt['OAward_ui_submit'] = 'Submit';

// Server response
$txt['OAward_response_create'] = 'You have succesfully created a new award';
$txt['OAward_response_update'] = 'You have succesfully updated the award';
$txt['OAward_response_delete'] = 'You have succesfully deleted the award';
$txt['OAward_response_empty'] = 'You have succesfully completed this action';

// Admin
$txt['OAward_admin_title_general'] = 'OAward General settings';
$txt['OAward_admin_manageAwards_title'] = 'Manage awards';
$txt['OAward_admin_manageAwards_desc'] = 'From here you can add/delete awards given.';
$txt['OAward_admin_manageImages_title'] = 'Manage images';
$txt['OAward_admin_manageImages_desc'] = 'From here you can remove images, if you delete an image, all awards associated with it will also be removed.';
$txt['OAward_admin_manageImages_warning'] = 'If you chose to delete an already associated image, all awards associated with it will be deleted too. Be careful with that.';
$txt['OAward_admin_manageAwards_serverResponse_success'] = 'You have succesfully deleted the image.';
$txt['OAward_admin_manageAwards_serverResponse_error'] = 'There was an error. Please try again later.';
$txt['OAward_admin_serverResponse_addAward_success'] = 'You have succesfully created the awards.';
$txt['OAward_admin_serverResponse_addAward_error'] = 'There was an error while trying to create the awards, please try again later.';
$txt['OAward_admin_serverResponse_deleteAward_success'] = 'You have succesfully deleted the awards.';

$txt['OAward_admin_desc'] = 'This is the main O Award admin panel.';
$txt['OAward_admin_enable'] = 'Enable the OAward mod';
$txt['OAward_admin_enable_sub'] = 'The master setting, check it to enable the mod.';
$txt['OAward_admin_images_profile_size'] = 'Set the image size fr the profile page';
$txt['OAward_admin_images_profile_size_sub'] = 'If left empty the mod will set a size of 40px';
$txt['OAward_admin_images_display_size'] = 'Set the image size for the display page';
$txt['OAward_admin_images_display_size_sub'] = 'If left empty the mod will set a default size of 15px;';
$txt['OAward_admin_directory_url'] = 'The url to the image\'s directory';
$txt['OAward_admin_directory_url_sub'] = 'This gotta be a valid directory url, if left empty the mod will use the default value: '. $settings['default_images_url'] . '/medals/';
$txt['OAward_admin_images_name'] = 'Name';
$txt['OAward_admin_images_ext'] = 'Extension';
$txt['OAward_admin_images_delete'] = 'Delete';
$txt['OAward_admin_images_associated_ids'] = 'Awards associated with this image:';
$txt['OAward_admin_images_image'] = 'Image';
$txt['OAward_admin_ssigned'] = ' assigned to ';
$txt['OAward_admin_images_unassigned_desc'] = 'The follow images does exists in the images dir but haven\'t been assigned to an award yet';
$txt['OAward_admin_images_assigned_desc'] = 'The follow images has been assigned to an award';

// Errors
$txt['OAward_error_multiple_empty_values'] = 'You need to fill all fields in the form, make sure to include an image extension in the image field';
$txt['OAward_error_no_valid_action'] = 'There\'s no such action';
$txt['OAward_error_no_valid_path'] = 'The directory for the images does not exists. The mod uses the follow url to access the images<br />'. $settings['default_images_url'] . '/medals/<br /> Make sur ethis directory does exists inside the default/images/ directory.';
$txt['OAward_error_no_image_ext'] = 'You must especify an image extension';
$txt['OAward_error_no_image_in_server'] = 'The image %s does not exists in the images directory';
