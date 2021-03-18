<?php

namespace ActiveTools\Admin;

class UberwachenPage {
    
    private ?UberwachenListTable $list_table;
    
    public function __construct() {
        $this->list_table = null;
        $this->init();
    }
    
    private function init() {
        
        $page = add_menu_page(
            'Überwachen',
            'Überwachen',
            'uberwachen',
            'at-uberwachen',
            array($this, 'render_page'),
            'dashicons-visibility',
            2
        );
    
        add_action( 'load-'. $page, [ $this, 'load_page_dependencies' ] );
    }
    
    public function render_page() {
        require_once \ActiveTools\TEMPLATES_PATH . 'admin/uberwachen-page/uberwachen-page.php';
    }
    
    public function load_page_dependencies() {
        /* Load JS */
        wp_enqueue_script('jquery');
        wp_enqueue_script('jquery-ui-core');
        wp_enqueue_script('jquery-ui-dialog');
        wp_enqueue_script( 'at-uberwachen', \ActiveTools\ADMIN_ASSETS_URL . 'js/uberwachen-page.js', array( 'jquery' ), time() );
        wp_enqueue_script( 'select2-js', \ActiveTools\ADMIN_ASSETS_URL . 'js/select2.min.js', array( 'jquery' ) );
    
        wp_enqueue_style('wp-jquery-ui-dialog');
        wp_enqueue_style( 'select2-css', \ActiveTools\ADMIN_ASSETS_URL . 'css/select2.min.css' );
        wp_enqueue_style( 'at-uberwachen', \ActiveTools\ADMIN_ASSETS_URL . 'css/uberwachen-page.css', [], time() );
        
        $this->list_table = new UberwachenListTable([
            'singular'	=>	'user',
            'plural'	=>	'users',
            'ajax'     => false,
        ]);
    
        $this->list_table->prepare_items();
    
        $arguments = array(
            'label'		=>	'Records Per Page',
            'default'	=>	20,
            'option'	=>	'at_uberwachen_per_page',
        );
        add_screen_option( 'per_page', $arguments );
    }
}
