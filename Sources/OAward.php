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

class OAward
{
	// Set a nice name to avoid having to write the same thing over anf over again...
	public static $name = 'OAward';

	// Hard-coded CRUD actions FTW!
	protected static $actions = array('create', 'read', 'update', 'delete');
	protected $error = false;
	protected $columns = array('award_id', 'award_user_id', 'award_name', 'award_image', 'award_description');
	protected = $user = 0;

	public function __construct($user)
	{
		global $smcFunc;

		// Load the very useful language strings
		loadLanguage(self::$name);

		// Yeah, we're using superglobals directly, ugly but when in Rome, do as the Romans do...
		$this->_data = $_REQUEST;
		$this->_smcFunc = $smcFunc;

		// The user we're handling the awards for
		$this->user = $user;
	}

	public function ajax()
	{
		// Time to instantiate yourself pal... did it here because we need a single text string and only if someone mess things up, yeah, talk about been efficient!
		$do = new self();

		// Call the inquisition squad!
		$this->sanitize('sa');

		// Nothing to see here, move on...
		if (empty($this->_data['sa']) or !in_array($this->_data['sa'], self::$actions))
			fatal_lang_error(self::$name .'_error_no_valid_action', false);

		// Leave to each case to solve things on their own...
		$do->$sa();

		// We got an issue...
		if (!empty($this->error))
			fatal_lang_error(self::$name .'_error_'. $this->error, false);

		// Everything went better than expected, send the response back to the client
		else
			$this->respond();
	}

	public function create()
	{
		global $txt;

		// You don't say...
		$array = array();

		// Used for collecting possible errors
		$tempError = array();

		// Get the data
		$this->sanitize(self::$columns);

		// Lets check if everything is in order...
		foreach (self::$columns as $value)
			if (empty($this->_data[$value]))
				$tempError[] = $value;

		// Are there any errors? if so, send them all at once!
		if (!empty($tempError) && is_array($tempError))
		{
			$this->error = vsprintf($txt[self::$name .'_error_multiple_empty_values'], $tempError);

			// Stop the process
			die;
		}

		// Insert!
		$this->_smcFunc['db_insert']('replace', '{db_prefix}' . (strtolower(self::$name)) .
			'',
			array(
				'award_id' => 'int',
				'award_user_id' => 'int',
				'award_name' => 'string',
				'award_image' => 'string',
				'award_description' => 'string',
			),
			$this->_data, array('award_id', )
		);
	}

	public function read()
	{

	}

	public function update()
	{

	}

	public function delete()
	{
		$this->sanitize('award_id');

		if (empty($this->_data['award_id']))
		{
			$this->setError('no_valid_id');

			// End it
			die;
		}

		// Does the entry exist?
		$this->read();

		if (empty($this->awards[$this->_data['award_id']]))
		{
			$this->setError('no_valid_id');
			die;
		}


		$this->_smcFunc['db_query']('', '
			DELETE FROM {db_prefix}' . (strtolower(self::$name)) . '
			WHERE award_id = {int:id}', array('id' => $this->_data['award_id'], ));
	}

	protected function respond()
	{
		global $context;

		loadTemplate(self::$name);

		// Pass everything to the template
		$context['template_layers'] = array();
		$context['sub_template'] = 'respond';

		// Done, keep the MVC thingy as much as we can!
		return template_main();
	}

	protected function setError($error, $optional = array())
	{

	}

	protected function sanitize($var)
	{
		// Don't waste my time
		if (empty($this->_data))
			return false;

		// Is this an array?
		if (is_array($var))
			foreach ($var as $item)
			{
				if (empty(trim($this->_data[$item])))
					$this->_data[$item] = false;

				else
				{
					// Delete stuff we don't need...
					foreach ($this->_data as $all)
						if (!in_array($all, $var))
							unset($this->_data[$all]);

					if (is_numeric($item))
						$this->_data[$item] = (int) trim($this->_data[$item]);

					elseif (is_string($item))
						$this->_data[$item] = trim(htmlspecialchars($this->_data[$item], ENT_QUOTES));
				}
			}

		// No? a single item then, check it boy, check it!
		elseif (empty(trim($this->_data[$var])))
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
}