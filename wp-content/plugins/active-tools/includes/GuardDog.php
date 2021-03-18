<?php


namespace ActiveTools;

use ActiveTools\Database\Models\IPDataModel;
use ActiveTools\Utility\IPTools;

class GuardDog {
    
    private bool $ip_guard_executed;
    
    private bool $option_disable_feeds;
    private bool $option_disable_rest_api;
    private bool $option_disable_xmlrpc;
    private bool $option_proxy_block_global;
    private bool $option_proxy_block_rest_api_global;
    // private bool $option_proxy_block_rest_api_posts;
    // private bool $option_proxy_block_posts;
    private array $option_rest_endpoints_whitelist;
    private string $option_rest_url_prefix;
    private array $option_countries_blacklisted;
    
    private bool $proxy_block_global_enabled;
    // private bool $proxy_block_posts_enabled;
    
    private ?string $visitor_ip;
    
    
    private bool $ip_whitelisted;
    private bool $ip_blacklisted;
    // private bool $post_blocked_due_to_proxy;
    
    private bool $doing_rest_request;
    
    protected static ?GuardDog $instance = null;
    
    public static function getInstance() {
        NULL === self::$instance and self::$instance = new self;
        return self::$instance;
    }
    
    private function __construct() {
        
        // Cancel if private/internal IP
        $this->visitor_ip = IPTools::get_visitor_ip();
        
        if ( IPTools::ip_is_private( $this->visitor_ip )
             || ( isset( $_GET['at_gd_gb_bp_k'] ) && $_GET['at_gd_gb_bp_k'] == \ActiveTools\GD_GLOBAL_BYPASS_KEY
             || ( defined( 'DOING_CRON' ) && DOING_CRON )
             ) ) {
            return;
        }
        
        $this->ip_guard_executed = false;
        
        $this->ip_whitelisted = false;
        $this->ip_blacklisted = false;
        // $this->post_blocked_due_to_proxy = false;
        $this->doing_rest_request = false;
        $this->option_disable_feeds = false;
        $this->option_disable_rest_api = false;
        $this->option_disable_xmlrpc = false;
        $this->proxy_block_global_enabled = false;
        $this->option_countries_blacklisted = [];
        
        // Country Blacklist
        $option_countries_blacklist = get_option( '_at_gd_co_bl', [] );
    
        if ( ! empty( $option_countries_blacklist ) ) {
            foreach( $option_countries_blacklist as $country ) {
                $this->option_countries_blacklisted[] = $country['value'];
            }
        }
        
        // IP Whitelist
        $option_rest_endpoints_whitelist = get_option( '_at_gd_ra_ep_wl', [] );
        
        
        $this->option_rest_endpoints_whitelist = [];
    
        foreach( $option_rest_endpoints_whitelist as $endpoint ) {
            if ( ! empty ( $endpoint['value'] ) ) {
                $this->option_rest_endpoints_whitelist[] = $endpoint['value'];
            }
        }
        
        $this->ip_whitelisted = $this->is_ip_whitelisted();
        
        // If the IP isn't whitelisted, continue with IP Guard
        if ( ! $this->ip_whitelisted ) {
    
            $this->abort_if_ip_blacklisted();
            
            // Begin IP Guard protection
            add_action( 'registered_taxonomy', array( $this, 'init_ip_guard' ), 1 );
            // Perform actions for feed & REST API protection
            $this->init_feed_guard();
        }
    }
    
