<?php 

if(is_plugin_active( 'wp-manga-core/wp-manga.php' ) || is_plugin_active( 'madara-core/wp-manga.php' ) ){

    add_action('wp_manga_before_chapter_content', 'wp_manga_before_chapter_content_community_credit', 10, 2);
    if(!function_exists('wp_manga_before_chapter_content_community_credit')){
        
        function wp_manga_before_chapter_content_community_credit($chapter_slug, $manga_id){
            global $wp_manga_functions;
            global $wpdb;
            $d_t = $wpdb->prefix . 'blck_payments';
            $reading_chapter = function_exists('madara_permalink_reading_chapter') ? madara_permalink_reading_chapter() : false;
            if(!$reading_chapter){
                // support Madara Core before 1.6
                if($chapter_slug = get_query_var('chapter')){
                   global $wp_manga_functions;
                   $reading_chapter = $wp_manga_functions->get_chapter_by_slug( $manga_id, $chapter_slug );
                }
                
                if(!$reading_chapter){
                   global $wp_query;
                   $wp_query->set_404();
                   status_header( 404 );
                   get_template_part( 404 ); exit();
                }
            }    
    
            $chapter_id = $reading_chapter['chapter_id'];
            
            $d_r = $wpdb->get_results("SELECT DISTINCT donor from $d_t where chapter_id = $chapter_id and post_id = $manga_id");
    
            $chapter = $wp_manga_functions->get_chapter_by_slug( $manga_id, $chapter_slug );
            $arr = [$d_r];
            if($d_r){
                if(count($d_r) > 0){
                    $html = '<div class="wp-manga-community-credits">';
                    $html .= '<h5>Credit(s) to:</h5>';
                    $html .= '<span>';
                    for($i = 0; $i < count($d_r) ; $i++){
                       
                        if($name !== $d_r[$i]->donor){
                            if($i !== count($d_r) - 1){
                                $html .= $d_r[$i]->donor . ', ';
                                
                            }else{
                                $html .= $d_r[$i]->donor;
                            }
                        $name = $d_r[$i]->donor;
    
                        }
                    }
                    $html .= '</span>';
                    $html .= '</div>';
    
                    echo $html;
                }
            }
        }
    }
}