<?php
/**
 *
 * @package OAwards mod
 * @version 1.0
 * @author Jessica González <suki@missallsunday.com>
 * @copyright Copyright (c) 2013, Jessica González
 * @license http://www.mozilla.org/MPL/MPL-1.1.html
 */


function template_display_awards($output)
{
	global $txt, $context, $settings, $scripturl, $modSettings;

	// Set a nice empty var, named it return because I like to state the obvious...
	$return = '
	<div class="OAward">
		<ul>';

	// A bunch of HTML here
	if (!empty($context['OAwards']))
		foreach ($context['OAwards'] as $award)
		{
			$return .= '<li>';
			$return .=  '<img src="'. $settings['default_images_url'] . '/medals/'. $award['award_image'] .'.'. $modSettings['OAward_admin_images_ext'] .'" width="15px;" class="oatoolTip_'. $award['award_id'] .'"/>
			<script type="text/javascript"><!-- // --><![CDATA[
				$(\'img.oatoolTip_'. $award['award_id'] .'\').aToolTip({
					clickIt: false,
					tipContent: \'<span style="font-weight:bold;">'. $award['award_name'] .'</span><p />'. $award['award_description'] .'\',
					toolTipClass: \'plainbox\',
					xOffset: 15,
					yOffset: 5,
				});
	// ]]></script>';

			// End the li
			$return .= '</li>';
		}

	// Close the list
	$return .= '
		</ul>';

	// Close the entire div
	$return .= '
	</div>';


	// Return the data... you don't say!
	return $return;
}

function template_display_profile()
{
	global $txt, $context, $memID, $settings, $modSettings;

	$return = '';

	// A bunch of HTML here
	if (!empty($context['OAwards']))
		foreach ($context['OAwards'] as $award)
		{
			$return .= '<li>';
			$return .=  '<img src="'. $settings['default_images_url'] . '/medals/'. $award['award_image'] .'.'. $modSettings['OAward_admin_images_ext'] .'" width="15px;" class="oatoolTip_'. $award['award_id'] .'"/>
			<script type="text/javascript"><!-- // --><![CDATA[
				$(\'img.oatoolTip_'. $award['award_id'] .'\').aToolTip({
					clickIt: false,
					tipContent: \'<span style="font-weight:bold;">'. $award['award_name'] .'</span><p />'. $award['award_description'] .'\',
					toolTipClass: \'plainbox\',
					xOffset: 15,
					yOffset: 5,
				});
	// ]]></script>';

			// End the li
			$return .= '</li>';
		}

	// Add a nice form
	$return .= '
		<a onmousedown="toggleDiv(\'oa_add\', this);">'. $txt['OAward_ui_add_new_award'] .'</a><br />
		<div id="oa_add" style="display:none;">
			<form method="post" action="#">
				<input type="hidden" name="award_user_id" id="award_user_id" value="'. $memID .'">
				'. $txt['OAward_ui_name'] .' <input type="text" name="award_name" id ="award_name">
				'. $txt['OAward_ui_image'] .' <input type="text" name="award_image" id ="award_image">
				'. $txt['OAward_ui_desc'] .' <input type="text" name="award_description" id="award_description">
				<input type="submit" value="Submit" class="oward">
			</form>
			<script type="text/javascript"><!-- // --><![CDATA[
				$(\'.oward\').click(function()
				{
					var award_user_id = $(\'#award_user_id\').val();
					var award_name = $(\'#award_name\').val();
					var award_image = $(\'#award_image\').val();
					var award_description = $(\'#award_description\').val();

					$(this).attr(\'disabled\', \'disabled\');

					jQuery.ajax(
					{
						type: \'POST\',
						url: smf_scripturl + \'?action=oaward;sa=create\',
						data: ({award_user_id : award_user_id, award_name : award_name, award_image : award_image, award_description : award_description}),
						cache: false,
						dataType: \'json\',
						success: function(html)
						{
							noty({
								text: html,
								timeout: 3500, type: \'success\',
							});

							// Enable the button again...
							$(\'.oward\').removeAttr(\'disabled\');
						},
						error: function (html)
						{},
					});

					return false;
				});
			// ]]></script>
		</div>';

	return $return;
}