    private function abort_if_ip_blacklisted() {
        if ( $this->ip_blacklisted = $this->is_ip_blacklisted() ) {
            $option_blacklisted_ip_message = get_option( '_at_gd_ip_bl_m', '<p>Oops! Your IP address has been blacklisted on our website.</p><p>If you are behind a VPN or Proxy, please disable it to continue accessing this website.</p><p>If you think this was in error, please contact us at ' . get_bloginfo( 'admin_email' ) . '</p>' );
            $this->die_html( 'Access Denied', $option_blacklisted_ip_message );
        }
    }
    public function init_ip_guard() {
        
        $this->option_proxy_block_global = ! empty( get_option( '_at_gd_pb_gb', false ) );
        // $this->option_proxy_block_posts = ! empty( get_option( '_at_gd_pb_pg', false ) );
        $this->option_proxy_block_rest_api_global = ! empty( get_option( '_at_gd_pb_gb_ra', false ) );
        // $this->option_proxy_block_rest_api_posts = ! empty( get_option( '_at_gd_pb_pg_ra', false ) );
    
        /* $this->proxy_block_posts_enabled = $this->option_proxy_block_posts;
    
        if ( $this->option_proxy_block_posts && $this->doing_rest_request) {
            $this->proxy_block_posts_enabled = $this->option_proxy_block_rest_api_posts;
        }
        */
    
        // Implement IP Guard for specific files
        $block_files = [
            'wp-links-opml.php',
            'xmlrpc.php',
            'wp-signup.php',
            'wp-trackback.php',
            'wp-activate.php',
            'wp-mail.php',
        ];
        
        $basename = basename($_SERVER['REQUEST_URI'], '?' . $_SERVER['QUERY_STRING']);
        
        if ( in_array( $basename, $block_files ) ) {
            $this->ip_guard();
            return;
        }
        
        // If admin page, do the IP Guard the earliest possible
        if( is_admin() ) {
            $this->ip_guard();
        } else {
            // Otherwise queue separately later for REST API and normal pages
            add_action( 'rest_api_init', array( $this, 'ip_guard' ), 1 ); // REST API
            add_action( 'wp', array( $this, 'ip_guard' ), 1 ); // Normal
            add_action( 'login_init', array( $this, 'ip_guard' ), 1 ); // Login page
        }
        
        // Maybe block post content from showing
        // add_filter( 'the_content', array( $this, 'maybe_block_post_proxy' ), PHP_INT_MAX );
        
    }
    
    private function init_feed_guard() {
    
        // Grab options
        $this->option_disable_feeds = ! empty( get_option( '_at_gd_fd_d', false ) );
        $this->option_rest_url_prefix = trim( get_option( '_at_gd_ra_ep_pf', '' ) );
        $this->option_disable_rest_api = ! empty( get_option( '_at_gd_ra_d', false ) );
        $this->option_disable_xmlrpc = ! empty( get_option( '_at_gd_xmlrpc_d', false ) );
    
        // Maybe disable Feeds
        if ( $this->option_disable_feeds ) {
            add_action( 'do_feed', array( $this, 'disable_feeds' ), 1 );
            add_action( 'do_feed_rdf', array( $this, 'disable_feeds' ), 1 );
            add_action( 'do_feed_rss', array( $this, 'disable_feeds' ), 1 );
            add_action( 'do_feed_rss2', array( $this, 'disable_feeds' ), 1 );
            add_action( 'do_feed_atom', array( $this, 'disable_feeds' ), 1 );
            add_action( 'do_feed_rss2_comments', array( $this, 'disable_feeds' ), 1 );
            add_action( 'do_feed_atom_comments', array( $this, 'disable_feeds' ), 1 );
        }
        
        // Maybe disable xmlrpc
        if ( $this->option_disable_xmlrpc ) {
            add_action( 'registered_taxonomy', array( $this, 'disable_xml_rpc' ) );
        }
        
        // Maybe change REST API endpoint prefix
        add_filter( 'rest_url_prefix', array( $this, 'set_rest_api_url_prefix' ) );
    
        // Maybe disable REST API entirely except for specific endpoints
        if ( $this->option_disable_rest_api ) {
            add_filter( 'rest_endpoints', array( $this, 'filter_rest_api_endpoints' ), PHP_INT_MAX, 1 );
            
            // Remove broadcast of REST API links in HTML <head>
            remove_action( 'wp_head', 'rest_output_link_wp_head', 10 );
            remove_action( 'xmlrpc_rsd_apis', 'rest_output_rsd' );
            remove_action( 'template_redirect', 'rest_output_link_header', 11 );
        }
    
        // Remove Wordpress version number
        add_filter( 'the_generator', function( $value ){return '';}, PHP_INT_MAX );
    }
    
