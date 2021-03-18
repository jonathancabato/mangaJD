<?php

function blck_enqueue_script(){
    wp_enqueue_style('wpcred_addons', plugins_url('../../assets/css/wpcred-addons.css', __FILE__ ) ,'1.0.0' );
    
    wp_enqueue_script('jQuery');
    wp_enqueue_script('wpcred_addons_js' , plugins_url('../../assets/js/wpcred-addons.js', __FILE__ ) ,'1.0.0' );
}
function front_end_enqueue(){
    wp_enqueue_style('custom_plugin_css' , plugins_url('../../assets/css/main.css', __FILE__), '1.0.0');
    wp_enqueue_script( 'custom_script', plugins_url('../../assets/js/custom-plugin.js', __FILE__ ), '1.0.0', true );

    wp_enqueue_script( 'crypto_btn', 'https://www.blockonomics.co/js/pay_button.js', [],false, true );

}

function append_html(){
    
    wp_register_script('manga_ajax_js', plugins_url('../../assets/js/manga-chapter/wp-madara-ajax.js', __FILE__ ));
    
    wp_localize_script( 'manga_ajax_js', 'wp_manga_params', array(
        'ajaxurl' => site_url() . '/wp-admin/admin-ajax.php', // WordPress AJAX
    ) );
 
     wp_enqueue_script( 'manga_ajax_js' );

}
function ajax_save(){
    wp_register_script('manga_as_js', plugins_url('../../assets/js/manga-save/wp-madara-ajax.js', __FILE__ ));
    
    wp_localize_script( 'manga_as_js', 'wp_manga_params', array(
        'ajaxurl' => site_url() . '/wp-admin/admin-ajax.php', // WordPress AJAX
    ) );
 
     wp_enqueue_script( 'manga_as_js' );
}

function ajax_add_modal(){
    wp_register_script('frontend_mycred_modal_ajax', plugins_url('../../assets/js/frontend/wp-manga-ajax.js', __FILE__ ),[], false, true);
    
    wp_localize_script( 'frontend_mycred_modal_ajax', 'wp_manga_modal_params', array(
        'ajaxurl' => admin_url('admin-ajax.php'), // WordPress AJAX
        'nonce' => wp_create_nonce( 'ajax-nonce' ),
        'post_id' => get_queried_object_id(),
        'user_id'   => wp_get_current_user()->ID
    ) );
 
     wp_enqueue_script( 'frontend_mycred_modal_ajax' );
}
function add_points_ajax(){
    wp_register_script('add_points_ajax', plugins_url('../../assets/js/frontend/wp-buy-points.js', __FILE__ ),[], false, true);
    
    wp_localize_script( 'add_points_ajax', 'wp_manga_buy_params', array(
        'ajaxurl' => admin_url('admin-ajax.php'), // WordPress AJAX
        'nonce' => wp_create_nonce( 'ajax-buy-nonce' ),
        'user_id'   => wp_get_current_user()->ID
    ) );
 
     wp_enqueue_script( 'add_points_ajax' );
}

// function ajax_donate(){
//     wp_register_script('manga_as_js', plugins_url('../../assets/js/manga-save/wp-madara-ajax.js', __FILE__ ));
    
//     wp_localize_script( 'manga_as_js', 'wp_manga_params', array(
//         'ajaxurl' => site_url() . '/wp-admin/admin-ajax.php', // WordPress AJAX
//     ) );
 
//      wp_enqueue_script( 'manga_as_js' );
// }
