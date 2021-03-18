<?php

namespace ActiveTools\Admin;

use ActiveTools;
use ActiveTools\CarbonFields\PostMetaSerializedDatastore;
use Carbon_Fields\Container;
use Carbon_Fields\Container\Post_Meta_Container;
use Carbon_Fields\Field;

class PostMetaOptions {
    
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
        
        if ( ! $this->is_active = $pagenow == 'post.php' ) {
            return;
        }
        
        \Carbon_Fields\Carbon_Fields::boot();
    }
    
    public function maybe_enqueue_scripts_styles() {
        
        if ( ! $this->is_active ) {
            return;
        }
        
        wp_enqueue_script( 'at-admin-post-meta', ActiveTools\ADMIN_ASSETS_URL . 'js/admin-post-meta.js', 'jquery', '' );
    }
    public function register_carbon_fields() {
    
        $container = Container::make( 'post_meta', 'Active Tools' )
            ->set_priority( 'low' )
            ->set_context( 'side' );
        
        $container->add_tab( 'Content Protection', [
            Field::make( 'set', 'at_po_t', __( 'Post Obfuscation' ) )
                ->set_datastore( new PostMetaSerializedDatastore() )
                ->set_options( array(
                    'disabled' => 'Disabled',
                    'all' => 'All Methods',
                    'blocking_overlay' => 'Blocking overlay',
                    'no_text_select' => 'No Text Selection',
                    'scrambled_text' => 'Scrambled Text',
                ) )
                ->set_classes( 'at_po_types' )
                ->set_default_value( ['all'] )
                ->set_help_text( 'Leaving all unchecked is the same as disabled' ),
        ]);
        
    }
}