    public function disable_xml_rpc() {
        
        if ( defined('XMLRPC_REQUEST') && XMLRPC_REQUEST ) {
            global $wp_query;
            $wp_query->set_404();
            status_header(404);
            header( 'Content-Type: text/html; charset=utf-8');
            exit();
        }
        
        add_filter('xmlrpc_enabled', '__return_false', PHP_INT_MAX );
        add_filter( 'xmlrpc_methods', function( $methods ) {
            unset( $methods['pingback.ping'] );
            unset( $methods['pingback.extensions.getPingbacks'] );
            return $methods;
        }, PHP_INT_MAX );
        add_action( 'wp', function(){header_remove( 'X-Pingback' );}, PHP_INT_MAX );
        remove_action ('wp_head', 'rsd_link');
    }
    
    public function ip_guard() {
    
        // Run IP Guard only once
        if( $this->ip_guard_executed ) {
            return;
        }
        
        $this->ip_guard_executed = true;
    
        $this->doing_rest_request = defined( '\REST_REQUEST' ) && \REST_REQUEST;
    
        $this->proxy_block_global_enabled = $this->option_proxy_block_global;
    
        if ( $this->option_proxy_block_global && $this->doing_rest_request) {
            $this->proxy_block_global_enabled = $this->option_proxy_block_rest_api_global;
        }
        
        // Do a IP validation check and rejection here
        if ( ! IPTools::ip_is_valid( $this->visitor_ip ) ) {
            $this->do_abort_proxy_blocked();
        }
        
        $db = new IPDataModel();
    
        $ip_data = $db->get_ip( $this->visitor_ip );
        
        $geoip_data_expired = empty( $ip_data ) || ( new \DateTime() )->modify( '-7 days') > $ip_data['geo_last_updated'];
        $proxy_data_expired = empty( $ip_data ) || ( new \DateTime() )->modify( '-4 days') > $ip_data['proxy_last_updated'];
        
        // Process GEO IP data only if there's a list of blacklisted countries and expired
        if( ! empty( $this->option_countries_blacklisted ) && $geoip_data_expired ) {
            
            $geoip_data = IPTools::get_ip_geo_data( $this->visitor_ip );
    
            $ip_data['country'] = ! empty ( $geoip_data['country'] ) ? $geoip_data['country']['isoCode'] : '--';
            $ip_data['region'] = ! empty ( $geoip_data['region'] ) ? $geoip_data['region']['isoCode'] : '--';
            $ip_data['city'] = ! empty ( $geoip_data['city'] ) ? $geoip_data['city']['name'] : '--';
    
            // Block by country now
            if ( $this->is_country_blacklisted( $ip_data['country'] ) ) {
                $db->update_ip( $this->visitor_ip, $ip_data['country'], $ip_data['region'], $ip_data['city'], null );
                $this->do_abort_country_blacklisted( $this->visitor_ip, $ip_data['country'] );
            }
            
        } else {
            
            if ( ! empty( $this->option_countries_blacklisted ) ) {
                if ( $this->is_country_blacklisted( $ip_data['country'] ) ) {
                    $this->do_abort_country_blacklisted( $this->visitor_ip, $ip_data['country'] );
                }
            }
            
            $ip_data['country'] = null;
            $ip_data['region'] = null;
            $ip_data['city'] = null;
        }
        
        // Process Proxy data only if enabled and expired
        if( $this->proxy_block_global_enabled ) {
            
            if ( $proxy_data_expired ) {
                // Get proxy service
                $proxy_service = get_option( '_at_gd_pb_ds', 'getipintel_free' );
                $subdomain     = 'check';
    
                if ( $proxy_service == 'getipintel_custom' ) {
                    $subdomain = get_option( '_at_gd_pb_gb_ds_gipi_c' );
                }
    
                if ( empty( $subdomain ) ) {
                    $db->update_ip( $this->visitor_ip, $ip_data['country'], $ip_data['region'], $ip_data['city'], null );
                    return;
                }
    
                $ip_data['proxy_level'] = IPTools::get_getipintel_proxy_level( $this->visitor_ip, $subdomain, 'b' );
    
                $db->update_ip( $this->visitor_ip, $ip_data['country'], $ip_data['region'], $ip_data['city'], $ip_data['proxy_level'] );
            }
            
            $sitewide_max_proxy_level = floatval( get_option( '_at_gd_pb_gb_rf', 1.0 ) );
            
            if ( floatval( $ip_data['proxy_level'] ) > $sitewide_max_proxy_level ) {
                $this->do_abort_proxy_blocked( $this->visitor_ip );
            }
        }
    }
    
