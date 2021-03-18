<?php
function b_activate_plugin(){
    if ( version_compare( get_bloginfo('version'), '4.0', '<' ) ){
        wp_die( __("You must update wordpress to use this plugin.", 'mycred-blocknomics') );
    }else{
    blck_install();

    }

}
global $blck_db_version;
$blck_db_version = '1.0';

function blck_install() {
	global $wpdb;
	global $blck_db_version;

	$table_name = $wpdb->prefix . 'blck_payments';
	$table_name1 = $wpdb->prefix . 'pay_to_unlock';
	$table_name2 = $wpdb->prefix . 'block_api';
	
	$charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
		payments_id bigint(9) NOT NULL AUTO_INCREMENT,
        `user_id` bigint(9),
        donor tinytext NOT NULL,
        amount bigint(9) NOT NULL,
        chapter_id bigint(9) NOT NULL,
        post_id bigint(9) NOT NULL,
        is_anonymous tinytext NOT NULL,
		time datetime NOT NULL,
		PRIMARY KEY  (payments_id)
	) $charset_collate;";
    $sql1 = "CREATE TABLE IF NOT EXISTS $table_name1 (
		pay_id bigint(9) NOT NULL AUTO_INCREMENT,
        chapter_id bigint(9) NOT NULL,
        post_id bigint(9) NOT NULL,
        total_payment bigint(9) NOT NULL,
        payment_to_unlock bigint(9) NOT NULL,
        is_manga_unlocked varchar(100) NOT NULL DEFAULT 'no' ,
		time datetime NOT NULL,
		PRIMARY KEY  (pay_id)
	) $charset_collate;";
    $sql2 = "CREATE TABLE IF NOT EXISTS $table_name2(
        api_id bigint(9) NOT NULL AUTO_INCREMENT,
        blockonomics_btn text NOT NULL,
        post_type_to_show tinytext NOT NULL,
        PRIMARY KEY (api_id)
    ) $charset_collate;";


	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

        dbDelta( $sql );
        dbDelta( $sql1 );
        dbDelta( $sql2 );


	add_option( 'blck_db_version', $blck_db_version );
}

global $wpdb;
$installed_ver = get_option( "blck_db_version" );

if ( $installed_ver != $blck_db_version ) {

 
	$table_name = $wpdb->prefix . 'blck_payments';
	$table_name1 = $wpdb->prefix . 'pay_to_unlock';
	$table_name2 = $wpdb->prefix . 'block_api';
	
	$charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
		payments_id bigint(9) NOT NULL AUTO_INCREMENT,
        `user_id` bigint(9),
        donor tinytext NOT NULL,
        amount bigint(9) NOT NULL,
        chapter_id bigint(9) NOT NULL,
        post_id bigint(9) NOT NULL,
        is_anonymous tinytext NOT NULL,
		time datetime NOT NULL,
		PRIMARY KEY  (payments_id)
	) $charset_collate;";
    $sql1 = "CREATE TABLE IF NOT EXISTS $table_name1 (
		pay_id bigint(9) NOT NULL AUTO_INCREMENT,
        chapter_id bigint(9) NOT NULL,
        post_id bigint(9) NOT NULL,
        total_payment bigint(9) NOT NULL,
        payment_to_unlock bigint(9) NOT NULL,
        is_manga_unlocked varchar(100) NOT NULL DEFAULT 'no' ,
		time datetime NOT NULL,
		PRIMARY KEY  (pay_id)
	) $charset_collate;";
    $sql2 = "CREATE TABLE IF NOT EXISTS $table_name2(
        api_id bigint(9) NOT NULL AUTO_INCREMENT,
        blockonomics_btn text NOT NULL,
        post_type_to_show tinytext NOT NULL,
        PRIMARY KEY (api_id)
    ) $charset_collate;";


	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

        dbDelta( $sql );
        dbDelta( $sql1 );
        dbDelta( $sql2 );



	update_option( "blck_db_version", $blck_db_version );
}