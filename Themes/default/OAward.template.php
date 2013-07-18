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
			$return .=  '<img src="'. $settings['default_images_url'] . '/medals/'. $award['award_image'] .'" width="'. $modSettings['OAward_admin_images_display_size'] .'px;" class="oatoolTip_'. $award['award_id'] .'"/>
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
	global $txt, $context, $settings, $modSettings, $scripturl;

	// If you're not an admin and there is no awards, then theres nothing for you to see...
	if (!$context['user']['is_admin'] && empty($context['OAwards']))
		return '';

	$return = '';

	// Show the awards
	if (!empty($context['OAwards']))
	{
		$return = '
		<ul class="reset">';

		foreach ($context['OAwards'] as $award)
		{
			$return .= '
			<li style="display: inline;">';
			$return .=  '
				<img src="'. $settings['default_images_url'] . '/medals/'. $award['award_image'] .'" width="'. $modSettings['OAward_admin_images_profile_size'] .'px;" class="oatoolTip_'. $award['award_id'] .'"/>
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
			$return .= '
			</li>';
		}

		// End the list
		$return .= '
		</ul>';
	}

	// Add a nice form so the admis can add more goodies
	if ($context['user']['is_admin'])
		$return .= '
		<a onmousedown="toggleDiv(\'oa_add\', this);" class="oaward_add">'. $txt['OAward_ui_add_new_award'] .'</a><br />
		<div id="oa_add" style="display:none;">
			<form method="post" action="'. $scripturl .'?action=oaward;sa=create">
				<input type="hidden" name="award_user_id" id="award_user_id" value="'. $context['member']['id'] .'">
				<label>'. $txt['OAward_ui_name'] .'</label>
				<input type="text" name="award_name" id ="award_name">
				<label>'. $txt['OAward_ui_image'] .'</label>
				<input type="text" name="award_image" id ="award_image">
				<label>'. $txt['OAward_ui_desc'] .'</label>
				<input type="text" name="award_description" id="award_description">
				<input type="submit" value="Submit" class="oward_button">
			</form>
			<script type="text/javascript"><!-- // --><![CDATA[
				$(\'.oward_button\').click(function()
				{
					var award_user_id = $(\'#award_user_id\').val();
					var award_name = $(\'#award_name\').val();
					var award_image = $(\'#award_image\').val();
					var award_description = $(\'#award_description\').val();

					$(\'.oward_button\').attr(\'disabled\', \'disabled\');

					$.ajax(
					{
						type: \'POST\',
						url: smf_scripturl + \'?action=oaward;sa=create\',
						data: ({award_user_id : award_user_id, award_name : award_name, award_image : award_image, award_description : award_description}),
						cache: false,
						dataType: \'json\',
						success: function(html)
						{
							$(\'#award_name\').val(\'\');
							$(\'#award_image\').val(\'\');
							$(\'#award_description\').val(\'\');
							jQuery(\'#oa_add\').slideToggle();

							noty({
								layout: \'top\',
								theme: \'defaultTheme\',
								type: html.type,
								text: html.message,
								timeout: 2500,
								callback: {
									afterClose: function() {
										if (html.type == \'success\'){
											location.reload();}

										else{
											$(\'.oaward_add\').html(oa_add_new_award);}
									}
								},
							});

							// Refresh the page...
							$(\'.oward_button\').removeAttr(\'disabled\');
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

function template_settings_awards()
{
	echo 'LOL';
}
