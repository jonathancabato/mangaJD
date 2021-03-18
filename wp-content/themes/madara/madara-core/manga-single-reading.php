<?php
	use App\Madara;
	
	$manga_id  = get_the_ID();
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

	$cur_chap = $reading_chapter['chapter_slug'];

	$wp_manga           = madara_get_global_wp_manga();
	$wp_manga_functions = madara_get_global_wp_manga_functions();
	

	$style    = isset( $_GET['style'] ) ? $_GET['style'] : $wp_manga_functions->get_reading_style();

	$wp_manga_settings = get_option( 'wp_manga_settings' );
	$related_manga     = isset( $wp_manga_settings['related_manga'] ) ? $wp_manga_settings['related_manga'] : null;

	$madara_single_sidebar      = madara_get_theme_sidebar_setting();
	$madara_breadcrumb          = Madara::getOption( 'manga_single_breadcrumb', 'on' );
	$manga_reading_discussion   = Madara::getOption( 'manga_reading_discussion', 'on' );
	$manga_reading_social_share = Madara::getOption( 'manga_reading_social_share', 'off' );
	
	$chapter_type = get_post_meta( $manga_id, '_wp_manga_chapter_type', true );
	$is_text_chapter_right_sidebar = ($madara_single_sidebar != 'full' && $chapter_type == 'text' && Madara::getOption( 'manga_reading_text_sidebar', 'on' ) == 'on') ? true : false;
	
	if ( $madara_single_sidebar == 'full' || $is_text_chapter_right_sidebar ) {
		$main_col_class = 'sidebar-hidden col-12 col-sm-12 col-md-12 col-lg-12';
	} else {
		$main_col_class = 'main-col col-12 col-sm-8 col-md-8 col-lg-12';
	}
	
	get_header();

?>
    <div class="c-page-content style-1 reading-content-wrap chapter-type-<?php echo esc_attr($chapter_type == '' ? 'manga' : $chapter_type);?>" data-site-url="<?php echo home_url( '/' ); ?>">
        <div class="content-area">
            <div class="container">
                <div class="row">
                    <div class="main-col <?php echo esc_attr($is_text_chapter_right_sidebar ? "col-md-12" : "col-md-12");?> col-sm-12 sidebar-hidden">
						<div class="madara-reading-single-before-content">
						<?php if ( has_post_thumbnail() ) { ?>
                        <div class="summary_image">
                           <a>
								<?php echo madara_thumbnail( $thumb_size ); ?>
                           </a>
                        </div>
					<?php }else{ ?>
                        <div class="summary_image">
                       <a>
                        <img  src="<?php bloginfo('template_directory'); ?>/images/default.png"  />
                       </a>
                        </div>
				    <?php } ?>
               
						 <h1><?php echo esc_html( get_the_title() ); ?></h1>
						<?php do_action('wp-manga_author', $post_id);?>
						<span>© Tetsuo</span>
							</div>
                        <!-- container & no-sidebar-->
                        <div class="main-col-inner">
                            <div class="c-blog-post">
                                <div class="entry-header header" id="manga-reading-nav-head" data-position="header" data-chapter="<?php echo esc_attr($cur_chap);?>" data-id="<?php echo esc_attr(get_the_ID());?>"> 
                               
