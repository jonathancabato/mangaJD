<?php

class WP_MANGA_ADDON_CHAPTER_COIN_BACKEND {
	private static $instance;

	public static function get_instance() {
		if ( null == self::$instance ) {
			self::$instance = new WP_MANGA_ADDON_CHAPTER_COIN_BACKEND();
		}

		return self::$instance;
	}
	
	public static $BUY_CHAPTER_REF = 'buy_chapter';
	
	private function __construct() {
		add_action( 'admin_init', array($this, 'admin_init' ));
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_script' ) );
		add_action('wp_ajax_wp_manga_chapter_coin_save', array($this, 'admin_save_chapter_coin'));
		
		add_action('wp_ajax_wp_manga_admin_chapter_modal_get_coin', array($this, 'admin_chapter_modal_get_coin'));
		add_filter('madara_ajax_next_page_content', array($this, 'madara_ajax_next_page_content'));
		
		add_filter('madara_chapter_content_li_html', array($this, 'admin_chapter_li_html'), 10, 3);
		
		add_action('wp_ajax_nopriv_wp_manga_buy_chapter', array($this, 'ajax_buy_chapter'));
		add_action('wp_ajax_wp_manga_buy_chapter', array($this, 'ajax_buy_chapter'));
		
		// support Member Upload Pro plugin
		add_action('muupro_upload_chapter_fields', array($this, 'muupro_upload_chapter_fields'));
		add_action('muupro_edit_chapter_modal_fields', array($this, 'muupro_edit_chapter_modal_fields'));
		add_action('wp_manga_upload_completed', array($this, 'muupro_after_add_chapter'), 20, 5);
		add_filter('muupro_get_chapter_info', array($this, 'muupro_get_chapter_info'), 10, 2);
		add_action('muupro_after_edit_chapter', array($this, 'muupro_after_edit_chapter'), 10, 2);
		
		add_action( 'after_madara_settings_page', array( $this, 'admin_settings_fields') );
		add_action( 'wp_manga_setting_save', array( $this, 'admin_settings_save') );
		
		add_filter('mycred_all_references', array($this,'mycred_all_references'));
		
		
	}
	
	/**
	 * Get revenue of a manga 
	 **/
	public function get_revenue($manga_id, $from = '', $to = ''){
		if($from == ''){
			$from = '1970-01-01 00:00:00';
		} else {
			$from .= ' 00:00:00';
		}
		
		if($to == ''){
			$to = date('Y-m-d H:i:s');
		} else {
			$to .= ' 23:59:59';
		}
		
		$args = array(
			'time' => array(
				'dates'   => array($from, $to),
				'compare' => 'BETWEEN'
			),
			'ref' => self::$BUY_CHAPTER_REF,
			'data' => '%s:8:"manga_id";s:' . strlen($manga_id) . ':"'. $manga_id .'"%',
			'number' => -1,
			'cache_results' => false
		);
		
		$logs  = new myCRED_Query_Log( $args );
		
		$total = 0;
		foreach($logs->results as $log){
			$total += abs($log->creds);
		}
		
		return $total;
	}
	
	function mycred_all_references($refs){
		$refs[ self::$BUY_CHAPTER_REF ] = esc_html__('Buy Chapter', MANGA_CHAPTER_COIN_TEXT_DOMAIN);
		
		return $refs;
	}
	
