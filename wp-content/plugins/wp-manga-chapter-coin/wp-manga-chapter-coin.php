<?php
	/**
	 *  Plugin Name: WP Manga - Chapter Coin
	 *  Description: Set coin value for Chapters. Works with MyCred plugin
	 *  Plugin URI: https://www.mangabooth.com/
	 *  Author: MangaBooth
	 *  Author URI: https://themeforest.net/user/wpstylish
	 *  Author Email: mangabooth@gmail.com
	 *  Version: 1.1.2.5
	 *  Text Domain: wp-manga-chapter-coin
	 * @since 1.0
     *
     * @required - WP Manga Core 1.6.5
	 */

if ( ! defined( 'WP_MANGA_CHAPTER_COIN_FILE' ) ) {
	define( 'WP_MANGA_CHAPTER_COIN_FILE', __FILE__ );
}

 // plugin dir URI
if ( ! defined( 'WP_MANGA_CHAPTER_COIN_URI' ) ) {
	define( 'WP_MANGA_CHAPTER_COIN_URI', plugin_dir_url( __FILE__ ) );
}

// plugin dir path
if ( ! defined( 'WP_MANGA_CHAPTER_COIN_DIR' ) ) {
	define( 'WP_MANGA_CHAPTER_COIN_DIR', plugin_dir_path( __FILE__ ) );
}


define('MANGA_CHAPTER_COIN_TEXT_DOMAIN', 'wp-manga-chapter-coin');

class WP_MANGA_ADDON_CHAPTER_COIN {
	private static $instance;

	public static function get_instance() {
		if ( null == self::$instance ) {
			self::$instance = new WP_MANGA_ADDON_CHAPTER_COIN();
		}

		return self::$instance;
	}
    
    public $user_bought_chapters;
    
    public $_chapter_coins = array();

	private function __construct() {
		add_action( 'init', array( $this, 'init' ) );
		
		add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_scripts' ) );
		
		add_filter('wp_manga_get_all_chapters', array($this, 'filter_get_all_chapters'), 10, 2);
		add_filter('wp_manga_latest_chapters', array($this, 'filter_get_latest_chapters'), 10, 7);
		
		add_filter('wp_manga-chapter-url', array($this, 'block_premium_chapter_url'), 100, 6); 
		add_action('wp_manga_before_chapter_name', array($this, 'add_before_chapter_name'), 10, 2);
		add_filter('wp_manga_chapter_item_class', array($this, 'wp_manga_chapter_item_class_for_premium'), 100, 3);
		add_filter('wp_manga_chapter_select_option_class', array($this, 'wp_manga_chapter_item_class_for_premium'), 10, 3);
		add_filter('wp_manga_chapter_nagivation_button_class', array($this, 'wp_manga_chapter_item_class_for_premium'), 10, 4);
		
		add_action('wp_manga_chapter_content_alternative', array($this, 'chapter_content_alternative'));
		add_filter('wp_manga_chapter_images_data', array($this, 'wp_manga_chapter_images_data'));
		add_filter( 'body_class', array($this, 'body_custom_class' ));
		
		add_action('wp_head', array($this, 'wp_head'));
		add_action('wp_footer', array($this, 'wp_footer'));
		add_filter('wp_manga_user_menu_before_items', array($this, 'wp_manga_user_menu_before_items'));
		
		add_action( 'madara_user_nav_tabs', array($this, 'user_settings_tab_nav'), 10, 2 );
		add_action( 'madara_user_nav_contents', array($this, 'user_settings_tab_content'), 10, 2);
		
		add_filter('wp_manga_db_get_SELECT', array($this, 'filter_wp_manga_db_get_SELECT'), 10, 6);
		add_filter('wp_manga_db_get_TABLE', array($this, 'filter_wp_manga_db_get_TABLE'), 10, 6);
		add_filter('wp_manga_db_get_WHERE', array($this, 'filter_wp_manga_db_get_WHERE'), 10, 6);
		
		require WP_MANGA_CHAPTER_COIN_DIR . 'admin/dbsetup.php';
		require WP_MANGA_CHAPTER_COIN_DIR . 'admin/backend.php';
		require WP_MANGA_CHAPTER_COIN_DIR . 'admin/reporter.php';
		require WP_MANGA_CHAPTER_COIN_DIR . 'inc/shortcodes.php';
		
		add_action( 'init', array($this, '__check_requirements' ));
		if(get_option('wp_manga_chapter_coin_db_ver','') == ''){
			wmcc_setup_db();
		}			
		
		// init admin functions
		WP_MANGA_ADDON_CHAPTER_COIN_BACKEND::get_instance();
		
		// report coins usage
		WP_MANGA_ADDON_CHAPTER_COIN_REPORT::get_instance();
	} 
	
