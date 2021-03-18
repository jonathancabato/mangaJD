<?php
/*

Plugin Name: MyCred Blocknomics Add-ons
Plugin URI: https://wordpress.org
Description: A plugin for mycreds extension
Version: 1.0
Author: Julius
Author URI: https://wordpress.org
Text Domain : mycred-blocknomics
License: GPLv2 or later

*/


// Setup

// Includes
include ('includes/activate.php');
include ('includes/init.php');
include ('includes/shortcodes/crypto.php'); 
include ('includes/admin-menu/menus.php'); 
include ('includes/front/enqueue.php'); 
include ('includes/handler/ajax.php');
include ('includes/filters/wp-manga-filters.php');
//Admin Menus

    

// Hooks
register_activation_hook ( __FILE__, 'b_activate_plugin' );

add_action( 'admin_menu', 'admin_page_menu' );
add_action( 'admin_print_styles', 'blck_enqueue_script' );
add_action('wp_enqueue_scripts' , 'front_end_enqueue', 100 ,1);
if(wp_get_block_checked() == 'wp-manga'){
    //Append HTML
	add_action( 'admin_enqueue_scripts', 'append_html' );
	add_action('wp_ajax_appendhtml', 'appendhtml_ajax_handler'); // wp_ajax_{action}
    //Save to DB
    add_action( 'admin_enqueue_scripts', 'ajax_save' );
	add_action('wp_ajax_savetodb', 'savetodb_ajax_handler'); // wp_ajax_{action}

    add_action( 'wp_enqueue_scripts', 'ajax_add_modal' );
	add_action('wp_ajax_modal', 'modal_ajax_handler'); // wp_ajax_{action}
	add_action('wp_ajax_nopriv_modal', 'modal_ajax_handler'); // wp_ajax_{action}
    
    // add_action( 'wp_enqueue_scripts', 'ajax_donate' );
	// add_action('wp_ajax_donation', 'donation_ajax_handler'); // wp_ajax_{action}
    // add_action('wp_ajax_donation_nopriv'
    if(is_plugin_active( 'mycred/mycred.php' )){
        add_action( 'wp_enqueue_scripts', 'add_points_ajax' );
        add_action('wp_ajax_add_points', 'add_points_ajax_handler'); // wp_ajax_{action}
    }
}
// Shortcodes
add_shortcode( 'crypto_creator', 'mycred_render_buy_form_points_btc' ); 

//FN


add_action( 'init', 'wpse9870_init_external' );
function wpse9870_init_external()
{
    global $wp_rewrite;
    $plugin_url = plugins_url( 'includes/ipn.php', __FILE__ );
    $plugin_url = substr( $plugin_url, strlen( home_url() ) + 1 );
    // The pattern is prefixed with '^'
    // The substitution is prefixed with the "home root", at least a '/'
    // This is equivalent to appending it to `non_wp_rules`
    $wp_rewrite->add_external_rule( 'ipn', $plugin_url );
}