	function admin_settings_save(){
		if( isset( $_POST['wp_manga_chapter_selling'] ) ){
			$wp_manga_settings = get_option( 'wp_manga_settings', array() );
			
			$wp_manga_settings['wp_manga_chapter_selling'] = array(
				'default_coin' => isset( $_POST['wp_manga_chapter_selling']['default_coin'] ) ? $_POST['wp_manga_chapter_selling']['default_coin'] : 0,
				'free_word' => isset( $_POST['wp_manga_chapter_selling']['free_word'] ) ? $_POST['wp_manga_chapter_selling']['free_word'] : esc_html__('Free', MANGA_CHAPTER_COIN_TEXT_DOMAIN),
				'free_color' => isset( $_POST['wp_manga_chapter_selling']['free_color'] ) ? $_POST['wp_manga_chapter_selling']['free_color'] : '#999999',
				'free_background' => isset( $_POST['wp_manga_chapter_selling']['free_background'] ) ? $_POST['wp_manga_chapter_selling']['free_background'] : '#DCDCDC',
				'unlock_color' => isset($_POST['wp_manga_chapter_selling']['unlock_color']) ? $_POST['wp_manga_chapter_selling']['unlock_color'] : '#999999',
				'unlock_background' => isset($_POST['wp_manga_chapter_selling']['unlock_background']) ? $_POST['wp_manga_chapter_selling']['unlock_background'] : '#DCDCDC',
				'ranking_background' => isset($_POST['wp_manga_chapter_selling']['ranking_background']) ? $_POST['wp_manga_chapter_selling']['ranking_background'] : 'rgba(255, 248, 26, 0.6)',
				'ranking_text_color' => isset($_POST['wp_manga_chapter_selling']['ranking_text_color']) ? $_POST['wp_manga_chapter_selling']['ranking_text_color'] : '#333333',
				'lock_color' => isset($_POST['wp_manga_chapter_selling']['lock_color']) ? $_POST['wp_manga_chapter_selling']['lock_color'] : '#ffffff',
				'lock_background' => isset($_POST['wp_manga_chapter_selling']['lock_background']) ? $_POST['wp_manga_chapter_selling']['lock_background'] : '#fe6a10',
				'muupro_allow_creator_edit'  => isset($_POST['wp_manga_chapter_selling']['muupro_allow_creator_edit']) ? $_POST['wp_manga_chapter_selling']['muupro_allow_creator_edit'] : 'no'
			);
			
			$resp = update_option( 'wp_manga_settings', $wp_manga_settings );
		}
	}
	
	/**
	 * append chapter coin value in the chapter listing in admin
	 *
	 * $output - string - current HTML output (without closing tag </li>)
	 * $chapter_id - int - Chapter ID
	 * $c - object - object Chapter
	 **/
	function admin_chapter_li_html($output, $chapter_id, $c){
		$manager = WP_MANGA_ADDON_CHAPTER_COIN::get_instance();
		$coin = $manager->get_chapter_coin($chapter_id);
		
		if($coin && $coin != -1){
			$output .= '<span class="coin"><i class="fas fa-coins"></i>' . $coin . '</span>';
		}
		
		return $output;
	}
	
	/**
     * ajax buy chapter from front-end
	 **/
	function ajax_buy_chapter(){
		$chapter_id = isset($_POST['chapter']) ? intval($_POST['chapter']) : 0;
		$nonce = isset($_POST['nonce']) ? esc_html($_POST['nonce']) : '';
		$user_id = get_current_user_id();
		
		global $wp_manga_chapter;
		$chapter = $wp_manga_chapter->get_chapter_by_id( null, $chapter_id );
				
		if($user_id && $chapter){
			if ( wp_verify_nonce($nonce, 'wp-manga-coin-nonce') ) {
				// check current coin
				if(function_exists('mycred_get_users_cred')){
					$coin = mycred_get_users_cred($user_id);
				} else {
					$coin = 0;
				}
				
				// get chapter coin
				$manager = WP_MANGA_ADDON_CHAPTER_COIN::get_instance();
				$chapter_coin = $manager->is_premium_chapter($chapter_id);
				
				// deduct coin
				if($chapter_coin > 0 && $coin >= $chapter_coin){
					// use later
					$ref_id = $chapter_id;
					$data = array(
								'chapter_id' => $chapter_id, 
								'manga_id' => $chapter['post_id'],
								'author_id' => get_post_field( 'post_author', $chapter['post_id'] ));
					
					mycred_subtract(self::$BUY_CHAPTER_REF, $user_id, $chapter_coin, sprintf(esc_html__('Buy Chapter: "%s" of "%s"', MANGA_CHAPTER_COIN_TEXT_DOMAIN), $chapter['chapter_name'], get_the_title($chapter['post_id'])), $ref_id, $data);
					
					// clear user bought chapters cache
					wp_cache_delete($user_id . '_bought_chapters', 'bought_chapters');
				} else {
					wp_send_json_error(array('status' => false, 
										'message' => esc_html__("You do not have enough coin to buy this chapter", MANGA_CHAPTER_COIN_TEXT_DOMAIN), 
										'nonce' => wp_create_nonce('wp-manga-coin-nonce')));
				}				
				
				// return url
				global $wp_manga_functions;
				$url = $wp_manga_functions->build_chapter_url($chapter['post_id'], $chapter);
				
				wp_send_json_success(array('status' => true, 
											'message' => esc_html__('Thank you. You are now redirected to the chapter', MANGA_CHAPTER_COIN_TEXT_DOMAIN), 
											'url' => $url,
											'nonce' => wp_create_nonce('wp-manga-coin-nonce')));
			}
		}
		
		wp_send_json_error(array('status' => false, 
										'message' => esc_html__('Invalid request. Please try again', MANGA_CHAPTER_COIN_TEXT_DOMAIN), 
										'nonce' => wp_create_nonce('wp-manga-coin-nonce')));
	}
	
