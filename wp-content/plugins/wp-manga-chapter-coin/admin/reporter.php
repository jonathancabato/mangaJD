<?php

class WP_MANGA_ADDON_CHAPTER_COIN_REPORT {
	private static $instance;

	public static function get_instance() {
		if ( null == self::$instance ) {
			self::$instance = new WP_MANGA_ADDON_CHAPTER_COIN_REPORT();
		}

		return self::$instance;
	}
	
	private function __construct() {
		add_action( 'admin_menu', array($this, 'register_admin_page') );
	}
	
	function register_admin_page(){
		add_menu_page( esc_html__('Coins Usage Report', MANGA_CHAPTER_COIN_TEXT_DOMAIN), esc_html__('Manga Coins Report', MANGA_CHAPTER_COIN_TEXT_DOMAIN), 'manage_options', 'wp-manga-chapter-coin/revenue', array($this, 'coin_report'), plugins_url('wp-manga-chapter-coin/admin/assets/icon.png'), 11 );
	}
	
	function coin_report(){
		require_once dirname(__FILE__) . '/report_table.php'; 
		?>
		<div class="wrap">
			<h1><?php echo esc_html__('Coins Usage Report', MANGA_CHAPTER_COIN_TEXT_DOMAIN);?></h1>
			<?php
			$table = new WP_MANGA_COIN_REPORT_TABLE();
			$table->prepare_items(); 
			$table->display();
			?>
		</div>
		<?php
	}
}