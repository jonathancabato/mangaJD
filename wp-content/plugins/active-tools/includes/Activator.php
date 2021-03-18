<?php

/**
 * Fired during plugin activation
 *
 * @link       https://tcj.rocks
 * @since      1.0.0
 *
 * @package    ActiveTools
 * @subpackage ActiveTools/includes
 */

namespace ActiveTools;

use ActiveTools\Database\DBManager;

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    ActiveTools
 * @subpackage ActiveTools/includes
 * @author     Randall Bezant <rbezant@tcj.rocks>
 */
class Activator
{
	public static function activate()
    {
        $db = new DBManager();
        $db->create_db_tables();
        
        // Initialize options as autoload
        // update_option( '', '', true );
        
	}
}
