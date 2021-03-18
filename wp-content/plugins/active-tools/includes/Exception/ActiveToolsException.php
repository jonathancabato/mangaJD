<?php

namespace ActiveTools\Exception;

use ActiveTools\Utility\Logger;

class ActiveToolsException extends \Exception {
    
    public static array $errors = array();
    public static $throw_errors = WP_DEBUG;
    
    /**
     * Throw an exception when WP_DEBUG is enabled or throwing is forced
     * and show a friendly admin notice otherwise
     *
     * @param string $message
     * @param int $code (optional)
     *
     * @param bool $force_throw Force an exception to be thrown whether WP_DEBUG or not
     * @param null $class Class name (optional -- for logging)
     * @param null $function Function name (optional -- for logging)
     *
     * @throws ActiveToolsException
     */
    public static function raise( $message, $code = null, $force_throw = false, $class = null, $function = null ) {
        
        if ( empty( static::$errors ) && is_admin() ) {
            add_action( 'admin_notices', array( __NAMESPACE__ . '\\ActiveToolsException', 'print_errors' ) );
            add_action( 'network_admin_notices', array( __NAMESPACE__ . '\\ActiveToolsException', 'print_errors' ) );
        }
        
        $exception = new self( $message, $code );
        
        if ( static::$throw_errors || $force_throw ) {
            throw $exception;
        } else {
            static::$errors[] = $exception;
        }
    }
    
    public static function print_errors() {
        $hideErrorsCookieName = 'atErrHide';
        
        // Disable cookies
        if ( isset( $_COOKIE[ $hideErrorsCookieName ] ) ) {
            return;
        }
        
        $errors = static::$errors;
        $plural = count( $errors ) === 1 ? '' : 's';
        
        // include \ActiveTools\TEMPLATES_PATH . 'Exception/active-tools.php';
    }
}
