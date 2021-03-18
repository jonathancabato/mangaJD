<?php

namespace ActiveTools\Utility;

use ActiveTools\Database\Models\LoggerModel;

class Logger {
    
    public const CATEGORY_GENERAL = 0;
    public const CATEGORY_CRON = 1;
    
    public const LEVEL_DEBUG = 0;
    public const LEVEL_NOTICE = 1;
    public const LEVEL_WARNING = 2;
    public const LEVEL_ERROR = 3;
    
    
    private static ?LoggerModel $model = null;
    
    private function __construct() {
        
        self::$model = new LoggerModel();
    }
    
    public static function log( string $message, int $category = self::CATEGORY_GENERAL, int $level = self::LEVEL_DEBUG, ?string $class = null, \DateTime $time = null ) {
    
        $min_level = self::get_level_from_label( get_option( '_at_logging_level', 'notice' ) );
        
        if ( $level < $min_level ) {
            return;
        }
        
        if ( self::$model == null ) {
            new Logger();
        }
        
        if ( $time == null ) {
            $time = new \DateTime();
        }
        
        if ( $class != null ) {
            $message = $class . ' :: ' . $message;
        }
        
        self::$model->add_entry( $time, $category, $level, $message );
    }
    
    public static function debug( string $message, int $category = self::CATEGORY_GENERAL, $class = null, \DateTime $time = null ) {
        self::log( $message, $category, self::LEVEL_DEBUG, $class, $time );
    }
    public static function notice( string $message, int $category = self::CATEGORY_GENERAL, $class = null, \DateTime $time = null ) {
        self::log( $message, $category, self::LEVEL_NOTICE, $class, $time );
    }
    public static function warning( string $message, int $category = self::CATEGORY_GENERAL, $class = null, \DateTime $time = null ) {
        self::log( $message, $category, self::LEVEL_WARNING, $class, $time );
    }
    public static function error( string $message, int $category = self::CATEGORY_GENERAL, $class = null, \DateTime $time = null ) {
        self::log( $message, $category, self::LEVEL_ERROR, $class, $time );
    }
    
    public static function get_category_label( int $category ) {
        switch ( $category ) {
            case self::CATEGORY_CRON:
                return 'Cron Job';
            default:
                return 'General';
        }
    }
    
    public static function get_category_description( int $category ) {
        switch ( $category ) {
            case self::CATEGORY_CRON:
                return 'This log entry is related to cron tasks used to maintain the Web Scheduler experience';
            default:
                return 'This log entry does not have a specific category.';
        }
    }
    
    public static function get_level_label( int $level ) {
        switch ( $level ) {
            case self::LEVEL_DEBUG:
                return 'Debug';
            case self::LEVEL_WARNING:
                return 'Warning';
            case self::LEVEL_ERROR:
                return 'Error';
            default:
                return 'Notice';
        }
    }
    
    public static function get_level_from_label( string $level ) {
        switch ( $level ) {
            case 'debug':
                return self::LEVEL_DEBUG;
            case 'warning':
                return self::LEVEL_WARNING;
            case 'error':
                return self::LEVEL_ERROR;
            default:
                return self::LEVEL_NOTICE;
        }
    }
    
    public static function get_level_description( int $level ) {
        switch ( $level ) {
            case self::LEVEL_DEBUG:
                return 'This is a debug entry used for troubleshooting problems. ';
            case self::LEVEL_WARNING:
                return 'This is a warning. It isn\'t necessarily feature-breaking but should be addressed as soon as possible.';
            case self::LEVEL_ERROR:
                return 'This is a feature-breaking error and needs to be addressed immediately.';
            default:
                return 'This is an informative notice that may or may not require attention.';
        }
    }
    
    public static function category_from_class( $class_name ) {
        $class_to_cat_map = [
            // 'ActiveTools\Some\Class' => self::CATEGORY_SOMETHING,
            // 'ActiveTools\Some\Class' => self::CATEGORY_SOMETHING,
        ];
        
        foreach ( $class_to_cat_map as $class => $category ) {
            if ( strpos( $class, $class_name ) !== false ) {
                return $category;
            }
        }
        
        return self::CATEGORY_GENERAL;
    }
}
