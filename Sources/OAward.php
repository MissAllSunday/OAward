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

	global $sourcedir;
	require_once($sourcedir .'/OAwardHooks.php');

class OAward
{
	// Set a nice name to avoid having to write the same thing over and over again...
	public static $name = 'OAward';

	// Hard-coded CRUD actions FTW!
	protected static $actions = array('create', 'read', 'update', 'delete');
	protected $error = false;
	protected $columns = array('award_id', 'award_user_id', 'award_name', 'award_image', 'award_description');
	protected $user = 0;
	public $awards = array();
	protected $currentAction = '';
	public $sa = 'default';

	public function __construct($user = false)
	{
		global $smcFunc, $user_info, $themedir;

		// Load the text strings
		loadLanguage(self::$name);

		// Yeah, we're using superglobals directly, ugly but when in Rome, do as the Romans do...
		$this->_data = $_REQUEST;
		$this->_smcFunc = $smcFunc;

		// The user we're handling the awards for
		$this->user = !empty($user) ? $user : $user_info['id'];
	}

	public function showAwards($output)
	{
		global $context;

		// Get the awards
		$this->read();

		// Assign them to a context var
		$context['OAwards'] = $this->awards;
		$context['unique_id'] = $output['id'];

		// Done
		return array(
			'placement' => 1,
			'value' =>  template_display_awards($output),
		);
	}

	public function showProfileAwards()
	{
		global $context, $txt;

		// Load the text strings
		loadLanguage(self::$name);

		// Get the awards
		$this->read();

		// Assign them to a context var
		$context['OAwards'] = $this->awards;

		// Done
		return array(
			'name' => $txt['OAward_name'],
			'placement' => 0,
			'output_html' => template_display_profile(),
		);
	}

	public static function ajax()
	{
		// Time to instantiate yourself pal...
		$do = new self();

		// Call the inquisition squad!
		$do->sanitize('sa');
		$sa = $do->data('sa');

		// Nothing to see here, move on...
		if (!$sa or !in_array($sa, self::$actions))
			$do->setError('no_valid_action');

		// Leave to each case to solve things on their own...
		else
		{
			$do->setSA($sa);
			$do->$sa();
		}

		// Everything went better than expected, send the response back to the client
		$do->respond();
	}

	public function create()
	{
		// Used for collecting possible errors
		$tempError = array();

		// Get the data, we don't need the ID as it doesn't exists yet!
		$temp = $this->columns;
		$cast_away = array_shift($temp);
		$this->sanitize($temp);

		// Lets check if everything is in order...
		foreach ($temp as $value)
			if (empty($this->_data[$value]))
				$tempError[] = $value;

		// Are there any errors? if so, send them all at once!
		if (!empty($tempError) && is_array($tempError))
		{
			$this->setError('multiple_empty_values', implode(',', $tempError));
			return;
		}

		// Everything is nice and dandy, now remove the stuff we don't need, SMF need the exact same amount of fields, blame array_combine()...
		$insert = array_splice($this->data(), 0, - count($temp) + 1);

		// Insert!
		$this->_smcFunc['db_insert']('replace', '{db_prefix}' . (strtolower(self::$name)) .
			'',
			array(
				'award_user_id' => 'int',
				'award_name' => 'string',
				'award_image' => 'string',
				'award_description' => 'string',
			),
			$insert, array('award_id', )
		);

		// Clean the cache
		$this->cleanCache();
	}

