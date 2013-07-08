<?php
/**
 *
 * @package OAwards mod
 * @version 1.0
 * @author Jessica González <suki@missallsunday.com>
 * @copyright Copyright (c) 2013, Jessica González
 * @license http://www.mozilla.org/MPL/MPL-1.1.html
 */


function template_show_display()
{
	global $txt, $context, $settings;

	// Set a nice empty var, named it return because I like to state the obvious...
	$return = '
	<div class="OAward">
		<ul class="reset">';

	// A bunch of HTML here
	if !empty($context['OAwards'])
		foreach ($context['OAwards'] as $award)
		{
			$return .= '<li>';
			$return .=  $award['award_image'] . '<br />';
			$return .=  $award['award_name'] . '<br />';

			// End the li
			$return .= '</li>';
		}

	// Close the list
	$return .= '
		</ul>';

	// Close the entire div
	$return .= '</div>';


	// Return the data... you don't say!
	return $return;
}

function template_respond()
{

}