<?php

function wp_manga_chapter_coin_get_settings(){
	$options = get_option( 'wp_manga_settings', array() );
	$options = isset( $options['wp_manga_chapter_selling'] ) ? $options['wp_manga_chapter_selling'] : array();

	return array(
		'default_coin'                 => isset( $options['default_coin'] ) ? $options['default_coin'] : 0,
		'free_word' => isset( $options['free_word'] ) ? ($options['free_word'] == '' ? esc_html__('Free', MANGA_CHAPTER_COIN_TEXT_DOMAIN) : $options['free_word']) : esc_html__('Free', MANGA_CHAPTER_COIN_TEXT_DOMAIN),
		'free_color' => isset( $options['free_color'] ) ? $options['free_color'] : '#999999',
		'free_background' => isset( $options['free_background'] ) ? $options['free_background'] : '#DCDCDC',
		'unlock_color' => isset($options['unlock_color']) ? $options['unlock_color'] : '#999999',
		'unlock_background' => isset($options['unlock_background']) ? $options['unlock_background'] : '#DCDCDC',
		'ranking_background' => isset($options['ranking_background']) ? $options['ranking_background'] : 'rgba(255, 248, 26, 0.6)',
		'ranking_text_color' => isset($options['ranking_text_color']) ? $options['ranking_text_color'] : '#333333',
		'lock_color' => isset($options['lock_color']) ? $options['lock_color'] : '#ffffff',
		'lock_background' => isset($options['lock_background']) ? $options['lock_background'] : '#fe6a10',
		'muupro_allow_creator_edit'  => isset($options['muupro_allow_creator_edit']) ? $options['muupro_allow_creator_edit'] : 'no'
		);
}

/**
 * Check if $current_user is able to edit/submit chapter coin. Work when WP Manga Upload Pro plugin is installed
 **/
function wp_manga_chapter_coin_is_allow_edit( $chapter_id, $current_user = 0, $manga_id = 0){
	if(!function_exists('muupro_get_chapter_author')){
		return false;
	}
	
	if(!$current_user){
		$current_user = get_current_user_id();
	}
	
	$user_meta= get_userdata($current_user);

	$user_roles = $user_meta->roles; //array of roles the user is part of.

	// if admin, he can do it
	$allow_roles = apply_filters('wp_manga_chapter_coin_allow_editor_roles',array('administrator', 'editor'));
	if(count(array_intersect($allow_roles, $user_roles))){
		return true;
	}
	
	$settings = wp_manga_chapter_coin_get_settings();
	if(!$manga_id){
		global $wp_manga_chapter;
		$chapter = $wp_manga_chapter->get_chapter_by_id( null, $chapter_id );
		if($chapter){
			$manga_id = $chapter['post_id'];
		}
	}
	
	if($manga_id){
		$manga = get_post($manga_id);
		// if manga author can edit all chapters
		if($manga->post_author == $current_user && $settings['muupro_allow_creator_edit'] == 'yes'){
			return true;
		}
	}
	
	return false;
}