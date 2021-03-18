<?php
	/*
	 *
	 * Main Navigation
	 *
	 * */

	$menu_location = 'primary_menu';
	$genre_args = array(
		'taxonomy' => 'wp-manga-genre',
		'hide_empty' => false,
		'exclude' => $exclude_genre
	);
	$genres = get_terms( $genre_args );

	if ( has_nav_menu( $menu_location ) ) {
		echo '<ul class="nav navbar-nav main-navbar">';
		wp_nav_menu( array(
			'theme_location' => $menu_location,
			'container'      => false,
			'items_wrap'     => '%3$s'
		) );
		echo '
		<li><div class="dropdown">
		<button class="dropbtn">Novel</button>
		<ul class="dropdown-content">' ?>
		<?php
		foreach( $genres as $genre ) {
			?>
			<li class="madara-bounce <?php echo $layout == 'layout-2' ? 'col-xs-6 col-sm-4 col-md-3 col-lg-2 col-6' : 'col-xs-6 col-sm-6'; ?>">
				<a href="<?php echo esc_url( get_term_link( $genre ) ); ?>">
					<?php if(is_plugin_active( 'advanced-category-and-custom-taxonomy-image/wp-advanced-taxonomy-image.php' ) === true){ ?>
						
						<?php if(get_taxonomy_image($genre->term_id) !== 'Please Upload Image First!'){ ?>
						
							<img src="<?= get_taxonomy_image($genre->term_id) ?>" class="wp-manga-genre-image" alt="">
						<?php }else{ ?>
							<img src="<?= home_url() .'/wp-content/uploads/2021/02/fist.svg' ?>" class="wp-manga-genre-image" alt="">
						<?php } ?>
					<?php } ?>
					<?php echo esc_html( $genre->name ); ?>
					
					<?php
					if( $show_manga_counts == 'true' ) {
						?>
						<span class="count">
							(<?php echo esc_html( $genre->count ); ?>)
						</span>
						<?php
					}
					?>
				</a>
			</li>
			<?php
		}
		?>
		<?php
		echo

		'</ul>
	  </div></li>
		</ul>';
	} else { ?>
        <ul class="nav navbar-nav main-navbar">
            <li><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'madara' ) ?></a></li>
        </ul>
	<?php }
 