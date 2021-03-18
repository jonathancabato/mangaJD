<?php declare(strict_types=1);

namespace ActiveTools\Utility;

class Sanitizer {
    
    public static function sanitize_fields( $field_data, $field_map ) {
        $sanitized_data = [];
    
        foreach ( $field_data as $key => $field ) {
            // If the field map has the same key, process it
            if ( array_key_exists( $key, $field_map ) ) {
                // If it's a null value, leave it in the output as null as well
                if ( $field_data[$key] === null || strtolower( $field_data[$key] ) === 'null' ) {
                    $sanitized_data[$key] = null;
                    continue;
                }
            
                // Get the sanitized & formatted data
                if ( is_array( $field_map[$key] ) ) {
                    $result = call_user_func( array( __NAMESPACE__ . '\\Sanitizer', $field_map[$key][0] ), $field, ...$field_map[$key][1] );
                
                    if ( $result !== null ) {
                        $sanitized_data[$key] = $result;
                    }
                
                    continue;
                }
            
                $result = call_user_func( array( __NAMESPACE__ . '\\Sanitizer', $field_map[$key] ), $field );
            
                if ( $result !== null ) {
                    $sanitized_data[$key] = $result;
                }
            }
        }
    
        return $sanitized_data;
    }
    public static function text_field( string $name, int $truncate_length = 0 ) : string {
        
        $name = sanitize_text_field( $name );
        
        if ( $truncate_length > 0 ) {
            $name = substr( $name, 0, $truncate_length );
        }
        
        return $name;
    }
    
    public static function email( string $email ) {
    
        $sanitized = filter_var( $email,FILTER_SANITIZE_EMAIL );
    
        if ( $email == $sanitized && filter_var( $email,FILTER_VALIDATE_EMAIL ) ){
            return $email;
        }
        
        return null;
    }
    
    public static function date( string $date ) {
        try {
            $date = new \DateTime( $date );
            return $date;
        } catch ( \Exception $e ) {
            return null;
        }
    }
    
    
    public static function digit( string $number, int $length ) {
        
        $number = preg_replace( '/\D/', '', $number );
        
        if ( strlen( $number ) == $length ) {
            return $number;
        }
        
        return null;
    }
    
    public static function slot_time( string $time ) {
        $pattern = '/^[\d]{2}:[\d]{2}:[\d]{2}$/';
        
        if ( preg_match( $pattern, $time ) ) {
            return $time;
        }
        
        return null;
    }
    
    public static function bool( $bool ) {
        
        if ( is_bool( $bool ) ) {
            return $bool;
        }
        
        if ( $bool === 0 || $bool === 1 ) {
            return !!$bool;
        }
        
        if ( is_string( $bool ) ) {
            switch( strtolower( $bool ) ) {
                case 'true':
                case '1':
                    return true;
                case 'false':
                case '0':
                    return false;
            }
        }
        
        return null;
    }
    
    public static function int( string $number ) {
        if ( filter_var( $number, FILTER_VALIDATE_INT ) !== false ) {
            return intval( $number );
        }
        return null;
    }
    public static function absint( string $number ) {
        return absint( $number );
    }
    public static function phone( string $phone ) {
        return self::digit( $phone, 10 );
    }
    public static function ssn( string $ssn ) {
        return self::digit( $ssn, 9 );
    }
}
