<?php
namespace Elementor;

class custom_query extends Widget_Base {

	public function get_name() {
		return 'Manga Query';
	}
	
	public function get_title() {
		return 'WP Manga - Manga Query';
	}
	
	public function get_icon() {
		return 'fas fa-comment-alt';
	}
	
	public function get_categories() {
		return [ 'mad-category' ];
	}
	protected function _register_controls() {
     $args = array(
         'taxonomy' => 'wp-manga-genre',
         'post_type'=> 'wp-manga',
         'orderby' => 'name',
         'order'   => 'ASC'
     );
     $categories = get_categories($args);
     $output = '';
     foreach($categories as $cat){
        $output .= '<option value="'.$cat->term_id.'">'.$cat->name.'</option>';
     }
	}
	
    protected function render() {
        $settings = $this->get_settings_for_display();
        $buildType = get_post_meta($settings['manga_genre'], 'tag_id', true);

        if($settings['manga_genre'] > 0){
            $args = array(
                'tax_query' => array(
                    array(
                        'taxonomy' => 'wp-manga-genre',
                        'terms' => $settings['manga_genre'],
                        'field' => 'term_id',
                    )
                    ),
                'paged'         => 1,
                // 'posts_per_page' => 8,
                'status'    => 'published'
            );
        }else{
            $args = array(
               'post_type'  => 'wp-manga',
               'paged'         => 1,
            //    'posts_per_page'  => 8,
               'status'     => 'published'
            );
        }
        $query = new \WP_Query($args);
?>
   

    <section class="manga-section">

        <div class="manga-container">
        <?php if($query->have_posts()) { ?>
            <div class="posts"  data-genre-id="<?= $settings['manga_genre'] ?>" data-total-num-page="<?= $query->max_num_pages + 1 ?>">
                <?php while($query->have_posts()){?>
                <?php $query->the_post();  ?>
                    <div class="post">
                       <a href="<?= get_the_permalink( $query->post->ID) ?>" class="img-wrapper">
                        <?php if ( has_post_thumbnail() ) {
                        the_post_thumbnail();
                        } else { ?>
                        <img src="<?php bloginfo('template_directory'); ?>/images/default.png" />
						  
                        <?php } ?>
                        </a>
                        <a href="<?= get_the_permalink( $query->post->ID) ?>" class="title-wrapper"><?php the_title(); ?></a>
                        <?php do_action('wp-manga-manga-properties', $post_id); ?>
                       
                        <?php do_action('wp-manga_rating', $post_id);?>
                        <!-- < -->
                     </div>
                <?php } ?>
            </div>
        <?php } ?>
<!--             <div class="button-wrapper"> 
                <a href="#" class="btn-primary  load-more">
                    <span class="overlay-btn"></span>
                    <span class="overlay-btn-text">Load more</span>
                </a>
            </div> -->
        </div>


    </section>
<?php
        
    }

    /**
     * Render the widget output in the editor.
     *
     * Written as a Backbone JavaScript template and used to generate the live preview.
     *
     * @since 1.0.0
     *
     * @access protected
     */
    protected function _content_template() {
   
  
    }
}

