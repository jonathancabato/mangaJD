<?php
/**
 * Active Tools
 *
 * This plugin contains a set of tools to aid in the protection and management of Active Translations.
 *
 * @link              https://tcj.rocks
 * @since             1.0.0
 * @package           ActiveTools
 *
 * @wordpress-plugin
 * Plugin Name: Active Tools
 * Plugin URI:  https://activetranslations.xyz
 * Description: This plugin contains a set of tools to aid in the protection and management of Active Translations.
 * Version:     1.0.0
 * Author:      Randall Bezant
 * Author URI:  mailto:rbezant@tcj.rocks
 * Requires at least: 5.4.0
 * Requires PHP: 7.3
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: active-tools
 */

namespace ActiveTools;

// If this file is called directly, abort.

use ActiveTools\Utility\UUID;

if ( ! defined( 'WPINC' ) ) {
    die;
}

define( 'ActiveTools\VERSION', '1.0.0' );

define('ActiveTools\AT_RAND_NUM1', rand(0, 999999) );
define('ActiveTools\AT_RAND_STR1', substr( md5( AT_RAND_NUM1 ), 0, 10 ) );
define('ActiveTools\AT_RAND_NUM2', rand(0, 999999) );
define('ActiveTools\AT_RAND_STR2', substr( md5( AT_RAND_NUM2 ), 0, 10 ) );
define('ActiveTools\AT_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define('ActiveTools\ADMIN_ASSETS_URL', AT_PLUGIN_URL . 'assets/admin/' );
define('ActiveTools\PUBLIC_ASSETS_URL', AT_PLUGIN_URL . 'assets/public/' );
define('ActiveTools\TEMPLATES_PATH', __DIR__ . '/templates/' );

// Composer's autoload
require_once( 'vendor/autoload.php' );

define('ActiveTools\GD_GLOBAL_BYPASS_KEY', UUID::v5( '3565b95f-d0ef-4b0b-bd33-ffa89ed3aa04', 'GD_GLOBAL_BYPASS_KEY' ) );
define('ActiveTools\GD_REST_API_BYPASS_KEY', UUID::v5( '3565b95f-d0ef-4b0b-bd33-ffa89ed3aa04', 'GD_REST_API_BYPASS_KEY' ) );

require_once( 'functions.php' );

/**
 * Runs during plugin activation.
 */
function activate_active_tools()
{
    Activator::activate();
}
register_activation_hook( __FILE__, 'ActiveTools\activate_active_tools' );

/**
 * Runs during plugin deactivation.
 */
function deactivate_active_tools() {
    Deactivator::deactivate();
}
register_deactivation_hook( __FILE__, 'ActiveTools\deactivate_active_tools' );

/**
 * Runs during plugin uninstallation.
 */
function uninstall_active_tools() {
    Uninstaller::uninstall();
}
register_uninstall_hook( __FILE__, 'ActiveTools\uninstall_active_tools' );

function run_active_tools() {
    (new Plugin())->boot();
}
run_active_tools();

add_action('init', function() {
    if ( isset( $_GET['po_do_test'] ) ) {
        $t = microtime();
        
        $r = 'do_something';
        
        echo var_export( $r, true );
        
        echo '<p>' . ( microtime() - $t ) . '</p>';
        
        die();
    }
});