	// block chapter content calling via Ajax
	function madara_ajax_next_page_content( $output ){
		$this_post = get_post( $_GET['postID'] );
		$post = $this_post;
		$post_id = $_GET['postID'];
		$chapter_slug = $_GET['chapter'];
		
		global $wp_manga_chapter;
		$reading_chapter = $wp_manga_chapter->get_chapter_by_slug( $post_id, $chapter_slug );
		
		global $wp_manga;
		
		$wp_manga_functions = madara_get_global_wp_manga_functions();
		
		if ( $reading_chapter ) {
			$manager = WP_MANGA_ADDON_CHAPTER_COIN::get_instance();
			$coins = $manager->get_chapter_coin($reading_chapter['chapter_id']);
		
			if(!empty($coins) && is_array($coins)){	
				$coin_login = array('guest');
				foreach($coins as $coin){
					if($coin['type'] = 'login'){
						$coin_login = $coin['value'];
						if(!is_array($coin_login)) $coin_login = array($coin_login);
						break;
					}
				}
				
				$user = wp_get_current_user();
				
				if(!in_array('guest', $coin_login) && count(array_intersect($coin_login, (array) $user->roles )) == 0){
					if(count($coin_login) == 1){
						$output['content'] = '<div class="reading-content"><div class="content-blocked login-required">' . wp_kses_post(sprintf(__('You need to be <b>%s</b> to read this chapter',MANGA_CHAPTER_COIN_TEXT_DOMAIN), $coin_login[0])) . '</div></div>';
					} else {
						$output['content'] = '<div class="reading-content"><div class="content-blocked login-required">' . wp_kses_post(sprintf(__('You need to be either one of the following roles: <b>%s</b> to read this chapter',MANGA_CHAPTER_COIN_TEXT_DOMAIN), implode(', ', $coin_login))) . '</div></div>';
					}
				}
			}
		}
		
		return $output;
	}
	
	function admin_settings_fields(){
		$manager = WP_MANGA_ADDON_CHAPTER_COIN::get_instance();
		$manager->load_template('admin', 'settings');
	}
	
	
	/**
	 * Ajax - get chapter coins
	 **/
	function admin_chapter_modal_get_coin(){
		$chapter_id = isset($_POST['chapter_id']) ? $_POST['chapter_id'] : 0;
		if($chapter_id){
			$manager = WP_MANGA_ADDON_CHAPTER_COIN::get_instance();
			$coin = $manager->get_chapter_coin($chapter_id);
			
			echo $coin;
		}
		
		die(0);
	}
	
	function admin_init() {
		add_action('madara_chapter_modal_after_chapter_meta', array($this, 'admin_edit_chapter_modal_extra_settings'), 10, 2);
	}
	
	function admin_save_chapter_coin(){
		$chapter_id = isset($_POST['chapter_id']) ? $_POST['chapter_id'] : 0;
		$manga_id = isset($_POST['manga_id']) ? $_POST['manga_id'] : 0;
		$coin = isset($_POST['chapter_coin']) ? intval($_POST['chapter_coin']) : -1;
		
		if($chapter_id){
			$this->update_coin($chapter_id, $coin, $manga_id);
		}
		
		wp_send_json_success();
		
		die(0);
	}
	
	function admin_enqueue_script(){
		global $pagenow;
		
		wp_enqueue_script( 'wp_manga_chapter_coin_js', WP_MANGA_CHAPTER_COIN_URI . 'assets/js/admin.js', array( 'jquery' ), '', true );
		
		wp_enqueue_style( 'wp_manga_chapter_coin_css', WP_MANGA_CHAPTER_COIN_URI . 'admin/assets/admin.css' );
	}
	
