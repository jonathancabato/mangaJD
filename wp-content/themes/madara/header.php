<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="profile" href="http://gmpg.org/xfn/11">
    <link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">

	<?php
		/**
		 * The Header for our theme.
		 *
		 * Displays all of the <head> section and everything up till <div id="content">
		 *
		 * @package madara
		 */

		use App\Madara;

		$madara_header_style = apply_filters( 'madara_header_style', Madara::getOption( 'header_style', 1 ) );
		$current_user = wp_get_current_user();
	?>


	<?php wp_head(); ?>
</head>

<body <?php body_class("notranslate"); ?>>

<?php if ( ! is_404() ) { ?>

<?php

	/**
	 * madara_before_body hook
	 *
	 * @hooked madara_before_body - 10
	 *
	 * @author
	 * @since 1.0
	 * @code     Madara
	 */
	do_action( 'madara_before_body' );
	
	$minimal_reading_page = Madara::getOption( 'minimal_reading_page', 'off' );
	
?>

<div class="wrap">
    <div class="body-wrap">
		<?php if(!(function_exists('is_manga_reading_page') && is_manga_reading_page()) || $minimal_reading_page == 'off') {?>
        <header class="site-header">
            <div class="c-header__top">
                <ul class="search-main-menu">
                    <li>
                        <form id="blog-post-search" action="<?php echo esc_url( home_url( '/' ) ); ?>" method="get">
                            <input type="text" placeholder="<?php echo esc_html__( 'Search...', 'madara' ); ?>" name="s" value="">
                            <input type="submit" value="<?php esc_html_e( 'Search', 'madara' ); ?>">
                            <div class="loader-inner line-scale">
                                <div></div>
                                <div></div>
                                <div></div>
                                <div></div>
                                <div></div>
                            </div>
                        </form>
                    </li>
                </ul>
                <div class="main-navigation <?php echo esc_attr( $madara_header_style == 3 ? 'style-2' : 'style-1'); ?> ">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="main-navigation_wrap">
                                    <div class="wrap_branding">
                                        <a class="logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
											<?php $logo = Madara::getOption( 'logo_image', '' ) == '' ? esc_url( get_parent_theme_file_uri() ) . '/images/logo.png' : Madara::getOption( 'logo_image', '' ); ?>
                                            <img class="img-responsive" src="<?= get_template_directory_uri() ?>/images/footer-logo.gif" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>"/>
                                        </a>
										
                                        <div class="mobile-navigation">
                                            <a class="logo mobile-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
                                                <img class="img-responsive" src="<?= get_template_directory_uri() ?>/images/com.wuxiaworld.mobile-logo.png" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>"/>
												<p>
													Wuxiaworld
												</p>
                                            </a>
									
                                            <div class="c-togle__menu">
												
                                                <button type="button" class="menu_icon__open mobile-avatar" style="background-image:url(<?= get_avatar_url($current_user->ID); ?>);">
                                                </button>
                                            </div>
                                        </div>
										
                                        
    <div class="distorted-menu">
		<?php get_template_part( 'html/header/main-nav' ); ?>
    </div>
                                    </div>
									<?php get_template_part( 'html/header/main-header-1' ); ?>
                                </div>
                            </div>
                        </div>
                </div>
            </div>
            
            <div class="modal fade " id="myCredForm" tabindex="-1" aria-labelledby="myCredPopupLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body">
            <?php 
            	$args = array(
                    'post_type' => 'coin-conversion',
                    'status'	=> 'published'
                );
                $field_id = 'wp_coins';
                $fieldset = 'type=fieldset_text';
                $arr = new WP_Query($args);
             
                if($arr->have_posts()){
                    $i = 0;
                    while($arr->have_posts()){
                    $arr->the_post();
                    $rmvalues = rwmb_meta($field_id, $fieldset, $arr->post->ID);
                    $count = count($rmvalues);
                    $str = '';	
                     foreach ( $rmvalues as $value ) {
                            if($i == $count-1){
                                $str .= $value['point'] . '';
                            }else{
                                $str .=$value['point'].',';
                            }
                            $i++;
                        }
                     }
                }
																															 			wp_reset_postdata();
            ?>
                    <?=  do_shortcode("[crypto_creator amount=".$str."]") ?>
                        
                    </div>
                    <!-- <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div> -->
                    </div>
                </div>
            </div>
			<?php get_template_part( 'html/header/mobile-navigation' ); ?>

			<?php get_template_part( 'html/header/sub-header-nav' ); ?>

        </header>
		<?php get_template_part( 'html/main-top' ); ?>

		<?php } ?>
        <div class="site-content">
<?php }
