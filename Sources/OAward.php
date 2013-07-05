<?php
/**
 *
 * @package awards mod
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
	protected $columns = array('award_id', 'user_id', 'name', 'image', 'description');

	public function __construct()
	{
		global $smcFunc;

		loadLanguage(self::$name);

		$this->_data = $_REQUEST;
		$this->_smcFunc = $smcFunc;
	}

	public function ajax()
	{
		// Yeah, we're using superglobals directly, ugly but when in Rome, do as the Romans do...
		$sa = trim(htmlspecialchars($_GET['sa'], ENT_QUOTES));

		// Time to instantiate yourself pal... did it here because we need a single text string and only if someone mess things up, yeah, talk about been efficient!
		$do = new self();

		// Nothing to see here, move on...
		if (empty($sa) or !in_array($sa, self::$actions))
			fatal_lang_error(self::$name .'_error_no_valid_action', false);

		// Leave to each case to solve things on their own...
		$do->$sa();

		// We got an issue...
		if (!empty($this->error))
			fatal_lang_error(self::$name .'_error_'. $this->error, false);

		// Everything went better than expected
		else
			$this->respond();
	}

	public function create()
	{
		// You don't say...
		$array = array();

		// Some logic here...


		// Insert!
		$this->_smcFunc['db_insert']('replace', '{db_prefix}' . (strtolower(self::$name)) .
			'',
			array(
				'award_id' => 'int',
				'user_id' => 'int',
				'name' => 'string',
				'image' => 'string',
				'description' => 'string',
			),
			$array, array('award_id', )
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
}