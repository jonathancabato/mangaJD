<?php

namespace ActiveTools\Admin;

use ActiveTools;
use ActiveTools\CarbonFields\PostMetaSerializedDatastore;
use Carbon_Fields\Container;
use Carbon_Fields\Container\Post_Meta_Container;
use Carbon_Fields\Field;

class UserMetaOptions {
    
    private bool $is_active;
    
    public function __construct() {
        
        $this->is_active = false;
        
        $this->register_hooks();
    }
    
    private function register_hooks() {
    
        if ( is_admin() ) {
            // Boot up Carbon Fields
            add_action( 'after_setup_theme', array( $this, 'boot_carbon_fields' ), 10, 0 );
            // Register the Carbon Fields containers
            add_action( 'carbon_fields_register_fields', array( $this, 'register_carbon_fields' ), 10, 0 );
            // Enqueue Scripts
            add_action( 'admin_enqueue_scripts', array( $this, 'maybe_enqueue_scripts_styles' ) );
        }
    }
    public function boot_carbon_fields() {
        
        global $pagenow;
        
        if ( ! $this->is_active = ( $pagenow == 'profile.php' || $pagenow == 'user-new.php' ) ) {
            return;
        }
        
        \Carbon_Fields\Carbon_Fields::boot();
    }
    
    public function maybe_enqueue_scripts_styles() {
        
        if ( ! $this->is_active ) {
            return;
        }
        
        // wp_enqueue_script( 'at-admin-user-meta', ActiveTools\ADMIN_ASSETS_URL . 'js/admin-user-meta.js', 'jquery', '' );
    }
    public function register_carbon_fields() {
    
        $container = Container::make( 'user_meta', 'Active Tools' );
        
        $container->add_tab( 'myCred', [
            Field::make( 'text', 'at_cp_mc_cpr_l', __( 'Chapter Purchase Rate Limiter' ) )
                 ->set_attribute( 'type', 'number' )
                 ->set_attribute( 'step', '1' )
                 ->set_attribute( 'min', '-1' )
                 ->set_default_value( -1 )
                 ->set_help_text( 'This will bypass the global setting even if globally disabled. Limits purchases to 1 every X minutes. Set to -1 to disable.'),
        ]);
        $container->add_tab( 'Bad Experience', [
            Field::make( 'checkbox', 'at_cp_be_e', __( 'Enable Bad Experience' ) )
                 ->set_option_value( 'yes' )
                 ->set_help_text( 'This user will receive a very poor reading experience for all protected content.'),
        ]);
        
    }
}
