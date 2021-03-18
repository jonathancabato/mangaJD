<?php

/**
 * Fired during plugin deletion
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
 * Fired during plugin deletion.
 *
 * This class defines all code necessary to run during the plugin's deletion.
 *
 * @since      1.0.0
 * @package    ActiveTools
 * @subpackage ActiveTools/includes
 * @author     Randall Bezant <rbezant@tcj.rocks>
 */
class Uninstaller {
    
    public static function uninstall() {
        
        $db_manager = new DBManager();
    
        $db_manager->drop_db_tables();
    }
}
