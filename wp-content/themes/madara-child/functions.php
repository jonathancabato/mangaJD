<?php
define ('MZ_DEV_MODE', true);
function madara_enqueue(){
$uri            =   get_theme_file_uri();
$ver            =   MZ_DEV_MODE ? time() : false;
	
	
	wp_register_script ('madara-custom', $uri . '/js/custom.js', [], $ver, true);
	// wp_register_script ('madara-translate', 'http://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit', [], $ver, true);
	wp_enqueue_script ('madara-custom');
	// wp_enqueue_script ('madara-translate');
}
	add_action ('wp_enqueue_scripts', 'madara_enqueue');
	add_action( 'wp_enqueue_scripts', 'madara_scripts_styles_child_theme' );
	function madara_scripts_styles_child_theme() {
		wp_enqueue_style( 'madara-css-child', get_parent_theme_file_uri() . '/style.css', array(
			'fontawesome',
			'bootstrap',
			'slick',
			'slick-theme'
		) );
	}
	
	/* Disable VC auto-update */
	add_action( 'admin_init', 'madara_vc_disable_update', 9 );
	function madara_vc_disable_update() {
		if ( function_exists( 'vc_license' ) && function_exists( 'vc_updater' ) && ! vc_license()->isActivated() ) {

			remove_filter( 'upgrader_pre_download', array( vc_updater(), 'preUpgradeFilter' ), 10 );
			remove_filter( 'pre_set_site_transient_update_plugins', array(
				vc_updater()->updateManager(),
				'check_update'
			) );

		}
	}
