<?php

	/** Single template of Manga **/

	get_header();

	use App\Madara;
    global $wpdb;
	$wp_manga           = madara_get_global_wp_manga();
	$wp_manga_functions = madara_get_global_wp_manga_functions();
	$thumb_size         = array( 193, 278 );
	$post_id            = get_the_ID();
    $page               = isset( $_GET[$wp_manga->manga_paged_var] ) ? $_GET[$wp_manga->manga_paged_var] : '1';
    $chapter_id = $GLOBALS['wp_manga_chapter']->get_chapter_id_by_slug( $post_id, $chapter );
    $bookmark_manga = get_user_meta( get_current_user_id(), '_wp_manga_bookmark', true );
	$madara_single_sidebar      = madara_get_theme_sidebar_setting();
	$madara_breadcrumb          = Madara::getOption( 'manga_single_breadcrumb', 'on' );
	$manga_profile_background   = madara_output_background_options( 'manga_profile_background' );
	$manga_single_summary       = Madara::getOption( 'manga_single_summary', 'on' );
    $manga_color_style          = Madara::getOption( 'manga_color_style', 'madara_default' );
	$wp_manga_settings = get_option( 'wp_manga_settings' );
	$related_manga     = isset( $wp_manga_settings['related_manga'] ) ? $wp_manga_settings['related_manga'] : null;
    global $wp_manga_chapter;
    $chapters = $wp_manga_chapter->get_manga_chapters( $post->ID );
    $total_bookmarked = get_post_meta($post_id, '_wp_user_bookmarked', true);
    $chapter_type = get_post_meta( $post_id, '_wp_manga_chapter_type', true );
    global $wpdb;
 
    $table_name = $wpdb->prefix . 'manga_chapters';
    $result = $wpdb->get_results("SELECT COUNT(*) as 'count' from $table_name where post_id = $post->ID group by date_format(date, '%y'), date_format(date, '%b') order by 	date_format(date, '%y') desc, date");
    $manga_coin = $wpdb->prefix . 'manga_chapter_coin';
    
    $free_r = $wpdb->get_results("SELECT MONTH(date) AS month, WEEK(date) AS week, DATE(date) AS date, COUNT(*) AS count FROM  $table_name INNER JOIN $manga_coin on $manga_coin.chapter_id = $table_name.chapter_id WHERE post_id = $post->ID and coin = 0 GROUP BY week ORDER BY date");
    if($free_r){
        for($index = 0 ; $index < count($free_r) ; $index++){
            $count = $count + $free_r[$index]->count;
        }
        if(is_plugin_active( 'mycred-blocknomics/mycred-blocknomics.php' )){
            $py_to_unlock = $wpdb->prefix . 'pay_to_unlock';
                $py_r = $wpdb->get_results("SELECT COUNT(*) as 'chapter_count' from $py_to_unlock where post_id = $post->ID and is_manga_unlocked = 'yes'");

                if($py_r){
                    $py_count = $py_r[0]->chapter_count;
                }else{  
                    $py_count = 0;
                }
            
        }else{
            $py_count = 0;
        }
        $free_releases = ($count - $py_count) / count($free_r);
    }else{
        $free_releases = 0;
    }
    
    
    if(count($result) > 0){
        for($i = 0; $i < count($result); $i++){
            $total = $total + $result[$i]->count;
            $totalCount = $total !== 0 && count($result) !== 0 ? $total / count($result): 0;

        }   
    }else{
        $totalCount = 0;
    }
?>