	function body_custom_class( $classes ) {
		if(is_manga_reading_page()){
			global $wp_manga;
				
			$wp_manga_functions = madara_get_global_wp_manga_functions();
			
			$manga_id  = get_the_ID();
			$reading_chapter = madara_permalink_reading_chapter();
			
			if ( $reading_chapter ) {
				$coin = $this->is_premium_chapter($reading_chapter['chapter_id']);
			
				if($coin){
					$user_id = get_current_user_id();
					
					if(!$user_id || !$this->has_bought($user_id, $reading_chapter)){
						if(!$this->has_special_roles($user_id)){
							$classes[] = 'chapter-blocked';
						}
					}
				}
				
			}
		}
		
		return $classes;
	}
	
	function __check_requirements(){
		if(!class_exists('myCRED_Core')){
			add_action('admin_notices', function(){
					$class = 'notice notice-warning is-dismissible';
					$message = sprintf(__('WP Manga - Chapter Coin requires %1$s MyCred plugin %2$s to be activated', MANGA_CHAPTER_COIN_TEXT_DOMAIN), '<a href="https://wordpress.org/plugins/mycred/" target="_blank">', '</a>');

					printf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($class), $message);
			});
		}
	}
	
	function wp_enqueue_scripts(){
		wp_enqueue_style( 'chapter-coin-css', WP_MANGA_CHAPTER_COIN_URI . 'assets/css/chapter-coin.css' );
		wp_enqueue_script( 'chapter-coin-js', WP_MANGA_CHAPTER_COIN_URI . 'assets/js/frontend.js', array( 'wp-manga' ), '1.0' );
	}
	
	function wp_head(){
		$settings = wp_manga_chapter_coin_get_settings();
		?>
		<style type="text/css">
			.wp-manga-chapter.free-chap .coin{background-color:<?php echo esc_attr($settings['free_background']);?>; color:<?php echo esc_attr($settings['free_color']);?>}
			.wp-manga-chapter.premium .coin{background-color:<?php echo esc_attr($settings['unlock_background']);?>; color:<?php echo esc_attr($settings['unlock_color']);?>}
			.wp-manga-chapter.premium.premium-block .coin{background-color:<?php echo esc_attr($settings['lock_background']);?>; color:<?php echo esc_attr($settings['lock_color']);?>}
			.shortcode-top-bought .item-thumb .index{background-color:<?php echo esc_attr($settings['ranking_background']);?>; color:<?php echo esc_attr($settings['ranking_text_color']);?>}
		</style>
		<?php
	}
    
    function init(){
        $this->load_plugin_textdomain();
        
        $user_id = get_current_user_id();
        $this->user_bought_chapters = array();
        
        if($user_id){
            $this->user_bought_chapters[$user_id] = false;
            $this->user_bought_chapters[$user_id] = $this->get_user_bought_chapters( $user_id );
        }
    }
	
	function load_plugin_textdomain() {
		load_plugin_textdomain( MANGA_CHAPTER_COIN_TEXT_DOMAIN, false, plugin_basename( dirname( __FILE__ ) ) . '/languages' );
	}
	
	function user_settings_tab_nav($tab_pane, $account ){
		global $wp_manga_user_actions;
		?>
		<li class="<?php echo esc_attr( $tab_pane == 'bought' ? 'active' : ''); ?>">
                    <a href="<?php echo esc_url( $wp_manga_user_actions->get_user_tab_url( 'bought' ) ); ?>"><i class="fas fa-coins"></i><?php echo esc_html__( 'My Bought Mangas', MANGA_CHAPTER_COIN_TEXT_DOMAIN ); ?>
                    </a>
                </li>
		<?php
	}
	
	function user_settings_tab_content( $tab_pane, $account ) {
		if( $tab_pane == 'bought' ){
			$this->load_template('user-settings', 'bought');
		}
	}
	
	function get_user_balance($user_id){
		if(function_exists('mycred_get_users_balance')){
			$coin = mycred_get_users_balance( $user_id );
		} else {
			$coin = 0;
		}
		
		return $coin;
	}
	
	function wp_manga_user_menu_before_items( $html ){
		$coin = $this->get_user_balance(get_current_user_id());
		
		global $wp_manga_user_actions;
		$url = $wp_manga_user_actions->get_user_tab_url( 'bought' );
		// $html .= '<li><a href="' . esc_url($url) . '">' . sprintf(wp_kses_post(__('Purchase Points', MANGA_CHAPTER_COIN_TEXT_DOMAIN)), number_format($coin)). '</a></li>';
		
		return $html;
	}
	
	function block_premium_chapter_url($url, $post_id, $chapter, $page_style = null, $host = null, $paged = null){
		global $wp_manga_chapter, $_wp_manga_wpseo_sitemap;
		
		if(!isset($_wp_manga_wpseo_sitemap) || !$_wp_manga_wpseo_sitemap){
			$user_id = get_current_user_id();
			
			if($chapter){
				if($this->is_premium_chapter($chapter['chapter_id'])){
					if(!$user_id || !$this->has_bought($user_id, $chapter)){
						if(!$this->has_special_roles($user_id)){
							return '#';
						}
					}
				}
			}
		}
		
		return $url;
	}
	
	/**
	 * Return class name for each chapter item
	 **/
	function wp_manga_chapter_item_class_for_premium( $class, $chapter, $manga_id, $link = ''){
		$user_id = get_current_user_id();
        
        if(!$chapter) { return $class; }
		
		if($link != '' && strpos($link, '?#/p/') === false && $link != '#'){
			// this is Chapter Page navigation, so we don't need to check for Chapter status
			return $class;
		}
		
		
		if($coin = (isset($chapter['price']) ? $chapter['price'] : $this->is_premium_chapter($chapter['chapter_id']))){
			$class .= ' premium coin-' . $coin . ' data-chapter-' . $chapter['chapter_id'];
			
			if(!$user_id || !(isset($chapter['bought']) ? $chapter['bought'] : $this->has_bought($user_id, $chapter))){
				if(!$this->has_special_roles($user_id)){
					return $class . ' premium-block';
				}
			}
		} else {
			$class .= ' free-chap data-chapter-' . $chapter['chapter_id'];
		}
		
		return $class;
	}
	
	/**
	 * print out meta data before chapter name in the Manga's chapter list
	 **/
	public function add_before_chapter_name( $chapter, $manga_id ){
		if(isset($chapter['price'])){
			$coin = $chapter['price'];
			
		} else {
			$coin = $this->get_chapter_coin($chapter['chapter_id']);
		
			if(($coin == '') || ($coin == -1)){
				$coin = $this->get_default_coin();
			}
		}
		if($chapter['bought']){
			echo '<span class="coin"><i class="fas fa-unlock"></i>Unlocked</span>';
		}
	 	else if($coin != 0){
			echo '<span class="coin"><i class="fas fa-lock"></i>' . $coin . '  Points</span>';
		} else {
			$settings = wp_manga_chapter_coin_get_settings();
			
			echo '<span class="coin free">' . $settings['free_word'] . '</span>';
		}
	}
	
	function wp_footer(){
		$this->load_template('modal','buy-coin');
		
		$user_id = get_current_user_id();
		if($user_id){
			echo '<input type="hidden" value="' . $this->get_user_balance($user_id) . '" id="wp_manga_chapter_coin_user_balance"/>';
		}
	}
	
	// block URLs of images which are in a protected chapter
	function wp_manga_chapter_images_data( $pages ){
		
		if(!is_admin()){
			
			$manga_id = is_singular('wp-manga') ? get_the_ID() : (isset($_GET['postID']) ? intval($_GET['postID']) : 0);
			$reading_chapter = function_exists('madara_permalink_reading_chapter') ? madara_permalink_reading_chapter() : false;
	
			if(!$reading_chapter){
				 // support Madara Core before 1.6
				 if($chapter_slug = get_query_var('chapter')){
					global $wp_manga_functions;
					$reading_chapter = $wp_manga_functions->get_chapter_by_slug( $post_id, $chapter_slug );
				 }
				 if(!$reading_chapter){
					return;
				 }
			}
			
			$chapter_slug     = $reading_chapter['chapter_slug'];
			
			global $wp_manga;
			
			$wp_manga_functions = madara_get_global_wp_manga_functions();
			
			if ( $reading_chapter ) {
				$coin = $this->is_premium_chapter($reading_chapter['chapter_id']);
		
				if($coin){
					$user_id = get_current_user_id();
					
					if(!$user_id || !$this->has_bought($user_id, $reading_chapter)){
						if(!$this->has_special_roles($user_id)){
							return array();
						}
					}
				}
			}
		}
		
		return $pages;
	}

	/**
	 * If chapter is not blocked, return empty. Otherwise, return a message
	 **/
	function chapter_content_alternative(){
		global $wp_manga;
		
		$wp_manga_functions = madara_get_global_wp_manga_functions();
		
		$manga_id  = get_the_ID();
		$reading_chapter = function_exists('madara_permalink_reading_chapter') ? madara_permalink_reading_chapter() : false;
		
		if ( $reading_chapter ) {
			$coin = $this->is_premium_chapter($reading_chapter['chapter_id']);
		
			if($coin){
				$user_id = get_current_user_id();
				
				if(!$user_id || !$this->has_bought($user_id, $reading_chapter)){
					if(!$this->has_special_roles($user_id)){
						return '<div class="premium coin-' . $coin . ' data-chapter-' . $reading_chapter['chapter_id'] . ' content-blocked premium-block">' . wp_kses_post(__('This chapter is locked!', MANGA_CHAPTER_COIN_TEXT_DOMAIN)) . ' <a href="#">' . esc_html__('Buy it?', MANGA_CHAPTER_COIN_TEXT_DOMAIN) . '</a></div>';
					}
				}
			}
			
		}
		
		return '';
	}
	
	/**
	 * Get default coin value for all chapters
	 **/
	public function get_default_coin(){
		$settings = wp_manga_chapter_coin_get_settings();
		return $settings['default_coin'];
	}
	
	/**
	 * Check if an user has bought the chapter
	 *
	 * $chapter - object/int - Chapter ID, or Chapter Object
	 **/
	public function has_bought($user_id, $chapter){
		if(class_exists('myCRED_Query_Log')){
			$chapter_id = $chapter;
			if(is_array($chapter)){
				$chapter_id = $chapter['chapter_id'];
			}
			
			if($this->is_premium_chapter($chapter_id)){
				$args = array(
					'ref'  => 'buy_chapter',
					'user_id' => $user_id,
					'ref_id' => $chapter_id,
					'cache_results' => false
				);
				$logs = new myCRED_Query_Log( $args );
				if($logs && $logs->results){
					return true;
				}
			}
		}
        
		return false;
	}
	
	/**
	 * Return myCred logs
	 **/
	public function get_user_bought_chapters($user_id){
        if(!isset($this->user_bought_chapters[$user_id]) || $this->user_bought_chapters[$user_id] === false){
            error_log(var_export($this->user_bought_chapters[$user_id], true));
            if(class_exists('myCRED_Query_Log')){
                $chapters = wp_cache_get($user_id . '_bought_chapters');
                if($chapters === false){
                    $args = array(
                            'ref'  => 'buy_chapter',
                            'user_id' => $user_id,
                            'cache_results' => false
                        );
                    $logs = new myCRED_Query_Log( $args );
                    
                    if($logs){
                        wp_cache_set($user_id . '_bought_chapters', $logs->results, 'bought_chapters', 60 * 60);
                        return $logs->results;
                    }
                }
                
                return $chapters;
            }
            
            return array();
        }
        
        return $this->user_bought_chapters[$user_id];
	}
	
	/**
	 * Get list of Manga IDs of all chapters that an user has bought
	 **/
	public function get_user_bought_mangas($user_id){
		$mangas = array();
		
		$chapters = $this->get_user_bought_chapters($user_id);
		if($chapters && count($chapters) > 0){
			foreach($chapters as $chapter){
				$data = unserialize($chapter->data);
				if(isset($data['manga_id']) && !in_array($data['manga_id'], $mangas)){
					array_push($mangas, $data['manga_id']);
				}
			}
		}
		
		return $mangas;
	}
	
	/**
	 * Get top bought mangas in a period
	 *
	 * $period - int - number of days ago ( > 0 )
	 **/
	public function get_top_bought_mangas( $period = 7 ){
		if($data = wp_cache_get('wp_manga_chapter_coin_ranks_' . $days))
			return $data;
		
		$args = array('post_type' => 'wp-manga',
						'posts_per_page' => -1
					);
		
		$mangas = get_posts($args);
		
		$data = array();
		
		$backend = WP_MANGA_ADDON_CHAPTER_COIN_BACKEND::get_instance();
		
		$date_to = date('Y-m-d H:i:s');
		$date_from = date('Y-m-d 00:00:00', strtotime('-' . $period . ($period == 1 ? ' day' : ' days')));
		
		foreach($mangas as $manga){
			$item = array(
						'id' => $manga->ID,
						'manga' => $manga,
						'title' => $manga->post_title,
						'author' => $manga->post_author,
						'coins' => $backend->get_revenue($manga->ID, $date_from, $date_to)
						);
						
			array_push($data, $item);
		}
		
		usort($data, function( $item1, $item2 ){
			if($item1['coins'] == $item2['coins']){
				return 0;
			}
			
			return ($item1['coins'] > $item2['coins'] ? -1 : 1);
		});
		
		$rank_count = apply_filters('wp_manga_chapter_coin_rank_count', 20);
		$data = array_slice($data, 0, $rank_count);
		
		wp_cache_set('wp_manga_chapter_coin_ranks_' . $days, $data, 'wp_manga_chapter_coin', 60 * 60);
		
		return $data;
	}
	
	/**
	 * Check if an user has special roles to view chapters without buying it
	 **/
	public function has_special_roles($user_id){
		$user = get_userdata( $user_id );
		$user_roles = empty( $user ) ? array() : $user->roles;
		
		$valid_roles = apply_filters('wp_manga_chapter_coin_special_roles', array('administrator', 'editor'));
		foreach($valid_roles as $role){
			if(in_array($role, $user_roles)){
				return true;
			}
		}
		
		return false;
	}
	
	/**
	 * Join _manga_chapter_coin table into the query so we can access chapter coin easily
	 **/
	function filter_wp_manga_db_get_SELECT($select, $table, $where, $orderBy, $order, $limit){
		global $wpdb;
		
		if(strpos($table, $wpdb->prefix . 'manga_chapters') !== false){
			$select .= ", CASE WHEN {$wpdb->prefix}manga_chapter_coin.coin IS NULL THEN '' ELSE {$wpdb->prefix}manga_chapter_coin.coin END AS coin";
		}
		
		return $select;
	}
	
	/**
	 * Join _manga_chapter_coin table into the query so we can access chapter coin easily
	 **/
	function filter_wp_manga_db_get_TABLE($table, $select, $where, $orderBy, $order, $limit){
		global $wpdb;
		
		if(strpos($table, $wpdb->prefix . 'manga_chapters') !== false){
			$table .= " LEFT JOIN {$wpdb->prefix}manga_chapter_coin ON {$wpdb->prefix}manga_chapters.chapter_id = {$wpdb->prefix}manga_chapter_coin.chapter_id";
		}
		
		return $table;
	}
	
	/**
	 * Join _manga_chapter_coin table into the query so we can access chapter coin easily
	 **/
	function filter_wp_manga_db_get_WHERE($where, $table, $select, $orderBy, $order, $limit){
		global $wpdb;
		
		if(strpos($table, $wpdb->prefix . 'manga_chapters') !== false){
			// to prevent ambiguous for query 
			
			if(strpos($where, ' chapter_id ') !== false){
				$where = str_replace(" chapter_id ", " {$wpdb->prefix}manga_chapters.chapter_id ", $where);
			}
			
			if(strpos($where, 'chapter_id ') == 0){
				$from = '/'.preg_quote('chapter_id ', '/').'/';

				$where = preg_replace($from, "{$wpdb->prefix}manga_chapters.chapter_id ", $where, 1);
			}
			
			
		}
		
		return $where;
	}
	
	function filter_get_latest_chapters($chapters, $post_id, $q, $num, $all_meta, $orderby, $order){
		$user_id = get_current_user_id();
		
		if($user_id){
			$logs = $this->get_user_bought_chapters($user_id);
		}
		
		$new_chapters = array();
		
		foreach($chapters as $chapter){
			$has_coin = false;
			
			if(isset($chapter['coin'])){
				$coin = $chapter['coin'];
				if($coin == '' || $coin == -1){
					$coin = $this->get_default_coin();
				}
				
				$chapter['price'] = $coin;
			} else {
				// this is to support old Madara Core plugin (before 1.6.1.4) which does not have filter SQL yet
				$chapter['price'] = $this->is_premium_chapter($chapter['chapter_id']);
			}
			
			$chapter['bought'] = false;
			if($user_id){
				// check if this chapter is bought by user
				if(isset($logs) && is_array($logs)){
					foreach($logs as $log){
						$data = unserialize( $log->data );
						if($data['chapter_id'] == $chapter['chapter_id']){
							$chapter['bought'] = true;
							break;
						}										
					}
				}
			}
			
		
			
			array_push($new_chapters, $chapter);
		}
		
		return $new_chapters;
	}
	
	/**
	 * Filter get_all_chapters result to add Chapter Price & Bought properties for chapter
	 **/
	function filter_get_all_chapters($all_chapters, $manga_id){
		$user_id = get_current_user_id();
		
		if($user_id){
			$logs = $this->get_user_bought_chapters($user_id);
		}
		
		$default_coin_value = $this->get_default_coin();
		
		$new_all_chapters = $all_chapters;
		
		// get all chapter ids so we can query their coin values at once
		$chapter_ids = array();
		
		foreach($all_chapters as $vol_id => $volumn){
			$new_volumn = $volumn;
			$new_volumn['chapters'] = array();
					
			foreach($volumn['chapters'] as $chapter){
				if(isset($chapter['coin'])){
					$has_coin = false;
					
					$coin = $chapter['coin'];
					
					if($coin == '' || $coin == -1) {
						$coin = $default_coin_value;
					}
					
					$chapter['price'] = $coin;
					
					$chapter['bought'] = false;
					if($user_id){
						// check if this chapter is bought by user
						if(isset($logs) && is_array($logs)){
							foreach($logs as $log){
								$data = unserialize( $log->data );
								if(isset($data['chapter_id']) && $data['chapter_id'] == $chapter['chapter_id']){
									$chapter['bought'] = true;
									break;
								}										
							}
						}
					}
					
					// if($chapter['price'] > 0){
					// 	if(!$chapter['bought']){
					// 		$chapter['chapter_name'] .= ' <i class="fas fa-lock"></i>';
					// 	} else {
					// 		$chapter['chapter_name'] .= ' <i class="fas fa-lock-open"></i>';
					// 	}
					// }
					
					array_push($new_volumn['chapters'], $chapter);
					
				} else {
					// this is to support old Madara Core plugin (before 1.6.1.4) which does not have filter SQL yet
					array_push($chapter_ids, $chapter['chapter_id']);
				}
			}
			
			$new_all_chapters[$vol_id] = $new_volumn;
		}
		
		// if we still need to query chapter coin again
		if(count($chapter_ids) > 0){
			$new_all_chapters = $all_chapters;
			
			$sql = "SELECT * FROM {$wpdb->prefix}manga_chapter_coin WHERE chapter_id IN (" . implode(',', $chapter_ids) . ")";
			global $wpdb;
			$chapter_coins = $wpdb->get_results($sql);
			if($chapter_coins && count($chapter_coins) > 0){
				
				// update chapter price & bought properties
				foreach($all_chapters as $vol_id => $volumn){
					$new_volumn = $volumn;
					$new_volumn['chapters'] = array();
					
					foreach($volumn['chapters'] as $chapter){
						$has_coin = false;
						
						foreach($chapter_coins as $chapter_coin){
							if($chapter_coin->chapter_id == $chapter['chapter_id']){
								$coin = $chapter_coin->coin;
								break;
							}
						}
						
						if($coin == '' || $coin == -1) {
							$coin = $default_coin_value;
						}
						
						$chapter['price'] = $coin;
						
						$chapter['bought'] = false;
						if($user_id){
							// check if this chapter is bought by user
							if(isset($logs) && is_array($logs)){
								foreach($logs as $log){
									$data = unserialize( $log->data );
									if(isset($data['chapter_id']) && $data['chapter_id'] == $chapter['chapter_id']){
										$chapter['bought'] = true;
										break;
									}										
								}
							}
						}
						
						// if($chapter['price'] > 0){
						// 	if(!$chapter['bought']){
						// 		$chapter['chapter_name'] .= ' <i class="fas fa-lock"></i>';
						// 	} else {
						// 		$chapter['chapter_name'] .= ' <i class="fas fa-lock-open"></i>';
						// 	}
						// }
						
						array_push($new_volumn['chapters'], $chapter);
					}
					
					$new_all_chapters[$vol_id] = $new_volumn;
				}
			}
		}		
		
		return $new_all_chapters;
	}
	
	public function load_template( $name, $extend = false, $include = true ) {
		$check = true;
		if ( $extend ) {
			$name .= '-' . $extend;
		}

		$template = null;

		$child_template  = get_stylesheet_directory() . '/wp-manga/' . $name . '.php';
		$parent_template = get_template_directory() . '/wp-manga/' . $name . '.php';
		$plugin_template = apply_filters( 'wp-manga-template', WP_MANGA_CHAPTER_COIN_DIR . 'templates/' . $name . '.php', $name );
		
		if ( file_exists( $child_template ) ) {

			$template = $child_template;

		} else if ( file_exists( $parent_template ) ) {
			$template = $parent_template;
		} else if ( file_exists( $plugin_template ) ) {
			$template = $plugin_template;
		}

		if ( ! isset( $template ) ) {
			_doing_it_wrong( __FUNCTION__, sprintf( "<strong>%s</strong> does not exists in <code>%s</code>.", $name, $template ), '1.4.0' );

			return false;
		}

		if ( ! $include ) {
			return $template;
		}

		include $template;
	}
	
	/**
	 * Get chapter coin value configured for each chapter
	 *
	 * @return int
	 **/
	public function get_chapter_coin($chapter_id){
        $val = -1;
        
        if(isset($this->_chapter_coins[$chapter_id])){
            $val = $this->_chapter_coins[$chapter_id];
        } else {
            global $wpdb;
            $coins = array();
            $result = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}manga_chapter_coin WHERE chapter_id=%d", $chapter_id));

            
            if($result){
                $result = $result[0];
                    
                $val = $result->coin;
            }
            
            $this->_chapter_coins[$chapter_id] = $val;
        }
		
		return apply_filters('wp_manga_chapter_coin_get_chapter_coin', $val);
	}
	
	/**
	 * Return chapter coin value, taken into account default setting
	 *
	 * @return int
	 **/
	public function is_premium_chapter($chapter_id){
		$coin = $this->get_chapter_coin($chapter_id);
		if($coin != '' && $coin != -1){
			return $coin;
		}
		
		return $this->get_default_coin();
	}
}

require_once('admin/settings-page.php');
require_once('inc/helper.php');

$license_key = "text";
if ($license_key) {
	$wp_manga_chapter_coin = WP_MANGA_ADDON_CHAPTER_COIN::get_instance();
} else {
	add_action('admin_notices', 'wp_manga_chapter_coin_admin_notice__warning');
}

update_option(WP_MANGA_CHAPTER_COIN_LICENSE_KEY, $license_key);