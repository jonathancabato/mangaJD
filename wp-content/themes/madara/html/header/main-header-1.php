<?php

	/**
	 * Hook to wrap Main Header div
	 */
	do_action( 'madara_main_header_before', 1 );

    
	global $wp_manga_user_actions;
	$wp_manga             = madara_get_global_wp_manga();
	
	$user_enabled = ! is_user_logged_in() && get_option( 'users_can_register' );
	$user_manga_logged = is_user_logged_in() && class_exists( 'WP_MANGA' );
?>
<div class="header-menu-wrapper">

    <div class="search-navigation search-sidebar">

        <a href="<?= home_url();?>?s" class="search-nav-link"><i class="fa fa-search"></i>Search..</a>

        <?php if ( $user_enabled ) { ?>
        <div class="c-sub-header-nav header-area">
        <div class="c-sub-nav_wrap">
                <div class="c-modal_item">
                    <!-- Button trigger modal -->
                    <a href="javascript:void(0)" data-toggle="modal" data-target="#form-login" class="btn-active-modal modal-header-btn modal-log-in"><?php echo esc_html__( 'Sign in', 'madara' ); ?></a>
                    <a href="javascript:void(0)" data-toggle="modal" data-target="#form-login" class="btn-active-modal modal-header-btn"><?php echo esc_html__( 'Sign up', 'madara' ); ?></a>
                </div>
        </div>

        </div>
            <?php } elseif ( $user_manga_logged ) { ?>
            
        <div class="c-sub-header-nav header-area">

            <div class="c-sub-nav_wrap">
                <div class="c-modal_item">
                    <?php if(has_nav_menu('primary_menu')){ ?>
                        <div class="distorted-menu" style="display:none">
                        <?php 
                            wp_nav_menu([
                                'theme_location'    => 'primary_menu',
                                'fallback_cb'       => false,
                                'container'         => false,
                                'depth'             => 0,
                              
                            ]);
                            ?>
                        </div>
                    <?php } ?>
                    <?php
                        if(defined('WP_MANGA_VER') && WP_MANGA_VER >= 1.6){
                            $wp_manga_user_actions->get_user_section( 50, true);
                        } else {
                            echo wp_kses_post($wp_manga_user_actions->get_user_section());
                        }
                    ?>
                </div>
                </div>
                </div>
            <?php }else {
                    ?>
 
 <div class="c-modal_item">
                    <!-- Button trigger modal -->
                    <a href="javascript:void(0)" data-toggle="modal" data-target="#form-login" class="btn-active-modal"><?php echo esc_html__( 'Sign in', 'madara' ); ?></a>
                    <a href="javascript:void(0)" data-toggle="modal" data-target="#form-sign-up" class="btn-active-modal"><?php echo esc_html__( 'Sign up', 'madara' ); ?></a>
                </div>
                    <?php

            } 
            
            ?>
        
    </div>
    <?php if ( is_user_logged_in() ) { ?>
    <div class="c-togle__menu">
        <button type="button" class="menu_icon__open">
            <span></span> <span></span> <span></span>
        </button>
    </div>
    <?php }else{ ?>
        <div class="c-togle__menu">
        <div class="c-user_avatar">
            <div class="c-user_avatar-image">
				  
            <a href="javascript:void(0)" data-toggle="modal" data-target="#form-login" class="btn-active-modal modal-header-btn modal-log-in"> <img alt="" src="<?= get_home_url(); ?>/wp-content/uploads/2021/03/dark_avast@2x.png" class="avatar avatar-50 photo" height="50" width="50" loading="lazy"></a>
				
    </div>
					</div>
                    </div>
                    <?php } ?>
</div>

<?php

	/**
	 * Hook to wrap Main Header div
	 */
	do_action( 'madara_main_header_after', 1 );
