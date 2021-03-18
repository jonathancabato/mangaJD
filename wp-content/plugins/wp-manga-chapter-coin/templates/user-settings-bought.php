<div class="tab-pane active">
	<?php

	use App\Madara;
	
	$manager = WP_MANGA_ADDON_CHAPTER_COIN::get_instance();
				
	$my_bought = $manager->get_user_bought_mangas(get_current_user_id());
	
	$madara_post_count = count($my_bought);
	
	$manga_archives_item_layout = 'simple'; // edit this if you want to change layout

		?>
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
								set_query_var('show_chapters', 1);
							?>

							<?php foreach($my_bought as $manga_id) {
								global $post;
								$post = get_post($manga_id);
								
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