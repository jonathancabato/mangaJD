<?php
	/**
	 * @package madara
	 */

	use App\Madara;

	$madara_postMeta = new App\Views\ParseMeta();
	$madara_sidebar  = madara_get_theme_sidebar_setting();
	$thumb_size      = array( 360, 206 );

	$archive_content_columns = get_query_var( 'archive_content_columns', Madara::getOption( 'archive_content_columns', 3 ) );
	$archive_post_excerpt    = Madara::getOption( 'archive_post_excerpt', 'on' );

	$columns_class = 'col-md-4';
	if ( $archive_content_columns == 2 ) {
		$columns_class = 'col-md-6';
	}

	$madara_loop_index = get_query_var( 'madara_loop_index' );
	$madara_post_count = get_query_var( 'madara_post_count' );

?>


<?php if ( $madara_loop_index % $archive_content_columns == 1 ) { ?>
    <div class="row c-row">
<?php } ?>
   <div class="search-post">
                       <a href="<?= get_the_permalink( $query->post->ID) ?>" class="search-img-wrapper">
                        <?php if ( has_post_thumbnail() ) {
                        the_post_thumbnail();
                        } else { ?>
                        <img src="<?php bloginfo('template_directory'); ?>/images/default.png" />
						  
                        <?php } ?>
                        </a>
	   					
                        <div class="item-summary">
					<div class="archive-manga-wrapper">

						<div class="post-title font-title">
							<h3 class="h5 search-h5">
									<a href="<?php the_permalink(); ?>"><?php the_title(); ?><span class="search-author"><?php do_action('wp-manga_author', $post_id);?></span> </a>
								</h3>
							</div>
				</div>
				<div class="search-genre genre archive">
					<?php do_action('wp-manga-manga-properties', $post_id); ?>
					     	<?php do_action('wp-manga_rating', $post_id);?>
				</div>
	
                </div>
                       
                   
                        <!-- < -->
                     </div>

<?php if ( $madara_loop_index % $archive_content_columns == 0 || $madara_loop_index == $madara_post_count ) { ?>
    </div>
<?php } ?>