<?php $wp_manga->manga_nav( 'header' ); ?> </div>
                                <div class="entry-content">
                                    <div class="entry-content_wrap">
										
                                        <div class="read-container">

											<?php echo apply_filters( 'madara_ads_before_content', madara_ads_position( 'ads_before_content', 'body-top-ads' ) ); ?>
											<?php 
						
						$madara_show_chapter_heading = Madara::getOption( 'chapter_heading', 'on' );
						
						if($madara_show_chapter_heading == 'on'){?>
						<h1 id="chapter-heading"> <?php echo esc_html($reading_chapter['chapter_name']);?></h1>
						<?php }  ?>
												<?php if($chapter_type == 'text'){?>
														<!-- <div class="google-wrapper">
															<div class="google-translate-wrapper">
																<div id="google_translate_element"></div>
																<a href="#" class="close-google"><svg id="Capa_1" enable-background="new 0 0 413.348 413.348" height="10" viewBox="0 0 413.348 413.348" width="10" xmlns="http://www.w3.org/2000/svg"><path d="m413.348 24.354-24.354-24.354-182.32 182.32-182.32-182.32-24.354 24.354 182.32 182.32-182.32 182.32 24.354 24.354 182.32-182.32 182.32 182.32 24.354-24.354-182.32-182.32z"/></svg></a>
															</div>
														<a href="#" class="google-icon">
														<i class="fab fa-google"></i>
														</a>
														</div>

											<script type="text/javascript">
											function googleTranslateElementInit() {

											new google.translate.TranslateElement({pageLanguage: 'en'}, 'google_translate_element');
											}
											</script>

											<script type="text/javascript" src="https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script> -->
											<?php } ?>
                                            <div class="reading-content"translate="yes">
												<input type="hidden" id="wp-manga-current-chap" data-id="<?php echo esc_attr($reading_chapter['chapter_id']);?>" value="<?php echo esc_attr($cur_chap);?>"/>
												<?php 
												
												/**
												 * If alternative_content is empty, show default content
												 **/
												$alternative_content = apply_filters('wp_manga_chapter_content_alternative', '');
												
												if(!$alternative_content){
													do_action('wp_manga_before_chapter_content', $cur_chap, $manga_id);
													
													if ( $wp_manga->is_content_manga( get_the_ID() ) ) {
														$GLOBALS['wp_manga_template']->load_template( 'reading-content/content', 'reading-content', true );
													} else {
														$GLOBALS['wp_manga_template']->load_template( 'reading-content/content', 'reading-' . $style, true );
													}
													
													do_action('wp_manga_after_chapter_content', $cur_chap, $manga_id);
												} else {
													echo madara_filter_content($alternative_content);
												}
												
												?>

                                            </div>
										

											<?php echo apply_filters( 'madara_ads_after_content', madara_ads_position( 'ads_after_content', 'body-bottom-ads' ) ); ?>

                                        </div>


                                    </div>
                                </div>
								<div class="entry-header footer" id="manga-reading-nav-foot" data-position="footer" data-id="<?php echo esc_attr(get_the_ID());?>"><?php $wp_manga->manga_nav( 'footer' ); ?></div>
                            </div>

							<?php if ( class_exists( 'APSS_Class' ) && $manga_reading_social_share == 'on' ) {

								$madara_sharing_text     = apply_filters( 'manga_reading_sharing_text', esc_html__( 'SHARE THIS MANGA', 'madara' ) );
								$madara_sharing_networks = 'facebook, twitter, google-plus, pinterest, linkedin, digg';
								$madara_sharing_networks = apply_filters( 'manga_reading_sharing_networkds', $madara_sharing_networks );
								echo do_shortcode( "[apss_share share_text='$madara_sharing_text' networks='$madara_sharing_networks' counter='1' total_counter='1' http_count='1']" );

							} ?>
									<!-- if ( $manga_reading_discussion == 'on' && !$is_text_chapter_right_sidebar ) -->
							<?php if ( $manga_reading_discussion == 'on') { ?>
                                <div class="row <?php echo esc_attr( $madara_single_sidebar == 'left' ? 'sidebar-left' : ''); ?>">
                                    <div class="<?php echo esc_attr( $main_col_class ); ?>">
									
                                        <!-- comments-area -->
										<?php do_action( 'wp_manga_discussion' ); ?>
										<!-- END comments-area -->
                                    </div>

									<?php
										if ( $madara_single_sidebar != 'full' ) {
											?>
                                            <div class="sidebar-col col-md-4 col-sm-4">
												<?php get_sidebar(); ?>
                                            </div>
										<?php }
									?>

                                </div>
							<?php } ?>

							<?php
								$minimal_reading_page = Madara::getOption( 'minimal_reading_page', 'off' );
					
							?>
                        </div>
                    </div>
					<?php
					if ( $madara_single_sidebar != 'full' && $is_text_chapter_right_sidebar ) {
						?>
						<div class="sidebar-col text-sidebar col-md-4 col-sm-12">
							<?php get_sidebar(); ?>
							
							
						</div>
					<?php }
					?>
                </div>
				<div class="<?php echo esc_attr( $main_col_class ); ?>">

					</div>
            </div>
			
        </div>
		
    </div>
	
<?php do_action( 'after_manga_single' ); ?>
<?php

	get_footer();
