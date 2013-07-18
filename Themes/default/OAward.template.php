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

	// Add a nice form so the admins can add more goodies
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

function template_manage_images()
{
	global $context, $txt, $modSettings, $scripturl;

	// A nice confirm message
	echo '
	<script type="text/javascript">
	function oa_youSure()
	{
		return confirm("', $txt['OAward_ui_confirm'] ,'");
	}
	</script>';

	// Show the response from the server
	if (!empty($_GET['response']))
		echo '<div ', ($_GET['response'] == 'success' ? 'class="windowbg" id="profile_success"' : 'class="errorbox"') ,'>', $txt['OAward_admin_manageAwards_serverResponse_'. $_GET['response']] ,'</div>';

	// The dir is not writeable, tell the admin about it
	if (!$context['OAward']['is_writeable'])
		echo '<div class="errorbox">some error here...</div>';

	// Show all the images the user has uploaded
	if (!empty($context['OAward']['images']))
	{
		// Tell the suer that all associated awards will be deleted
		echo '<div class="errorbox">', $txt['OAward_admin_manageImages_warning']  ,'</div>';

		echo '
		<div class="cat_bar">
			<h3 class="catbg">', $txt['OAward_admin_images_assigned_desc'] ,'</h3>
		</div>';

		echo '
			<table class="table_grid" cellspacing="0" width="100%">
				<thead>
					<tr class="catbg">
						<th scope="col" class="first_th">', $txt['OAward_admin_images_image'] ,'</th>
						<th scope="col" >', $txt['OAward_admin_images_name'] ,'</th>
						<th scope="col">', $txt['OAward_admin_images_ext'] ,'</th>
						<th scope="col">', $txt['OAward_admin_images_associated_ids'] ,'</th>
						<th scope="col" class="last_th">', $txt['OAward_admin_images_delete'] ,'</th>
					</tr>
				</thead>
			<tbody>';

		foreach($context['OAward']['images'] as $image)
		{
			echo '
				<tr class="windowbg" style="text-align: center">
					<td>
						<img src="', $modSettings['OAward_admin_folder_url'] . $image['image_info']['basename'] ,'" />
					</td>
					<td>
						', $image['image_info']['filename'] ,'
					</td>
					<td>
						', $image['image_info']['extension'] ,'
					</td>
					<td>';

			foreach ($image['associated_ids'] as $id)
				echo $id['name'] . $txt['OAward_admin_ssigned'] . $context['OAward']['usersData'][$id['user']]['link'],'<br />';

				echo '
					</td>
					<td>
						<a href="', $context['OAward']['deleteImage'] ,';image=', urlencode($image['image_info']['basename']) ,'" onclick="return oa_youSure();">', $txt['OAward_admin_images_delete'] ,'</a>
					</td>
				</tr>';
		}

		echo '
			</tbody>
		</table><hr /><br />';
	}

	// Show the unassigned table
	if (!empty($context['OAward']['unassigned_images']))
	{
		echo '
		<div class="cat_bar">
			<h3 class="catbg">', $txt['OAward_admin_images_unassigned_desc'] ,'</h3>
		</div>';

		echo '
			<table class="table_grid" cellspacing="0" width="100%">
				<thead>
					<tr class="catbg">
						<th scope="col" class="first_th">', $txt['OAward_admin_images_image'] ,'</th>
						<th scope="col" >', $txt['OAward_admin_images_name'] ,'</th>
						<th scope="col">', $txt['OAward_admin_images_ext'] ,'</th>
						<th scope="col" class="last_th">', $txt['OAward_admin_images_delete'] ,'</th>
					</tr>
				</thead>
			<tbody>';

		foreach($context['OAward']['unassigned_images'] as $unassigned)
		{
			echo '
				<tr class="windowbg" style="text-align: center">
					<td>
						<img src="', $modSettings['OAward_admin_folder_url'] . $unassigned['basename'] ,'" />
					</td>
					<td>
						', $unassigned['filename'] ,'
					</td>
					<td>
						', $unassigned['extension'] ,'
					</td>
					<td>
						<a href="', $context['OAward']['deleteImage'] ,';image=', urlencode($unassigned['basename']) ,'" onclick="return oa_youSure();">', $txt['OAward_admin_images_delete'] ,'</a>
					</td>
				</tr>';
		}

		echo '
			</tbody>
		</table><hr /><br />';

	}
}
