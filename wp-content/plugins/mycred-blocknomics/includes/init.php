<?php 
function wp_get_block_api(){
    global $wpdb;
    $items = $wpdb->get_results("SELECT blockonomics_btn from {$wpdb->prefix}block_api WHERE api_id = 1 ");

   
    return $items[0]->blockonomics_btn ? stripslashes($items[0]->blockonomics_btn) : '' ;
}
function wp_get_block_checked(){
    global $wpdb;
    $items = $wpdb->get_results("SELECT post_type_to_show from {$wpdb->prefix}block_api WHERE api_id = 1 ");
	
    return $items[0]->post_type_to_show ? $items[0]->post_type_to_show : '';
	
}

function custom_gateway(){
    include('menu-template/api.php');
    insert_api();
}