<?php do_action( 'before_manga_single' ); ?>
<div <?php post_class();?>>
<div class="profile-manga" >
	
	
    
           
                <div class="tab-summary <?php echo has_post_thumbnail() ? '' : esc_attr( 'no-thumb' ); ?>">

                <div class="manga-inner-wrap desktop-background" style="background-image: url(<?= get_the_post_thumbnail_url()?>)">
                    <div class="manga-inner">


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
               
                    <div class="summary_content_wrap">
                        <div class="summary_content">
                            <div class="post-content">  
								<?php get_template_part( 'html/ajax-loading/ball-pulse' ); ?>
                                <h4 class="read-type"><?php  switch($chapter_type){
						        case 'video': echo esc_html__('Drama', WP_MANGA_TEXTDOMAIN);break;
						        case 'text': echo esc_html__('Novel', WP_MANGA_TEXTDOMAIN);break;
						        case 'manga': echo esc_html__('Manga', WP_MANGA_TEXTDOMAIN); break;
					            } ?></h4>
                               
                                
                                <h1><?php echo esc_html( get_the_title() ); ?></h1>

                                <div class="stats-read-wrapper">

                                <div class="genre">
									
                                <?php do_action('wp-manga-manga-properties', $post_id); ?>
                                </div>
                                <?php do_action('wp-manga_status', $post_id);?>
                                <?php 
                                echo '
                                <div class="post-content_item">
                                <div class="summary-heading">
                                <i class="fas fa-chart-bar"></i>
                                <div class="summary-heading-wrapper">

                                
                                <p>Avg. Upload Rate /m: '
                                . $totalCount  .
                                '</p>
                                </div>
                                </div>
                                </div>' 
                                ?>
                                <?php 
                                echo '
                                <div class="post-content_item">
                                <div class="summary-heading">
                                <i class="fas fa-chart-bar"></i>
                                <div class="summary-heading-wrapper">

                                <p>
                                Free release per week: '
                                . $free_releases .
                                '</p>
                                </div>
                                </div>
                                </div>
                                </div>' 
                                ?>
                                <div class="stats-bottom-wrapper">
                                    <?php do_action('wp-manga_author', $post_id);?>    
                                    <div class="post-status">
                                    <?php do_action('wp-manga_rating', $post_id);?>
                                    </div>

                                </div>
                             </div>
                            
                        <div class = "madara_buttons">
                            <?php do_action('wp-manga-after-manga-properties', $post_id);?>
                        </div>
                        </div>
                            
                            </div>
                    </div>
                </div>
                </div>


                    
                <div class="c-page">
                        <!-- <div class="c-page__inner"> -->
                            <div class="c-page__content">
                                <div class="manga-tab-wrapper">
                                <div class="container">
                                    <ul class="nav nav-tabs madara-tabs">
                                        <li class="madara-tab-link"><a data-toggle="tab" href="#menu1">About</a></li>
										<span class="madara-separator">|</span>
										<li class="madara-tab-link"><a class="active" data-toggle="tab" href="#home">Chapters</a></li>
                                       
                                    </ul>

                                    <div class="tab-content">
                                        <div id="menu1" class="tab-pane fade in ">
                                            
                                            <?php if ( get_the_content() != '' ) { ?>
                                                <div id = "manga-about">
<!--                                                 <div class="c-blog__heading style-2 font-heading" style="margin-bottom: 15px">

                                              		<h2 class="h4">
                                                        <?php echo esc_attr__( 'Summary', 'madara' ); ?>
                                                    </h2>
                                                </div> -->

                                                <div class="description-summary">

                                                    <div class="summary__content <?php echo( esc_attr($manga_single_summary == 'on' ? 'show-more' : '' )); ?>">						<h4 class="madara-sypnosis">
														Sypnosis
														</h4>
														
                                                        <?php the_content(); ?>
                                                    </div>

                                                    <?php if ( $manga_single_summary == 'on' ) { ?>
                                                        <div class="c-content-readmore">
                                                            <span class="btn btn-link content-readmore">
                                                                <?php echo esc_html__( 'Show more  ', 'madara' ); ?>
                                                            </span>
                                                </div>
                                            <?php } ?>
                                    </div>

                                <?php } ?>
                                </div>
                                        </div>

                                        <div id="home" class="tab-pane fade active show">

                                        <?php do_action('wp-manga-chapter-listing', $post_id); ?>

										</div>
                                    </div>
                                </div>


                                        </div>
                            </div>
                            
                        <!-- </div> -->
                            
                    
                </div>
                            <div class="<?php echo esc_attr( $main_col_class ); ?> manga-discussion">
                                        <!-- comments-area -->
                                        <?php do_action( 'wp_manga_discussion' ); ?>
                                        <!-- END comments-area -->
                                    </div>
                                                            
        </div>
       
    </div>



<?php do_action( 'after_manga_single' ); ?>
</div>
<?php get_footer();