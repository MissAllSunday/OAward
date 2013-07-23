<?php
/**
 *
 * @package OAwards mod
 * @version 1.0
 * @author Jessica González <suki@missallsunday.com>
 * @copyright Copyright (c) 2013, Jessica González
 * @license http://www.mozilla.org/MPL/ MPL 2.0
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
	public $allowedExtensions = array('gif','jpeg','png','bmp','tiff',);
	protected $customResponse = false;
	protected $_data = array();
	protected $_globalData = array();

	public function __construct($user = false)
	{
		global $smcFunc, $user_info, $themedir, $modSettings;

		// Load the text strings
		loadLanguage(self::$name);

		// Yeah, we're using superglobals directly, ugly but when in Rome, do as the Romans do...
		$this->_globalData = $_REQUEST;
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

	public function createMulti($users, $data)
	{
		// Checks!
		if (empty($users) || empty($data) || !is_array($users) || !is_array($data))
			return false;

		// Used for collecting possible errors
		$tempError = array();
		$insertedIDs = array();

		// Get the data
		$insert = $this->getData($data);

		// Insert
		foreach ($users as $u)
		{
			// Insert the user ID key
			$insert = array('award_user_id' => $u) + $insert;

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

			$insertedIDs[] = $this->_smcFunc['db_insert_id']('{db_prefix}' . (strtolower(self::$name)), 'award_id');
		}

		// Clean the cache
		$this->cleanCache($users);

		if (!empty($insertedIDs))
			return $insertedIDs;

		else
			return false;
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

	public function deleteMulti($IDs)
	{
		if (empty($IDs) || !is_array($IDs))
			return false;

		// All  good!
		$this->_smcFunc['db_query']('', '
			DELETE FROM {db_prefix}' . (strtolower(self::$name)) . '
			WHERE award_id = IN ({array_int:user})',
			array('ids' => ($IDs))
		);

		// Clean the cache
		$this->cleanCache($IDs);
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

	public function checkExt($var)
	{
		if (empty($var))
			return false;

		if (!in_array(strtolower(substr(strrchr($var, '.'), 1)), $this->allowedExtensions))
			return false;

		else
			return true;
	}

	public function sanitize($var)
	{
		if (empty($var))
			return false;

		$return = false;

		// Is this an array?
		if (is_array($var))
			foreach ($var as $item)
			{
				if (!in_array($item, $_REQUEST))
					continue;

				if (empty($_REQUEST[$item]))
					$return[$item] = '';

				if (is_numeric($_REQUEST[$item]))
					$return[$item] = (int) trim($_REQUEST[$item]);

				elseif (is_string($_REQUEST[$item]))
					$return[$item] = trim(htmlspecialchars($_REQUEST[$item], ENT_QUOTES));
			}

		// No? a single item then, check it boy, check it!
		elseif (empty($_REQUEST[$var]))
			return false;

		else
		{
			if (is_numeric($_REQUEST[$var]))
				$return = (int) trim($_REQUEST[$var]);

			elseif (is_string($_REQUEST[$var]))
				$return = trim(htmlspecialchars($_REQUEST[$var], ENT_QUOTES));
		}

		return $return;
	}

	public function getData($var = false)
	{
		if (empty($var))
			return false;

		return $this->sanitize($var);
	}

	public function getGlobalData()
	{
		if (empty($this->_globalData))
			$this->_globalData = $_REQUEST;

		return $this->_globalData;
	}

	public function getColumns()
	{
		return $this->columns;
	}

	protected function cleanCache($arrayIDs = false)
	{
		if ($arrayIDs)
			foreach ($arrayIDs as $user)
				cache_put_data(OAward::$name .'-User-' . $user, null, 120);

		else
			cache_put_data(OAward::$name .'-User-' . $this->user, null, 120);
	}

	public static function deleteImage($path, $image)
	{
		if (empty($path) || empty($image))
			return false;

		// Merge!
		$file = $path .'/'. $image;

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
	<link rel="stylesheet" type="text/css" href="'. $settings['default_theme_url'] .'/css/fineuploader.css" />
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