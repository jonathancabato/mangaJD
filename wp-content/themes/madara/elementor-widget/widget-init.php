<?php

class My_Elementor_Widgets {

	protected static $instance = null;

	public static function get_instance() {
		if ( ! isset( static::$instance ) ) {
			static::$instance = new static;
		}
		return static::$instance;
	}
	

	protected function __construct() {
   
        require_once( get_theme_file_path( '/elementor-widget/widgets/widget-one.php' ) );
        // require_once( get_theme_file_path( '/elementor-widgets/widgets/widget-testimonials.php' ) );
        // require_once( get_theme_file_path( '/elementor-widgets/widgets/widget-products.php' ) );
        // require_once( get_theme_file_path( '/elementor-widgets/widgets/widget-store.php' ) );
        // require_once( get_theme_file_path( '/elementor-widgets/widgets/widget-about-us.php' ) );
        // require_once( get_theme_file_path( '/elementor-widgets/widgets/widget-ads.php' ) );
        // require_once( get_theme_file_path( '/elementor-widgets/widgets/widget-section.php' ) );
        // require_once( get_theme_file_path( '/elementor-widgets/widgets/widget-bxslider.php' ) );

       	add_action( 'elementor/widgets/widgets_registered', [ $this, 'register_widgets' ] );
		add_action( 'elementor/elements/categories_registered', [$this, 'add_elementor_widget_categories'], 1);
	}

	public function register_widgets() {
 
   
        \Elementor\Plugin::instance()->widgets_manager->register_widget_type( new \Elementor\custom_query() );
        // \Elementor\Plugin::instance()->widgets_manager->register_widget_type( new \Elementor\custom_testimonial() );
        // \Elementor\Plugin::instance()->widgets_manager->register_widget_type( new \Elementor\custom_products() );
        // \Elementor\Plugin::instance()->widgets_manager->register_widget_type( new \Elementor\custom_store() );
        // \Elementor\Plugin::instance()->widgets_manager->register_widget_type( new \Elementor\custom_aboutus() );
        // \Elementor\Plugin::instance()->widgets_manager->register_widget_type( new \Elementor\custom_ads() );
        // \Elementor\Plugin::instance()->widgets_manager->register_widget_type( new \Elementor\custom_section() );
        // \Elementor\Plugin::instance()->widgets_manager->register_widget_type( new \Elementor\custom_bxslider() );
       
        
        
    }
	
   
	public function add_elementor_widget_categories( $elements_manager ) {
                 //add our categories
            $category_prefix = 'mad-';
		$elements_manager->add_category(
			$category_prefix.'category',
			[
				'title' => __( 'Custom Widgets', 'elementor' ),
				'icon' => 'fa fa-plug',
			]
                );
                $reorder_cats = function() use($category_prefix){
                        uksort($this->categories, function($keyOne, $keyTwo) use($category_prefix){
                            if(substr($keyOne, 0, 4) == $category_prefix){
                                return -1;
                            }
                            if(substr($keyTwo, 0, 4) == $category_prefix){
                                return 1;
                            }
                            return 0;
                        });
        
                    };
                    $reorder_cats->call($elements_manager);
	
        }
	
}

add_action( 'init', 'my_elementor_init' );
function my_elementor_init() {
	My_Elementor_Widgets::get_instance();
}