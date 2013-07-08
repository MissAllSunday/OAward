<?php

/**
 *
 * @package OAwards mod
 * @version 1.0
 * @author Jessica González <suki@missallsunday.com>
 * @copyright Copyright (c) 2013, Jessica González
 * @license http://www.mozilla.org/MPL/MPL-1.1.html
 */

	if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
		require_once(dirname(__FILE__) . '/SSI.php');

	elseif (!defined('SMF'))
		exit('<b>Error:</b> Cannot install - please verify you put this in the same place as SMF\'s index.php.');

	global $smcFunc, $context;

	db_extend('packages');

	if (empty($context['uninstalling']))
	{
		$tables[] = array(
			'table_name' => '{db_prefix}oaward',
			'columns' => array(
				array(
					'name' => 'award_id',
					'type' => 'int',
					'size' => 5,
					'null' => false,
					'auto' => true
				),
				array(
					'name' => 'award_user_id',
					'type' => 'int',
					'size' => 5,
					'null' => false
				),
				array(
					'name' => 'award_name',
					'type' => 'varchar',
					'size' => 255,
					'default' => '',
				),
				array(
					'name' => 'award_image',
					'type' => 'varchar',
					'size' => 255,
					'default' => '',
				),
				array(
					'name' => 'award_description',
					'type' => 'text',
					'size' => '',
					'default' => '',
				),
			),
			'indexes' => array(
				array(
					'type' => 'primary',
					'columns' => array('award_id')
				),
			),
			'if_exists' => 'ignore',
			'error' => 'fatal',
			'parameters' => array(),
		);

		// Installing
		foreach ($tables as $table)
		$smcFunc['db_create_table']($table['table_name'], $table['columns'], $table['indexes'], $table['parameters'], $table['if_exists'], $table['error']);
	}
