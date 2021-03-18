<?php

namespace ActiveTools;

use ActiveTools\Database\Models\LoggerModel;
use ActiveTools\Utility\Logger;

class Cron {
    
    function __construct() {
    
    
        /** Creates cron schedules @see Cron::custom_cron_schedules */
        add_filter( 'cron_schedules', array( $this, 'custom_cron_schedules' ), 10, 1 );
        /** Schedule cron events @see Cron::maybe_schedule_crons */
        add_action( 'init', array( $this, 'maybe_schedule_crons' ) );
        
        /** Pruning error logs @see Cron::prune_error_logs */
        add_action( 'at_prune_error_logs', array( $this, 'prune_error_logs' ) );
        
    }
    
    
    public function custom_cron_schedules( $schedules ) {
        
        // General 5-minute cron for utility purposes
        $schedules[ 'every-5-minutes' ] = array( 'interval' => 5 * MINUTE_IN_SECONDS, 'display' => __( 'Every 5 minutes', 'active-tools' ) );
        
        /*
        // Every 1 minute
        $schedules[ 'every-1-minute' ] = array( 'interval' => MINUTE_IN_SECONDS, 'display' => __( 'Every 1 minute', 'active-tools' ) );
        // Every 30 minutes
        $schedules[ 'every-30-minutes' ] = array( 'interval' => 30 * MINUTE_IN_SECONDS, 'display' => __( 'Every 30 minutes', 'active-tools' ) );
        // Every 4 hours
        $schedules[ 'every-4-hours' ] = array( 'interval' => 4 * HOUR_IN_SECONDS, 'display' => __( 'Every 4 hours', 'active-tools' ) );
        // Every 1 day
        $schedules[ 'every-1-day' ] = array( 'interval' => DAY_IN_SECONDS, 'display' => __( 'Every 4 hours', 'active-tools' ) );
        */
    
        return $schedules;
    }
    
    public function maybe_schedule_crons() {
        
        // Prune error logs
        if ( ! wp_next_scheduled( 'at_prune_error_logs' ) ) {
            wp_schedule_event( time(), 'every-5-minutes', 'at_prune_error_logs' );
        }
        
    }
    
    public static function deregister_cron_jobs() {
        
        // Remove error log pruning
        $timestamp = wp_next_scheduled( 'at_prune_error_logs' );
        wp_unschedule_event( $timestamp, 'at_prune_error_logs' );
        
    }
    
    public function prune_error_logs() {
        
        if ( $prune_age = get_option( '_at_logging_retention', 14 ) ) {
    
            Logger::notice( 'Pruned error logs', Logger::CATEGORY_CRON );
            
            $loggerModel = new LoggerModel();
            
            $loggerModel->prune_entries( $prune_age );
        }
    }
}
