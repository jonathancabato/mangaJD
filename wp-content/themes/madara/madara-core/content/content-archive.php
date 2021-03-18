<?php

	use App\Madara;

	$wp_query           = madara_get_global_wp_query();
	$wp_manga           = madara_get_global_wp_manga();
	$wp_manga_setting   = madara_get_global_wp_manga_setting();
	$wp_manga_functions = madara_get_global_wp_manga_functions();


	//get ready
	$thumb_size          = array( 110, 150 );
	$madara_loop_index   = get_query_var( 'madara_loop_index' );
	$madara_total_posts  = get_query_var( 'madara_post_count' );
	$madara_page_sidebar = get_query_var( 'sidebar' );

	$manga_hover_details     = Madara::getOption( 'manga_hover_details', 'off' );
	
	$manga_id = get_the_ID();

	$alternative             = $wp_manga_functions->get_manga_alternative( $manga_id );

	$authors                 = $wp_manga_functions->get_manga_authors( $manga_id );
	$chapter_type = get_post_meta( $manga_id, '_wp_manga_chapter_type', true );

	$manga_archives_item_layout = get_query_var('manga_archives_item_layout');
	
	$item_columns = 3;
	if ( $madara_page_sidebar == 'full' ) {
		if($manga_archives_item_layout == 'default' || $manga_archives_item_layout == 'small_thumbnail'){
			$main_col_class = 'col-12 col-md-4';
		} elseif($manga_archives_item_layout == 'big_thumbnail'){
			// big thumbnail layout
			$thumb_size              = 'madara_manga_big_thumb';
			$main_col_class = 'col-6 col-md-2';
			$item_columns = 6;
		} elseif($manga_archives_item_layout == 'simple') {
			$main_col_class = 'col-12';
			$item_columns = 12;
		}
	} else {
		if($manga_archives_item_layout == 'default' || $manga_archives_item_layout == 'small_thumbnail'){
			$main_col_class = 'col-12 col-md-6';
			$item_columns = 2;
		} elseif($manga_archives_item_layout == 'big_thumbnail') {
			// big thumbnail layout
			$thumb_size              = 'madara_manga_big_thumb';
			$main_col_class = 'col-6 col-md-3';
			$item_columns = 4;
		} elseif($manga_archives_item_layout == 'simple') {
			$main_col_class = 'col-12';
			$item_columns = 12;
		}
	}
	
	$title_badge_pos = Madara::getOption('manga_badge_position', 1); // 1: before title, 2: before thumbnail
	if ( $madara_loop_index % $item_columns == 1 ) {
?>
<div class="page-listing-item">
    <div class="row row-eq-height">
		<?php
			}
		?>

        <div class="<?php echo esc_attr( $main_col_class ); ?> <?php echo 'badge-pos-' . esc_attr($title_badge_pos);?>">
            <div class="page-item-detail <?php echo esc_html($chapter_type);?>">
				<?php 
				if($manga_archives_item_layout == 'simple'){
					get_template_part( 'madara-core/content/content-archive-simple' );
				} else { ?>
                <div id="manga-item-<?php echo esc_attr( $manga_id ); ?>" class="item-thumb <?php echo esc_attr($manga_hover_details == 'off' ? '' : 'hover-details'); ?> c-image-hover" data-post-id="<?php echo esc_attr($manga_id); ?>">
				<?php
						if ( has_post_thumbnail() ) {
							?>
                            <a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>">
								<?php echo madara_thumbnail( $thumb_size ); ?>
								<?php if($title_badge_pos == 2){?>
								<?php madara_manga_title_badges_html( $manga_id, 1 ); ?>
								<?php }?>
                            </a>
							<?php }else{ ?>
								<a class="no-image" href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>"> 
								<img src="<?php bloginfo('template_directory'); ?>/images/default.png"  />
								<?php if($title_badge_pos == 2){?>
								<?php madara_manga_title_badges_html( $manga_id, 1 ); ?>
								<?php }?>
							</a>
							<?php } ?>
					
                </div>
                <div class="item-summary">
					<div class="archive-manga-wrapper">

						<div class="post-title font-title">
							<h3 class="h5">
									<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
								</h3>
							</div>
				</div>
				<div class="genre archive">
					<?php do_action('wp-manga-manga-properties', $post_id); ?>
								<?php do_action('wp-manga_author', $post_id);?> 
				</div>
								<p class="manga-description archive">
									<?php if( '' !== get_post()->post_content ) {
										?> 
										
									<strong>Description</strong>  <div class="summary__content <?php echo( esc_attr($manga_single_summary == 'on' ? 'show-more' : '' )); ?>"><span><?php the_content(); ?></span></div>
									<?php
										// do something
										}
?>
								</p>
					
                </div>
				
				<?php } ?>
            </div>

        </div>
		<?php
			if ( ($madara_loop_index % $item_columns == 0 ) || ( $madara_loop_index == $madara_total_posts ) ) {
		?>
    </div>
</div>
<?php
	}