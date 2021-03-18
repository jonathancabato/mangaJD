<?php

namespace ActiveTools\Frontend;

use ActiveTools;
use ActiveTools\Database\Models\IPDataModel;
use ActiveTools\Utility\IPTools;

class Setup {
    
    public function __construct() {
        
        // Register shortcodes and related features
        new ShortCodes();
        
        $this->register_hooks();
    }
    
    private function register_hooks(  ) {
    
        add_filter( 'mycred_before_content_purchase', array( $this, 'limit_user_purchase_time' ), 10, 4 );
        add_action('template_redirect', array( $this, 'maybe_run_maintenance_mode') );
        
        add_action('wp_head', array( $this, 'print_header_scripts' ) );
        add_action('wp_footer', array( $this, 'print_footer_scripts' ) );
    }
    
    function limit_user_purchase_time( $result, $post_id, $user_id, $point_type ) {
    
        $purchase_rate = get_user_meta( $user_id, '_at_cp_mc_cpr_l', true );
        
        if ( $purchase_rate === '0' ) {
            return $result;
        }
        
        if ( empty( $purchase_rate ) || $purchase_rate === '-1' ) {
            $purchase_rate = absint( get_option( '_at_cp_mc_cpr_l', 0 ) );
        } else {
            $purchase_rate = intval( $purchase_rate );
        }
        
        $date_last_purchase_attempted = get_user_meta( $user_id, 'at_mycred_date_last_purchase_attempted', true );
    
        $date = new \DateTime();
    
        if ( empty( $date_last_purchase_attempted ) ) {
            update_user_meta( $user_id, 'at_mycred_date_last_purchase_attempted', new \DateTime() );
            return $result;
        }
    
        $date->modify('-' . $purchase_rate . ' minute' );
        
        $date_diff = $date->diff( $date_last_purchase_attempted )->i;
        
        if ( $date_diff == 0 ) {
            $date_diff_string = $date->diff( $date_last_purchase_attempted )->s . ' seconds';
        } else {
            $date_diff_string = $date_diff . ' minute' . ($date_diff == 1 ? '' : 's');
        }
        
        if ( $date < $date_last_purchase_attempted ) {
            return 'Sorry! There is a ' . $purchase_rate . ' minute wait time between purchasing chapters. You have ' . $date_diff_string . ' remaining.' ;
        }
    
        update_user_meta( $user_id, 'at_mycred_date_last_purchase_attempted', new \DateTime() );
    
        return $result;
    }
    
    /**
     * Places website in maintenance mode for non-administrators
     * Based on carbon fields set in WP-Admin
     */
    function maybe_run_maintenance_mode() {
        
        if ( current_user_can( 'manage_options' ) ) {
            return;
        }
        
        if ( empty ( get_option( '_at_so_mm', false ) ) ) {
            return;
        }
        $protocol = $_SERVER["SERVER_PROTOCOL"];
        if ( 'HTTP/1.1' != $protocol && 'HTTP/1.0' != $protocol ) $protocol = 'HTTP/1.0';
        header( "$protocol 503 Service Unavailable", true, 503 );
        header( 'Content-Type: text/html; charset=utf-8' );
        header( 'Retry-After: 600' );
        
        include( \ActiveTools\TEMPLATES_PATH . 'public/maintenance-mode.php' );
        
        die();
        
        
    }
    
    function print_header_scripts() {
        
        if ( empty ( $header_scripts = get_option( '_at_so_hs', false ) ) ) {
            return;
        }
        
        echo $header_scripts;
    }
    
    function print_footer_scripts() {
        
        if ( empty ( $header_scripts = get_option( '_at_so_fs', false ) ) ) {
            return;
        }
        
        echo $header_scripts;
    }
    
}
