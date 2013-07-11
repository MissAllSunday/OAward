<?php
/**
 *
 * @package OAwards mod
 * @version 1.0
 * @author Jessica González <suki@missallsunday.com>
 * @copyright Copyright (c) 2013, Jessica González
 * @license http://www.mozilla.org/MPL/MPL-1.1.html
 */


function template_display_awards()
{
	global $txt, $context, $settings, $scripturl;

	// Set a nice empty var, named it return because I like to state the obvious...
	$return = '
	<div class="OAward">
		<ul class="reset">';

	// A bunch of HTML here
	if (!empty($context['OAwards']))
		foreach ($context['OAwards'] as $award)
		{
			$return .= '<li>';
			$return .=  $settings['default_images_url'] . '/medals/'. $award['award_image'] . '<br />';
			$return .=  $award['award_name'] . '<br />';

			// End the li
			$return .= '</li>';
		}

	// Close the list
	$return .= '
		</ul>';

	// Add a nice form
	$return .= '
		<a onmousedown="toggleDiv(\'oa_add_'. $context['unique_id'] .'\', this);">'. $txt['OAward_ui_add_new_award'] .'</a><br />
		<div id="oa_add_'. $context['unique_id'] .'" style="display:none;">
			<form method="post" action="'. $scripturl .'?action=oaward;sa=create">
				award_user_id: <input type="text" name="award_user_id">
				name: <input type="text" name="award_name">
				image: <input type="text" name="award_image">
				description: <input type="text" name="award_description">
				<input type="submit" value="Submit">
			</form>
		</div>';

	// Close the entire div
	$return .= '
	</div>';


	// Return the data... you don't say!
	return $return;
}

function template_respond()
{

}