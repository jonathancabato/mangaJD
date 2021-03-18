<?php

add_shortcode('wp_manga_bought', 'wp_manga_chapter_coin_shortcode_my_bought');

/**
 * Show list of bought mangas
 **/
function wp_manga_chapter_coin_shortcode_my_bought($atts, $content = ''){
	$current_user_id = get_current_user_id();
	
	if(!$current_user_id) return;
	
	$count = isset($atts['count']) ? intval($atts['count']) : -1;
	$layout = isset($atts['layout']) ? $atts['layout'] : 'simple'; // default, big_thumbnail, simple, small_thumbnail, grid
	$order = isset($atts['order']) ? $atts['order'] : 'latest_bought'; // latest_update
	$sidebar = isset($atts['sidebar']) ? intval($atts['sidebar']) : 0; // is this shorcode place in 9 or 12 columns (with or without sidebar)
	$title = isset($atts['title']) ? $atts['title'] : '';
	$chapters = isset($atts['chapters']) ? $atts['chapters'] : 0;
	$badge_pos = isset($atts['badge_pos']) ? $atts['badge_pos'] : 2;
	
	$manager = WP_MANGA_ADDON_CHAPTER_COIN::get_instance();
				
	$my_bought = $manager->get_user_bought_mangas($current_user_id);
	
	if($order == 'latest_update'){
		$shortcode_args = array(
			'post__in'       => $my_bought,
			'post_type'      => 'wp-manga',
			'posts_per_page' => $layout == 'grid' ? 9 : $count
		);

		$query = new WP_Query( $shortcode_args ); // by default, WP_Query is hooked to order by latest_update
		$madara_post_count = $query->found_posts;
		$my_bought = $query->posts;
	} else {
		$madara_post_count = count($my_bought);
		if($count > 0){
			$my_bought = array_slice($my_bought, 0, $count);
		}
	}
	
	$manga_archives_item_layout = $layout; // edit this if you want to change layout
	
	$html = '';
	
	if(count($my_bought) == 0) return;
	ob_start();
	
	if($layout == 'grid'){
	?>
	<div class="wp-manga-chapter-coin-shortcode shortcode-my-bought">
		<?php if($title) {?>
			<h3 class="title"><?php esc_html_e($title);?></h3>
		<?php }?>
		<div class="c-page">
			<div class="c-page__content">
				<div class="grid9">
					<?php 
					$idx = 0;
					$thumb_size          = 'manga-single';
					global $wp_manga_functions;
					foreach($my_bought as $manga_id) {
						global $post;
						if(is_integer($manga_id)){
							$post = get_post($manga_id);
						} else {
							$post = $manga_id;
						}
						
						setup_postdata($post);
						
						$manga_id = get_the_ID();
						
						$class = "grid_col_2";
						if($idx == 0) { $class = "grid_col_4"; }
						
						$class .= ' badge-pos-' . $badge_pos;
					?>
					<div class="item <?php echo esc_attr($class);?> <?php echo 'badge-pos-' . esc_attr($title_badge_pos);?>">
						<div class="item-inner" id="manga-item-<?php echo esc_attr( $manga_id ); ?>"  data-post-id="<?php echo esc_attr($manga_id); ?>">
							<div class="item-thumb c-image-hover">
								<?php
									if ( has_post_thumbnail() ) {
										?>
										<a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>">
											<?php echo madara_thumbnail( $thumb_size ); ?>
											<?php 
											
											if($badge_pos == 2) {
												madara_manga_title_badges_html( $manga_id, 1 );
											}
											
											?>
										</a>
										<?php
									}
								?>
							</div>
							<div class="item-summary">
								<div class="post-title font-title">
									<h3 class="h5 text2row">
										<?php
										if($badge_pos == 1) {
												madara_manga_title_badges_html( $manga_id, 1 );
											}
											?>
										<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
									</h3>
								</div>
								<div class="meta-item genres">
									<?php
										$genres = $wp_manga_functions->get_manga_genres( $manga_id );
										if($genres){
											$genres = explode(',', $genres);
											echo trim($genres[0]);
										}
									?>
								</div>
								<div class="meta-item rating">
									<?php
										$wp_manga_functions->manga_rating_display( $manga_id );
									?>
								</div>
								<?php if($idx == 0){?>
								<div class="meta-item description">
									<?php
										the_excerpt();
									?>
								</div>
								<?php }?>
							</div>
						
						</div>
					</div>
					<?php 
							$idx++;
						}
					
					wp_reset_postdata();
					?>
				</div>
			</div>
		</div>
	</div>
	<?php } else {?>
	<div class="wp-manga-chapter-coin-shortcode shortcode-my-bought">
		<?php if($title) {?>
			<h3 class="title"><?php esc_html_e($title);?></h3>
		<?php }?>
		<div class="c-page-content">
			<div class="c-page">
				<div class="c-blog-listing c-page__content manga_content">
					<div class="c-blog__inner">
						<div class="c-blog__content">
							<div id="loop-content" style="margin-top:0" class="page-content-listing <?php echo esc_attr('item-' . $manga_archives_item_layout);?>">

							<?php
									$index = 1;
									set_query_var( 'madara_post_count', $madara_post_count );
									set_query_var('manga_archives_item_layout', $manga_archives_item_layout);
									set_query_var('sidebar', $sidebar ? '' : 'full');
									set_query_var('show_chapters', $chapters ? 1 : 0);
									
								foreach($my_bought as $manga_id) {
									global $post;
									if(is_integer($manga_id)){
										$post = get_post($manga_id);
									} else {
										$post = $manga_id;
									}
									
									setup_postdata($post);
									
									
									set_query_var( 'madara_loop_index', $index );
									
									if(locate_template('madara-core/user/page/item-mybought')){
										get_template_part( 'madara-core/user/page/item-mybought' );
										$template = 'madara-core/user/page/item-mybought';
									} else {
										$manager->load_template('loop', 'mybought');
										$template = 'wp-manga/loop-mybought';
									}

									$index ++;

									?>

								<?php }
									wp_reset_postdata(); ?>

							</div>
							

							<?php
								// for Madara Theme
								//Get Pagination
								$madara_pagination = new App\Views\ParsePagination();
								$madara_pagination->renderPageNavigation( '#loop-content', $template, $mymangas, 'ajax' );
							?>
							
							<script type="text/javascript">
								// update args
								__madara_query_vars['manga_archives_item_layout'] = '<?php echo esc_js($manga_archives_item_layout);?>';
								__madara_query_vars['madara_post_count'] = <?php echo esc_js($madara_post_count);?>;
							</script>
						</div>
					</div>
				</div>	
			</div>
		</div>
	</div>
	<?php
	
	}
	
	$html = ob_get_contents();
	ob_end_clean();
	
	return $html;
}