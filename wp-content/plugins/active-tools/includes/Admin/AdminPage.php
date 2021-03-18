<?php

namespace ActiveTools\Admin;

use ActiveTools;
use Carbon_Fields\Container;
use Carbon_Fields\Container\Theme_Options_Container;
use Carbon_Fields\Field;

abstract class AdminPage {
    
    protected string $slug;
    protected string $short_slug;
    protected string $id;
    protected string $page_title;
    protected string $menu_title;
    protected string $user_role = 'administrator';
    protected array $notices;
    
    protected bool $is_active;
    
    private ?array $all_pages_as_options;
    
    protected ?AdminPage $parent_page;
    protected Theme_Options_Container $container;
    
    public function __construct( ?AdminPage $parent = null ) {
        
        $this->parent_page = $parent;
        
        $this->all_pages_as_options = null;
        
        $this->register_hooks();
    }
    
    protected function register_hooks() {
    
        if ( is_admin() ) {
            
            // Boot up Carbon Fields
            add_action( 'after_setup_theme', array( $this, 'boot_carbon_fields' ), 10, 0 );
            // Register the Carbon Fields containers
            add_action( 'carbon_fields_register_fields', array( $this, 'register_carbon_fields' ), 10, 0 );
            // Hooks fired when the specific page is loaded
            add_action( 'admin_init', array( $this, 'admin_init' ), 10, 0 );
        }
    }
    
    public function boot_carbon_fields() {
    
        $this->is_active = isset( $_GET['page'] ) && $_GET['page'] == $this->slug && current_user_can( $this->user_role );
        
        \Carbon_Fields\Carbon_Fields::boot();
    }
    
    public function register_carbon_fields() {
    
        $this->container = Container::make( 'theme_options', $this->id, __( $this->page_title ) )
                              ->set_page_file( $this->slug )
                              ->set_page_menu_title( $this->menu_title )
                              ->where( 'current_user_role', '=', $this->user_role );
        
        // If a parent page is set, assign it
        if ( $this->parent_page ) {
            $this->container->set_page_parent( $this->parent_page->container );
        } else {
            // Otherwise assign a top-level icon
            $this->container->set_icon( $this->admin_menu_icon_base64() );
        }
    }
    
    protected function admin_menu_icon_base64() {
        return 'data:image/svg+xml;base64,' . base64_encode( '
			<svg width="128mm" height="128mm" version="1.1" viewBox="0 0 128 128" xmlns="http://www.w3.org/2000/svg"><path d="m64 1.191a62.81 62.81 0 0 0-62.81 62.81 62.81 62.81 0 0 0 62.81 62.81 62.81 62.81 0 0 0 62.81-62.81 62.81 62.81 0 0 0-62.81-62.81zm-27.13 30.37h13.13c8.164 0 12.25 4.345 12.25 13.04v51.84h-13.83v-21.43h-9.974v21.43h-13.83v-51.84c0-8.69 4.081-13.04 12.24-13.04zm31.93 0.9873h34.56v11.75h-10.37v52.14h-13.83v-52.14h-10.37zm-28.48 8.394c-0.5267 0-0.9877 0.1975-1.383 0.5925-0.3292 0.395-0.4934 0.856-0.4934 1.383v21.43h9.974v-21.43c0-0.5267-0.1975-0.9877-0.5925-1.383-0.3292-0.395-0.7569-0.5925-1.284-0.5925z" fill="#eee" /></svg>
		' );
    }
    
    public function admin_init() {
        
        if ( $this->is_active ) {
            add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts_styles' ) );
            $this->maybe_render_notice();
        }
    }
    
    public function get_admin_page_url() {
        return add_query_arg( 'page', $this->slug, admin_url( 'admin.php' ) );
    }
    
    public function enqueue_scripts_styles() {
        wp_enqueue_script( 'at-admin-general', ActiveTools\ADMIN_ASSETS_URL . 'js/admin-general.js', 'jquery', '' );
        wp_enqueue_style( 'at-admin-general', ActiveTools\ADMIN_ASSETS_URL . 'css/admin-general.css', 'jquery', '' );
    }
    
    private function maybe_render_notice() {
        
        if ( isset( $_GET['render_notice'] ) && isset( $this->notices[$_GET['render_notice']] ) ) {
            $notice = $this->notices[$_GET['render_notice']];
        
            add_action('admin_notices', function() use ( $notice ) {
                ?>
                <div class="notice notice-<?php esc_attr_e( $notice['level'] ); ?>">
                    <p><?php esc_html_e( $notice['message'] ); ?></p>
                </div>
                <?php
            });
        }
    }
    
    function get_all_pages_as_options() {
        
        if ( $this->all_pages_as_options != null ) {
            return $this->all_pages_as_options;
        }
        
        $pages = get_posts([
            'post_type' => 'page',
            'numberposts' => -1,
            'post_status' => 'publish',
            'orderby' => 'post_title',
            'order' => 'ASC',
        ]);
        
        $options = ['' => '-- None --'];
        
        /** @var WP_Post $page */
        foreach( $pages as $page ) {
            if ( $page->post_title ) {
                $options[ $page->ID ] = $page->post_title;
            }
        }
        
        $this->all_pages_as_options = $options;
        
        return $options;
    }
    
}
