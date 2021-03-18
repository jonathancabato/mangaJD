<?php

add_shortcode('wp_manga_author_revenue', array($this, 'shortcode_author_revenue'));

function shortcode_author_revenue( $atts, $content = '' ) {
        $author_id = 0;
        
        if(!isset($atts['author'])){
            $author_id = get_current_user_id();
        } else {
            $author_id = $atts['author'];
            // make sure only Admin can see other revenue
            if(!current_user_can('manage_options')){
                $author_id = 0;
            }
        }
        
        if($author_id){
            $args = array('post_type' => 'wp-manga',
						'posts_per_page' => -1,
						'author' => $author_id
					);
        }
		
		$mangas = get_posts($args);
		
		$data = array();
		
		$backend = WP_MANGA_ADDON_CHAPTER_COIN_BACKEND::get_instance();
		
        $total = 0;
		foreach($mangas as $manga){
			$item = array(
						'id' => $manga->ID,
						'title' => $manga->post_title,
						'author' => $manga->post_author,
						'coins' => $backend->get_revenue($manga->ID, $date_from, $date_to)
						);
						
			array_push($data, $item);
            $total += $item['coins'];
		}
        
        return '<span class="wp-manga-chapter-coin author-revenue">' . $total . '</span>';
    }   