    private function do_abort_proxy_blocked( ?string $ip = null ) {
    
        $response_type = get_option( '_at_gd_pb_gb_rt', 'empty' );
        
        switch( $response_type ) {
            case 'page':
                $this->do_abort_proxy_blocked_page();
                break;
            case 'template_404':
                $this->do_abort_proxy_blocked_template_404();
                break;
            case 'message':
                $this->do_abort_proxy_blocked_message( $ip );
                break;
            case 'empty_404':
                $this->abort_empty( 404 );
                break;
            default:
                $this->abort_empty( 403 );
                break;
        }
        
    }
    
    private function do_abort_proxy_blocked_page() {
        
        global $post;
        
        $page_id = get_option( '_at_gd_pb_gb_p', 0 );
    
        if ( empty( $page_id ) ) {
            $this->do_abort_proxy_blocked_template_404();
        }
        
        if ( ! empty( $post ) ) {
            if ( $post->ID == $page_id ) {
                return;
            }
        }
        
        wp_redirect( get_permalink( $page_id ) );
        
        exit;
    }
    private function do_abort_proxy_blocked_template_404() {
        global $wp_query;
        $wp_query->set_404();
        status_header(404);
        header( 'Content-Type: text/html; charset=utf-8');
        get_template_part( 404 );
        exit();
        
    }
    private function do_abort_proxy_blocked_message( ?string $ip = null ) {
        $option_block_proxy_message      = get_option( '_at_gd_pb_gb_m', '<p>Oops! Your IP address has been flagged as suspicious.</p><p>If you are behind a VPN or Proxy, please disable it to continue accessing this website.</p><p>If you think this was in error, please contact us at ' . get_bloginfo( 'admin_email' ) . '</p>' );
        
        if ( ! empty( $ip ) ) {
            $option_block_proxy_message .= '<p>Detected IP: ' . $ip . '</p>';
        }
        
        $this->die_html( 'Access Denied', $option_block_proxy_message );
    }
    private function abort_empty( $error_code ) {
        
        if ( ! in_array( $error_code, [403, 404]) ) {
            $error_code = 403;
        }
        
        global $wp_query;
        $wp_query->{"set_{$error_code}"}();
        status_header( $error_code );
        exit();
    }
    
    private function is_country_blacklisted( ?string $country_code ) {
        return ! empty( $country_code ) && in_array( $country_code, $this->option_countries_blacklisted );
    }
    
    private function do_abort_country_blacklisted( ?string $ip = null, ?string $country = null ) {
        $option_block_country_message = get_option( '_at_gd_co_bl_m', '<p>Oops! Your country is being blocked by our website.</p><p>If you are behind a VPN or Proxy, please disable it to continue accessing this website.</p><p>If you think this was in error, please contact us at ' . get_bloginfo( 'admin_email' ) . '</p>' );
    
        $option_block_country_message .= '<p>';
        if ( ! empty( $ip ) ) {
            $option_block_country_message .= 'Detected IP: ' . $ip;
        }
        
        if ( ! empty( $country ) ) {
            $option_block_country_message .= ' Country Code: ' . $country;
        }
        $option_block_country_message .= '</p>';
        
        $this->die_html( 'Access Denied', $option_block_country_message );
    }
    private function is_ip_whitelisted( ?string $ip = null ) {
    
        if ( $ip == null ) {
            $ip = $this->visitor_ip;
        }
        
        $whitelisted_ips = get_option( '_at_gd_ip_wl', [] );
    
        foreach( $whitelisted_ips as $whitelisted_ip ) {
            if ( $ip == $whitelisted_ip['ip'][0]['value'] ) {
                return true;
            }
        }
        
        return false;
    }
    
