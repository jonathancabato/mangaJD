<?php declare(strict_types=1);

namespace ActiveTools\Utility;


class IPTools {
    
    
    /**
     * Gets IP address
     *
     * @param $deep_detect - Set to true if server is under a proxy and configured to forward the client IP
     *
     * @return mixed
     */
    public static function get_visitor_ip( bool $deep_detect = false ) {
        
        $ip = $_SERVER["REMOTE_ADDR"];
        
        if ( $deep_detect ) {
            if (filter_var(@$_SERVER['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP)) {
                $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
            } else {
                if ( filter_var( @$_SERVER['HTTP_CLIENT_IP'], FILTER_VALIDATE_IP ) ) {
                    $ip = $_SERVER['HTTP_CLIENT_IP'];
                }
            }
        }
        
        return $ip;
    }
    
    public static function get_visitor_ip_proxy_level( bool $full_lookup = false ) {
        return self::get_ip_proxy_level( self::get_visitor_ip(), $full_lookup );
    }
    
    public static function visitor_ip_is_proxy( float $min_value = 0.6, bool $full_lookup = false ) {
        return self::get_visitor_ip_proxy_level( $full_lookup ) >= $min_value;
    }
    
    public static function ip_is_valid( $ip ) {
        return !!filter_var( $ip, FILTER_VALIDATE_IP );
    }
    public static function ip_is_ipv4( $ip ) {
        return !!filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 );
    }
    public static function ip_is_ipv6( $ip ) {
        return !!filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6 );
    }
    public static function ip_is_private( $ip ) {
        
        if ( $ip == 'localhost' || $ip == null ) {
            return true;
        }
        
        if ( ! self::ip_is_valid( $ip ) ) {
            return false;
        }
        
        return ! filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE );
    }
    
    /**
     * Checks https://getipintel.net/ API for level on IP
     *
     * @param string $ip
     * @param bool $full_lookup
     *
     * @return mixed
     */
    public static function get_getipintel_proxy_level( string $ip, string $subdomain = 'check', ?string $flags = 'f' ) {

        if ( self::ip_is_private( $ip ) ) {
            return 0;
        }
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://{$subdomain}.getipintel.net/check.php?ip={$ip}&contact=" . get_bloginfo('admin_email') . ( $flags != null ? "&flags={$flags}" : '' ) );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $output = max( 0, min( 1, floatVal( curl_exec($ch) ) ) );

        curl_close($ch);
        
        return $output;
    }
    
    /**
     * Checks https://api.get-geo-ip.info/ for geoip data on IP
     *
     * @param string $ip
     * @param bool $full_lookup
     *
     * @return mixed
     */
    public static function get_ip_geo_data( string $ip, bool $full_lookup = false ) {
        
        if ( self::ip_is_private( $ip ) ) {
            return 0;
        }
        
        $result = file_get_contents( 'http://api.get-geo-ip.info/' . $ip );
        
        if ( empty( $result ) ) {
            return [];
        }
        
        return json_decode( $result, true );
    }
}
