<?php
	/**
	 * Madara Functions and Definitions
	 *
	 * @package madara
	 */
	require( get_template_directory() . '/app/theme.php' );
	
	if ( did_action( 'elementor/loaded' ) ) {

		require_once( get_theme_file_path( '/elementor-widget/widget-init.php' ) );
	}	
/**
 * Volume Pricing for buyCRED
 * @mycred
 * @version 1.0
 */
add_filter( 'mycred_buycred_get_cost', 'mycredpro_buyred_volume_pricing', 10, 2 );
function mycredpro_buyred_volume_pricing( $cost, $amount ) {

	$args = array(
	   'post_type' => 'coin-conversion',
	   'status'	=> 'published'
   );
   $field_id = 'wp_coins';
   $fieldset = 'type=fieldset_text';
   $arr = new WP_Query($args);

   if($arr->have_posts()){
	   while($arr->have_posts()){
	   $arr->the_post();
		$values = rwmb_meta($field_id, $fieldset, $arr->post->ID);
			foreach ( $values as $value ) {
				if($amount == $value['point']){
					$cost =  $value['dollar'];
				}
			}
		}
   }
   wp_reset_postdata();
   return $cost;
}
	add_action( 'wp_enqueue_scripts', 'load_more_posts' );
	add_action('wp_ajax_loadmore', 'loadmore_ajax_handler'); // wp_ajax_{action}
	add_action('wp_ajax_nopriv_loadmore', 'loadmore_ajax_handler'); // wp_ajax_nopriv_{action}

	function load_more_posts() {
       
        global $wp_query; 
     
        
        wp_enqueue_script('jquery');
     
        // 
        wp_register_script( 'ajax_js', get_stylesheet_directory_uri() . '/js/custom-ajax.js', array('jquery'));
	
     
        
        wp_localize_script( 'ajax_js', 'loadmore_params', array(
            'ajaxurl' => site_url() . '/wp-admin/admin-ajax.php', // WordPress AJAX
            'current_page' => (get_query_var( 'paged' )) ? ( get_query_var('paged')) + 1 : 2,
            'post_count'    => 8,
           
        ) );
     
         wp_enqueue_script( 'ajax_js' );
	
    }
	function loadmore_ajax_handler(){
 
		// Arguments for Query
		$args['paged'] = ($_POST['paged']) + 1; //Page to be loaded
		$args['post_status'] = 'publish';
		$args['ppp']        = $_POST['posts_per_page']+4;
	
		if($_POST['term'] > 0){
			$loop = new WP_Query(array(
				'posts_per_page' =>$args['ppp'],
				'paged'         => $args['paged'],
				'post_status'   => $args['post_status'],
				'tax_query'		=> array(
					array(
						'taxonomy'	=> 'wp-manga-genre',
						'terms'	=> $_POST['term'],
						'field'	=> 'term_id'
					)
					)
			));

		}else{
			$loop = new WP_Query(array(
				'post_type' 	=> 'wp-manga',	
				'posts_per_page' =>$args['ppp'],
				'paged'         => $args['paged'],
				'post_status'   => $args['post_status'],
			));
		}
	
	 
		if( $loop->have_posts() ) :
	 
			// run the loop
			while( $loop->have_posts() ): $loop->the_post();
	 
			 ?>
			 
			 <div class="post">

				<a href="<?= get_the_permalink( $loop->post->ID) ?>">
				<?php if ( has_post_thumbnail() ) {
				the_post_thumbnail();
				} else { ?>
				<img src="<?php bloginfo('template_directory'); ?>/images/default.png" />
				<?php } ?>
				</a>
				</div>

			 <?php
	 
			endwhile;
	 
		endif;
		die; // here we exit the script and even no wp_reset_query() required!
	}


	add_filter( 'rwmb_meta_boxes', 'your_prefix_register_meta_boxes' );

	function your_prefix_register_meta_boxes( $meta_boxes ) {
		$prefix = 'wp_';

		$meta_boxes[] = [
			'title'      => esc_html__( 'Conversion', 'online-generator' ),
			'id'         => 'convert',
			'post_types' => ['coin-conversion'],
			'context'    => 'side',
			'fields'  => [
				[
					'type'    => 'fieldset_text',
					'name'    => esc_html__( 'Fieldset Text', 'online-generator' ),
					'id'      => $prefix . 'coins',
					'options' => [
						'point'  => 'Points',
						'dollar' => 'Money',
					],
					'clone'   => true,
				],
			],
		];
	
		return $meta_boxes;
	}
	add_action( 'init', 'coin_conversion' );
	function coin_conversion() {
		$args = [
			'label'  => esc_html__( 'Coins Conversion', 'text-domain' ),
			'labels' => [
				'menu_name'          => esc_html__( 'Coins Conversion', 'coin-convert' ),
				'name_admin_bar'     => esc_html__( 'Coin Conversion', 'coin-convert' ),
				'add_new'            => esc_html__( 'Add Coin Conversion', 'coin-convert' ),
				'add_new_item'       => esc_html__( 'Add new Coin Conversion', 'coin-convert' ),
				'new_item'           => esc_html__( 'New Coin Conversion', 'coin-convert' ),
				'edit_item'          => esc_html__( 'Edit Coin Conversion', 'coin-convert' ),
				'view_item'          => esc_html__( 'View Coin Conversion', 'coin-convert' ),
				'update_item'        => esc_html__( 'View Coin Conversion', 'coin-convert' ),
				'all_items'          => esc_html__( 'All Coins Conversion', 'coin-convert' ),
				'search_items'       => esc_html__( 'Search Coins Conversion', 'coin-convert' ),
				'parent_item_colon'  => esc_html__( 'Parent Coin Conversion', 'coin-convert' ),
				'not_found'          => esc_html__( 'No Coins Conversion found', 'coin-convert' ),
				'not_found_in_trash' => esc_html__( 'No Coins Conversion found in Trash', 'coin-convert' ),
				'name'               => esc_html__( 'Coins Conversion', 'coin-convert' ),
				'singular_name'      => esc_html__( 'Coin Conversion', 'coin-convert' ),
			],
			'public'              => true,
			'exclude_from_search' => false,
			'publicly_queryable'  => true,
			'show_ui'             => true,
			'show_in_nav_menus'   => true,
			'show_in_admin_bar'   => true,
			'show_in_rest'        => true,
			'capability_type'     => 'post',
			'hierarchical'        => false,
			'has_archive'         => true,
			'query_var'           => true,
			'can_export'          => true,
			'rewrite_no_front'    => false,
			'show_in_menu'        => true,
			
			
			'rewrite' => true
		];
	
		register_post_type( 'coin-conversion', $args );
	}