	/**
	 * Update or save chapter coins
	 * $coin - int (-1 or null: use Default Global Value; 0: Free; > 0: Chapter Coin)
	 *
	 * $manga_id is required when adding new (chapter_id not exist)
	 *
	 **/
	private function update_coin($chapter_id, $coin, $manga_id = 0){
		global $wpdb;
		$found = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}manga_chapter_coin WHERE chapter_id=%d", $chapter_id));
		if($found){
			$wpdb->update($wpdb->prefix . "manga_chapter_coin",array("coin" => $coin), array("chapter_id" => $chapter_id), array('%d') );
		} else {
			$wpdb->insert($wpdb->prefix . "manga_chapter_coin",array("chapter_id" => $chapter_id, "coin" => $coin, "manga_id" => $manga_id), array('%d','%d', '%d'));
		}
	}
	
	function admin_edit_chapter_modal_extra_settings($chapter_type, $post_id){
			?>
		<div class="wp-manga-modal-coin">
			<strong><?php esc_html_e( 'Coin Value: ', MANGA_CHAPTER_COIN_TEXT_DOMAIN ); ?></strong>

			<input type="number" name="chapter-coin"/>
			<span class="desc"><?php esc_html_e('leave empty or -1 if this chapter uses global setting value');?></span>
		</div>
		<?php
	}
	
	function muupro_after_edit_chapter( $data, $where ){
		$settings = wp_manga_chapter_coin_get_settings();
		if($settings['muupro_allow_creator_edit'] == 'yes' && wp_manga_chapter_coin_is_allow_edit($where['chapter_id'])){
			if(isset($_POST['chapter-coin'])){
				$update_coin =  intval($_POST['chapter-coin']);
				if($update_coin >= 0){
					$this->update_coin($where['chapter_id'], $update_coin, $where['post_id']);
				}
			}
		}
	}
	
	// get chapter info to fill in the modal form
	function muupro_get_chapter_info( $data, $chapter ) {
		$settings = wp_manga_chapter_coin_get_settings();
		if($settings['muupro_allow_creator_edit'] != 'yes'){
			$data['allow_set_coin'] = false;
		} else {
			$data['allow_set_coin'] = true;
			
			$manager = WP_MANGA_ADDON_CHAPTER_COIN::get_instance();
			$data['chapter_coin'] = $manager->get_chapter_coin($data['id']);
		}
		
		
		return $data;
	}
	
	// support plugin WP Manga Member Upload PRO
	function muupro_edit_chapter_modal_fields(){
		$settings = wp_manga_chapter_coin_get_settings();
		if($settings['muupro_allow_creator_edit'] == 'yes'){
		?>
		<div id="madara-chapter-coin-row" class="form-group row">
			<label for="madara-chapter-coin" class="col-md-3">
				<?php esc_html_e('Chapter Coin', MANGA_CHAPTER_COIN_TEXT_DOMAIN); ?>
			</label>
			<div class="col-md-9">
				<input type="number" class="form-control" id="chapter-coin" name="chapter-coin" />
				<p style="font-style:italic"><?php esc_html_e('Set coin value for this chapter. If it is free, set 0', MANGA_CHAPTER_COIN_TEXT_DOMAIN);?></p>
			</div>
		</div>
		<?php
		}
	}
	
	function muupro_upload_chapter_fields(){
		$settings = wp_manga_chapter_coin_get_settings();
		if($settings['muupro_allow_creator_edit'] == 'yes'){
		?>
		<div class="form-group row title-field">
			<label class="col-md-3">
				<?php esc_html_e('Chapter Coin', MANGA_CHAPTER_COIN_TEXT_DOMAIN); ?>
			</label>
			<div class="col-md-9">
				<input type="number" class="form-control" id="chapter-coin" name="chapter-coin" />
				<p style="font-style:italic"><?php esc_html_e('Set coin value for this chapter. If it is free, set 0', MANGA_CHAPTER_COIN_TEXT_DOMAIN);?></p>
			</div>
		</div>
		<?php
		}
	}
	
	/**
	 * Save chapter coin when uploading chapter
	 **/
	function muupro_after_add_chapter($chapter_id, $post_id, $extract, $extract_uri, $storage){
		$settings = wp_manga_chapter_coin_get_settings();
		if($settings['muupro_allow_creator_edit'] == 'yes' && wp_manga_chapter_coin_is_allow_edit($chapter_id)){
			if(isset($_POST['chapter-coin'])){
				$coin = intval($_POST['chapter-coin']);
				$this->update_coin($chapter_id, $coin, $post_id);
			}
		}
	}
}