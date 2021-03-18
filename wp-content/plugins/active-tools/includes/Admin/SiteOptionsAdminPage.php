<?php

namespace ActiveTools\Admin;

use ActiveTools;
use ActiveTools\CarbonFields\ThemeOptionSerializedDatastore;
use ActiveTools\GuardDog;
use Carbon_Fields\Field;
use ActiveTools\Utility\Encryptor;
use ActiveTools\Utility\Logger;

class SiteOptionsAdminPage extends AdminPage {
    
    public function __construct( AdminPage $parent ) {
        
        $this->slug = 'at-so';
        $this->short_slug = 'site_options';
        $this->id = 'at_so';
        $this->page_title = 'Active Tools - Site Options';
        $this->menu_title = 'Site Options';
        
        $this->notices = [
            'at_so_success' => [
                'level' => 'success',
                'message' => 'Congratulations! You have succeeded'
            ],
            'at_so_failure' => [
                'level' => 'error',
                'message' => 'Error: Something happened'
            ],
        ];
        
        parent::__construct( $parent );
    }
    
    protected function register_hooks() {
        
        if ( is_admin() ) {
            add_filter( 'carbon_fields_before_field_save', array( $this, 'before_field_save' ), 10, 1 );
        }
        
        parent::register_hooks();
    }
    
    
    public function register_carbon_fields() {
    
        parent::register_carbon_fields();
        
        if ( ! $this->is_active ) {
            return;
        }
    
        $this->container->add_tab( __( 'Header Scripts' ), [
            Field::make( 'header_scripts', 'at_so_hs', __( 'Header Scripts' ) )
                 ->set_help_text( 'Styles or scripts that get inserted into the header of every page'),
        ] );
        $this->container->add_tab( __( 'Footer Scripts' ), [
            Field::make( 'footer_scripts', 'at_so_fs', __( 'Footer Scripts' ) )
                 ->set_help_text( 'Styles or scripts that get inserted into the header of every page'),
        ] );
    
        $this->container->add_tab( __( 'Maintenance Mode' ), array(
                Field::make( 'checkbox', 'at_so_mm', __( 'Enable Maintenance Mode' ) ),
                Field::make( 'rich_text', 'at_so_mm_m', __( 'Message to display during maintenance mode' ) ),
            ) );
    }
    
    public function admin_init() {
        parent::admin_init();
    }
    
    public function enqueue_scripts_styles() {
        parent::enqueue_scripts_styles();
    }
    
    
    /**
     * @param $field Field\Field
     *
     * @return Field\Field
     */
    function before_field_save( $field ) {
        
        return $field;
    }
    
    /**
     * Redirects to the configuration page and displays a notice
     *
     * @param $notice string Notice message slug
     */
    private function redirect_notice( $notice ) {
        wp_redirect( add_query_arg( 'render_notice', $notice, $this->get_admin_page_url() ) );
        exit;
    }
}
