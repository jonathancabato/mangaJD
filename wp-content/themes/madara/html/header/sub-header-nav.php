<?php

	/**
	 *  Sub header Navigation bar
	 */

	use App\Madara;

	global $wp_manga_user_actions;

	$header_bottom_border = Madara::getOption( 'header_bottom_border', 'on' );
	$madara_header_style  = apply_filters( 'madara_header_style', Madara::getOption( 'header_style', 1 ) );
	$sticky_menu          = Madara::getOption( 'nav_sticky', 1 );
	$sticky_navgiation    = Madara::getOption('manga_reading_sticky_navigation', 'on');
	$sticky_reading_header = Madara::getOption( 'manga_reading_sticky_header', '' );
	$is_manga_reading_page = false;
	if( function_exists( 'is_manga_reading_page' ) && is_manga_reading_page() ) {
		$is_manga_reading_page = true;
	}
	
	if ( $is_manga_reading_page && $sticky_reading_header == 'on' ) {
		$sticky_menu = 1;
	}
	
	$wp_manga             = madara_get_global_wp_manga();
	
	$user_enabled = ! is_user_logged_in() && get_option( 'users_can_register' );
	$user_manga_logged = is_user_logged_in() && class_exists( 'WP_MANGA' );
	$has_secondary_menu = has_nav_menu( 'secondary_menu' );
	if ( $has_secondary_menu || ($user_enabled || $user_manga_logged) ) {
		?>
        <div class="<?php echo esc_attr($has_secondary_menu ? '' : 'no-subnav');?> c-sub-header-nav<?php echo esc_attr( $header_bottom_border == 'on' ? ' with-border ' : '' ); ?> <?php echo esc_attr($sticky_menu == 0 ? 'hide-sticky-menu' : ''); ?>">
            <div class="container <?php echo esc_attr( $madara_header_style == '2' ? 'custom-width' : '' ); ?>">
				<?php if( function_exists('is_manga_reading_page') && is_manga_reading_page() && $sticky_navgiation == 'on' ){ ?>
					<div class="entry-header">
						<?php $wp_manga->manga_nav( 'footer' ); ?>
                    </div>
				<?php } ?>
            </div>
        </div>

	<?php }
