<?php
add_shortcode('wp_manga_user_balance', 'wp_manga_user_balance_shortcode');

/**
 * Show User Balance
 **/
function wp_manga_user_balance_shortcode($atts, $content = ''){
	$user_id = 0;
	if(isset($atts['user_id'])){
		$user_id = $atts['user_id'];
        
        // make sure only Admin can see other revenue
        if(!current_user_can('manage_options')){
            $user_id = 0;
        }
	} else {
		$user_id = get_current_user_id();
	}
	
	if(!$user_id) return;
	
	$manager = WP_MANGA_ADDON_CHAPTER_COIN::get_instance();
	$html = '<div class="user-balance-panel">' . $content . '<span class="user-balance"><i class="fas fa-coins"></i> ' . number_format($manager->get_user_balance($user_id)) . '</span></div>';
	
	return $html;
}

require_once 'shortcode-my-bought.php';
require_once 'shortcode-top-bought.php';
require_once 'shortcode-author-revenue.php';