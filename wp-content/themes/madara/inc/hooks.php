<?php

use App\Madara;

add_filter('wp_manga_chapters_sort', 'madara_wp_manga_chapters_sort');

function madara_wp_manga_chapters_sort( $sort_option ){
	$sort_option = App\Madara::getOption('manga_chapters_order', 'name_desc');

	if( in_array( $sort_option, array( 'name_desc', 'name_asc' ) ) ){
		$sort_option = array(
			'sortBy' => 'name',
			'sort'   => $sort_option == 'name_desc' ? 'desc' : 'asc'
		);
	} elseif(in_array( $sort_option, array( 'date_desc', 'date_asc' ) )){
		$sort_option = array(
			'sortBy' => 'date',
			'sort'   => $sort_option == 'date_desc' ? 'desc' : 'asc',
		);
	} else {
		$sort_option = array(
			'sortBy' => 'index',
			'sort'   => $sort_option == 'index_desc' ? 'desc' : 'asc',
		);
	}
	
	$reading_manga_chapters_order = App\Madara::getOption('manga_chapters_select_order', 'default');
	if((is_manga_reading_page() || (wp_doing_ajax() && isset($_GET['postID']) && $_GET['postID'] != '' && isset($_GET['chapter']) && $_GET['chapter'] != '')) && $reading_manga_chapters_order == 'reverse'){
		$sort_option['sort'] = ($sort_option['sort'] == 'desc' ? 'asc' : 'desc');
	}
	
	return $sort_option;
}

// support Imagify
add_filter('imagify_picture_img_attributes', 'madara_imagify_picture_img_attributes', 10, 2);
function madara_imagify_picture_img_attributes($attributes, $image ){
	$lazyload  = Madara::getOption( 'lazyload', 'off' );
	if($lazyload == 'on'){
		$attributes['class'] = 'img-responsive lazyload effect-fade';
	}
	return $attributes;
}

add_filter('imagify_picture_attributes', 'madara_imagify_picture_attributes', 10, 2);
function madara_imagify_picture_attributes($attributes, $image ){
	$lazyload  = Madara::getOption( 'lazyload', 'off' );
	if($lazyload == 'on'){
		$attributes['class'] = '';
	}
	return $attributes;
}