    private function is_ip_blacklisted( ?string $ip = null ) {
    
        if ( $ip == null ) {
            $ip = $this->visitor_ip;
        }
    
        $blacklisted_ips = get_option( '_at_gd_ip_bl', [] );
    
        foreach( $blacklisted_ips as $blacklisted_ip ) {
            if ( $ip == $blacklisted_ip['ip'][0]['value'] ) {
                return true;
            }
        }
        
        return false;
    }
    
    /* public function maybe_block_post_proxy( $content ) {
        
        global $post;
        
        if ( ! $this->option_proxy_block_posts
             || empty( $post )
             || ! $this->post_blocked_due_to_proxy ) {
            return $content;
        }
        
        $message = get_option( '_at_gd_pb_pg_m', '<p>Sorry, due to malicious users and for legal reasons, we don\'t allow readership behind VPNs or Proxies.</p><p>If you are behind a VPN or Proxy, please disable it to continue reading this material.</p><p>If you think this was in error, please contact us at ' . get_bloginfo( 'admin_email' ) . '</p>' );
    
        remove_filter( 'the_content', array( $this, 'maybe_block_post_proxy' ), PHP_INT_MAX );
        remove_filter( 'the_content', array( ContentProtection::getInstance(), 'filter_content' ), PHP_INT_MAX - 1 );
    
        $message = apply_filters( 'the_content', $message );
        
        add_filter( 'the_content', array( $this, 'maybe_block_post_proxy' ), PHP_INT_MAX );
        add_filter( 'the_content', array( ContentProtection::getInstance(), 'filter_content' ), PHP_INT_MAX - 1 );
        
        return $message;
        
    } */
    
    private function die_html( $title, $message ) {
        header( 'Content-Type: text/html; charset=utf-8');
        ?>
        <html>
            <head>
                <title><?php esc_attr_e( $title ); ?></title>
            </head>
            <body>
                <?php echo apply_filters( 'the_content', $message ); ?>
            </body>
        </html>
        <?php
        die();
    }
    
    public function disable_feeds() {
        global $wp_query;
        $wp_query->set_404();
        status_header(404);
        header( 'Content-Type: text/html; charset=utf-8');
        get_template_part( 404 );
        exit();
        // wp_die( __( 'No feed available, please visit <a href="'. esc_url( home_url( '/' ) ) .'">Active Translations</a> normally to view content.' ) );
    }
    
    public function set_rest_api_url_prefix( $prefix ) {
        
        if ( ! empty( $this->option_rest_url_prefix ) ) {
            $prefix = $this->option_rest_url_prefix;
        }
        
        return $prefix;
    }
    
    public function filter_rest_api_endpoints( $endpoints ) {
        
        // If Bypass Key, cancel filter
        if( isset( $_GET['at_gd_ra_bp_k'] ) && $_GET['at_gd_ra_bp_k'] == \ActiveTools\GD_REST_API_BYPASS_KEY ) {
            return $endpoints;
        }
        
        // If the whitelist is empty, disable all
        if ( empty( $this->option_rest_endpoints_whitelist ) ) {
            return [];
        }
        
        // Loop through the endpoints currently in Wordpress
        foreach (  $endpoints as $key => $value ) {
            
            $keep = false;
            
            // If a match (or partial match) to a whitelist entry, set to keep
            foreach ( $this->option_rest_endpoints_whitelist as $whitelisted_endpoint ) {
                
                if ( stripos( $key, $whitelisted_endpoint ) !== false ) {
                    $keep = true;
                    break;
                }
            }
            
            // If no match was found to keep, unset it
            if ( ! $keep ) {
                unset( $endpoints[ $key ] );
            }
        }
        
        return $endpoints;
    }
}
