<?php

	if ( ! is_user_logged_in() ) {
		return;
	}

	$user_id       = get_current_user_id();
	$history_manga = madara_get_user_history_manga( $user_id );
	$reading_style = $GLOBALS['wp_manga_functions']->get_reading_style();
?>

<div class="tab-group-item">

	<?php if ( ! empty( $history_manga ) ) { ?>

		<?php
		$thumb_size       = array( 75, 106 );

		$last_index = count( $history_manga ) - 1;

		foreach ( $history_manga as $id => $manga ) {

			if ( ! isset( $index ) ) {
				$index = 0;
			}

			$this_history_manga = $history_manga[ $id ];
			$post_url           = get_the_permalink( $id );

			//get chapter
			if ( class_exists( 'WP_MANGA' ) && ! empty( $this_history_manga['c'] ) && ! is_array( $this_history_manga['c'] ) ) {

				$wp_manga_chapter = madara_get_global_wp_manga_chapter();
				$chapter          = $wp_manga_chapter->get_chapter_by_id( $id, $this_history_manga['c'] );

			}

			if ( $index % 3 == 0 ) {
				?>
				<div class="tab-item"><div class="row">
				<?php
			}
			?>

			<div class="col-md-4">
				<div class="history-content">
					<div class="item-thumb">
						<?php if ( has_post_thumbnail( $id ) ) { ?>
							<a href="<?php echo esc_url( $post_url ); ?>" title="<?php echo esc_attr( get_the_title( $id ) ); ?>">
								<?php echo madara_thumbnail( $thumb_size, $id ); ?>
							</a>
							<?php }else{ ?>
							<a href="<?php echo esc_url( $post_url ); ?>" title="<?php echo esc_attr( get_the_title( $id ) ); ?>">
							<img class="no-image" src="<?php bloginfo('template_directory'); ?>/images/default.png"  /> 
							<?php } ?>
					</div>
					<div class="item-infor">
						<div class="settings-title">
							<h3>
								<a href="<?php echo esc_url( $post_url ); ?>"><?php echo esc_html( get_the_title( $id ) ); ?></a>
							</h3>
						</div>
						<?php if ( ! empty( $chapter ) ) { ?>
							<div class="chapter">
								<?php
									$c_url = $GLOBALS['wp_manga_functions']->build_chapter_url( $id, $chapter['chapter_slug'], $reading_style );

									if( !empty( $this_history_manga['i'] ) && $reading_style == 'list' ){
										$c_url .= '#image-' . $this_history_manga['i'];
									}
								?>
								<span class="chap">
										<a href="<?php echo esc_url( $c_url ); ?>">
											<?php echo esc_html( $chapter['chapter_name'] ); ?>
										</a>
									</span>
								<?php 
								global $wp_manga;
								if ( ! empty( $this_history_manga['p'] ) && $reading_style == 'paged' ) {
									$p_url = add_query_arg( array(
										$wp_manga->manga_paged_var => $this_history_manga['p']
									), $c_url ); ?>

									<span class="page">
										<a href="<?php echo esc_url( $p_url ); ?>">
											<?php echo sprintf(esc_html__('page %d', 'madara'), $this_history_manga['p']);?> 
										</a>
									</span>
								<?php } ?>
							</div>
						<?php } ?>
						<div class="post-on font-meta">
							<?php echo esc_html( $GLOBALS['wp_manga_functions']->get_time_diff( $this_history_manga['t'], true ) ); ?>
						</div>
					</div>
					<div class="action">
						<a href="javascript:void(0)" class="remove-manga-history" data-manga="<?php echo esc_attr( $id ); ?>"><i class="icon ion-ios-close"></i></a>
					</div>
				</div>
			</div>

			<?php
			if ( $index % 3 == 2 || $index == $last_index ) {
				?>
				</div></div>
				<?php
			}

			$index ++;

		}

		wp_reset_postdata();

	?>
	<?php } ?>

	<?php if( empty( $history_manga ) ){ ?>
		<span><?php esc_html_e( 'You haven\'t read any manga yet', 'madara' ); ?></span>
	<?php } ?>

</div>
