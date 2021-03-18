<?php

namespace ActiveTools\Admin;

use Carbon_Fields\Field;
use ActiveTools\Utility\Encryptor;
use ActiveTools\Utility\Logger;

class MainAdminPage extends AdminPage {
    
    public function __construct() {
        
        $this->slug = 'at-main-admin-page';
        $this->short_slug = 'main';
        $this->id = 'at_main_admin_page';
        $this->page_title = 'Active Tools';
        $this->menu_title = 'Active Tools';
        
        $this->notices = [
            'at_main_success' => [
                'level' => 'success',
                'message' => 'Congratulations! You have succeeded'
            ],
            'at_main_failure' => [
                'level' => 'error',
                'message' => 'Error: Something happened'
            ],
        ];
        
        parent::__construct();
    }
    
    protected function register_hooks() {
        parent::register_hooks();
    }
    
    
    public function register_carbon_fields() {
    
        parent::register_carbon_fields();
        
        if ( ! $this->is_active ) {
            return;
        }
    
        // General settings
    
    }
    
    public function admin_init() {
        parent::admin_init();
    }
    
    public function enqueue_scripts_styles() {
        parent::enqueue_scripts_styles();
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