	public function read()
	{
		// Use the cache please...
		if (($this->awards = cache_get_data(OAward::$name .'-User-' . $this->user, 120)) == null)
		{
			$result = $this->_smcFunc['db_query']('', '
				SELECT '. (implode(',', $this->columns)) .'
				FROM {db_prefix}' . (strtolower(self::$name)) . '
				WHERE award_user_id = {int:user}
				', array(
					'user' => $this->user,
				)
			);

			// Populate the array
			while ($row = $this->_smcFunc['db_fetch_assoc']($result))
				$this->awards[$row['award_id']] = array(
				'award_id' => $row['award_id'],
				'award_user_id' => $row['award_user_id'],
				'award_name' => $row['award_name'],
				'award_image' => $row['award_image'],
				'award_description' => $row['award_description'],
			);

			$this->_smcFunc['db_free_result']($result);

			// Cache this beauty
			cache_put_data(OAward::$name .'-User-' . $this->user, $this->awards, 120);
		}
	}

	public function readAll()
	{
		$return = array();

		// Use the cache please...
		if (($return = cache_get_data(OAward::$name .'-All', 120)) == null)
		{
			$result = $this->_smcFunc['db_query']('', '
				SELECT '. (implode(',', $this->columns)) .'
				FROM {db_prefix}' . (strtolower(self::$name)) . '
				', array()
			);

			// Populate the array
			while ($row = $this->_smcFunc['db_fetch_assoc']($result))
				$return[$row['award_id']] = array(
				'award_id' => $row['award_id'],
				'award_user_id' => $row['award_user_id'],
				'award_name' => $row['award_name'],
				'award_image' => $row['award_image'],
				'award_description' => $row['award_description'],
			);

			$this->_smcFunc['db_free_result']($result);

			// Cache this beauty
			cache_put_data(OAward::$name .'-All', $return, 120);
		}

		return $return;
	}

	public function update()
	{
		// Used for collecting possible errors
		$tempError = array();

		// Get the data
		$this->sanitize($this->columns);

		// Lets check if everything is in order...
		foreach ($this->columns as $value)
			if (empty($this->_data[$value]))
				$tempError[] = $value;

		// Are there any errors? if so, send them all at once!
		if (!empty($tempError) && is_array($tempError))
			$this->setError('multiple_empty_values', $tempError);

		// Does the entry exist?
		$this->read();

		if (empty($this->awards[$this->_data['award_id']]))
			$this->setError('no_valid_id');

		$this->_smcFunc['db_query']('', '
			UPDATE {db_prefix}' . (strtolower(self::$name)) . '
			SET award_name = {string:name}, award_image = {string:image}, award_descripion = {string:description}
			WHERE id = {int:id}',
			array(
				'id' => $this->_data['award_id'],
				'name' => $this->_data['award_name'],
				'image' => $this->_data['award_image'],
				'description' => $this->_data['award_description'],
			)
		);

		// Clean the cache
		$this->cleanCache();
	}

	public function delete()
	{
		$this->sanitize('award_id');

		if (empty($this->_data['award_id']))
			$this->setError('no_valid_id');

		// Does the entry exist?
		$this->read();

		if (empty($this->awards[$this->_data['award_id']]))
			$this->setError('no_valid_id');

		// All  good!
		$this->_smcFunc['db_query']('', '
			DELETE FROM {db_prefix}' . (strtolower(self::$name)) . '
			WHERE award_id = {int:id}', array('id' => $this->_data['award_id']));

		// Clean the cache
		$this->cleanCache();
	}

	protected function respond()
	{
		global $modSettings, $txt;

		loadLanguage(self::$name);

		// Kill anything else
		ob_end_clean();

		if (!empty($modSettings['enableCompressedOutput']))
			@ob_start('ob_gzhandler');

		else
			ob_start();

		// Send the header
		header('Content-Type: application/json');

		echo json_encode(array(
			'type' => !empty($this->error) ? 'error' : 'success',
			'message' => !empty($this->error) ? $this->error : $txt['OAward_response_'. $this->sa]
		));

		// Done
		obExit(false);
	}

	protected function setError($error, $optionalData = array())
	{
		global $txt;

		// Load the very useful language strings
		loadLanguage(self::$name);

		// Is there any special cases?
		if (!empty($optionalData))
			$this->error = vsprintf($txt[self::$name .'_error_'. $error], $optionalData);

		else
			$this->error = $txt[self::$name .'_error_'. $error];
	}

	public function setSA($sa)
	{
		$this->sa = !empty($sa) ? $sa : 'default';
	}

	public function sanitize($var)
	{
		if (empty($var))
			return false;

		// An extra check!
		$this->_data = $_REQUEST;

		// Is this an array?
		if (is_array($var))
			foreach ($var as $item)
			{
				if (!$this->_data[$item])
					continue;

				// Delete stuff we don't need...
				foreach ($this->_data as $all)
					if (!in_array($all, $var))
						unset($this->_data[$all]);

				if (is_numeric($item))
					$this->_data[$item] = (int) trim($this->_data[$item]);

				elseif (is_string($item))
					$this->_data[$item] = trim(htmlspecialchars($this->_data[$item], ENT_QUOTES));

			}

		// No? a single item then, check it boy, check it!
		elseif (empty($this->_data[$var]))
			return false;

		else
		{
			// Delete stuff we don't need...
			foreach ($this->_data as $all)
				if ($all != $var)
					unset($this->_data[$all]);

			if (is_numeric($var))
				$this->_data[$var] = (int) trim($this->_data[$var]);

			elseif (is_string($var))
				$this->_data[$var] = trim(htmlspecialchars($this->_data[$var], ENT_QUOTES));
		}
	}

	protected function cleanCache()
	{
		cache_put_data(OAward::$name .'-User-' . $this->user, null, 120);
	}

	public function data($var = false)
	{
		if ($var)
			return !empty($this->_data[$var]) ? $this->_data[$var] : false;

		else
			return $this->_data;
	}

	public static function deleteImage($path, $image)
	{
		if (empty($path) || empty($image))
			return false;

		// Merge!
		$file = $path . $image;

		if (!file_exists($file))
			return false;

		// I should create an error log entry if unlink failed...
		return unlink($file);
	}

	public static function setHeaders()
	{
		global $context, $txt, $settings;

		loadTemplate('OAward');
		loadLanguage(self::$name);

		$context['html_headers'] .= '
	<script type="text/javascript">!window.jQuery && document.write(unescape(\'%3Cscript src="http://code.jquery.com/jquery-1.10.2.min.js"%3E%3C/script%3E\'))</script>
	<script type="text/javascript" src="'. $settings['default_theme_url'] .'/scripts/jquery.atooltip.min.js"></script>
	<script type="text/javascript" src="'. $settings['default_theme_url'] .'/scripts/noty/jquery.noty.js"></script>
	<script type="text/javascript" src="'. $settings['default_theme_url'] .'/scripts/noty/themes/default.js"></script>
	<script type="text/javascript" src="'. $settings['default_theme_url'] .'/scripts/noty/layouts/top.js"></script>
	<script type="text/javascript"><!-- // --><![CDATA[
		var oa_add_new_award = '. JavaScriptEscape($txt['OAward_ui_add_new_award']) .';
		var oa_cancel = '. JavaScriptEscape($txt['OAward_ui_cancel']) .';
		var oa_name = '. JavaScriptEscape($txt['OAward_ui_name']) .';
		var oa_image = '. JavaScriptEscape($txt['OAward_ui_image']) .';
		var oa_desc = '. JavaScriptEscape($txt['OAward_ui_desc']) .';
		function toggleDiv(divid, obj){
			jQuery(\'#\' + divid).slideToggle();

			if (obj.innerHTML == oa_add_new_award){
				obj.innerHTML = oa_cancel;}

			else{
				obj.innerHTML = oa_add_new_award;}
		}

	// ]]></script>
	<style>
	#aToolTip {
	position: absolute;
	display: none;
	z-index: 50000;
	}

	#aToolTip .aToolTipContent {
		position:relative;
		margin:0;
		padding:0;
	}

	#oa_add form
	{
	 width: 300px;
	 overflow:hidden;
	}

	#oa_add label
	{
	 clear: both;
	 float: left;
	}

	#oa_add input
	{
	 float: right;
	}

	.oward_button
	{
		clear:both !important;
		padding:3px;
		margin: 3px;
	}
	</style>';

		// Load the template
		loadTemplate('OAward');
	}
}