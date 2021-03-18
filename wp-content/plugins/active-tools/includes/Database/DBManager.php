<?php

namespace ActiveTools\Database;

class DBManager {
    
    private array $tables = [
        DBTABLES::LOGGER,
        DBTABLES::IP_DATA,
    ];
    
    public function __construct() {
    
    }
    
    /**
     * Creates and modifies the necessary tables
     */
    public function create_db_tables() {
        global $wpdb;
        
        $table_querys = array();
        
        // Logger
        $logger_table_suffix = DBTABLES::LOGGER;
        $table_querys[] = <<<EOT
CREATE TABLE IF NOT EXISTS {$wpdb->prefix}{$logger_table_suffix} (
  id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  time DATETIME NOT NULL,
  category TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
  level TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
  message LONGTEXT NOT NULL DEFAULT '',
  PRIMARY KEY (id),
  INDEX at_log_index (category ASC, id ASC) ) {$wpdb->get_charset_collate()};
EOT;
        // IP Data
        $ip_table_prefix = DBTABLES::IP_DATA;
        $table_querys[] = <<<EOT
CREATE TABLE IF NOT EXISTS {$wpdb->prefix}{$ip_table_prefix} (
  id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  ip VARCHAR(39) NOT NULL,
  geo_last_updated DATETIME NOT NULL,
  proxy_last_updated DATETIME NOT NULL,
  country VARCHAR(255) NOT NULL DEFAULT '',
  region VARCHAR(255) NOT NULL DEFAULT '',
  city VARCHAR(255) NOT NULL DEFAULT '',
  proxy_level FLOAT(23) NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  INDEX at_geoip_index (ip ASC ) ) {$wpdb->get_charset_collate()};
EOT;
        // User IP Relations
        $ip_user_table_prefix = DBTABLES::IP_USERS;
        $table_querys[] = <<<EOT
CREATE TABLE IF NOT EXISTS {$wpdb->prefix}{$ip_user_table_prefix} (
  id BINARY(16) NOT NULL,
  user_id BIGINT(20) UNSIGNED NOT NULL,
  ip_id BIGINT(20) UNSIGNED NOT NULL,
  last_used DATETIME NOT NULL,
  PRIMARY KEY (id),
  INDEX at_user_ip_index (user_id ASC, ip_d ASC )
  CONSTRAINT fk_user_id_to_wp_user_id FOREIGN KEY (user_id) REFERENCES {$wpdb->prefix}users (ID) ON DELETE CASCADE
  CONSTRAINT fk_ip_id_to_ip_data_id FOREIGN KEY (ip_id) REFERENCES {$wpdb->prefix}{$ip_table_prefix} (id) ON DELETE CASCADE) {$wpdb->get_charset_collate()};
EOT;
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        // Create table structure
        foreach ($table_querys as $table_query) {
            dbDelta($table_query);
        }
        
    }
    
    /**
     * Removes tables completely
     *
     * @param array|string|null $tables_to_drop
     */
    public function drop_db_tables( $tables_to_drop = null ) {
        
        global $wpdb;
    
        if ( ! is_array( $tables_to_drop ) && ! is_string( $tables_to_drop ) ) {
            $tables_to_drop = $this->tables;
        }
    
        if ( is_string( $tables_to_drop ) ) {
            $tables_to_drop = [ $tables_to_drop ];
        }
        
        foreach ($tables_to_drop as $table) {
            $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}{$table}");
        }
        
    }
    
    /**
     * Deletes all data in tables
     *
     * @var array|string|null $tables_to_truncate array Tables to clear
     */
    public function truncate_db_tables( $tables_to_truncate = null ) {
        
        global $wpdb;
        
        if ( ! is_array( $tables_to_truncate ) && ! is_string( $tables_to_truncate ) ) {
            $tables_to_truncate = $this->tables;
        }
        
        if ( is_string( $tables_to_truncate ) ) {
            $tables_to_truncate = [ $tables_to_truncate ];
        }
        
        $wpdb->query("SET FOREIGN_KEY_CHECKS=0;");
        $wpdb->query("SET AUTOCOMMIT = 0;");
        $wpdb->query("START TRANSACTION;");
        
        foreach ($tables_to_truncate as $table) {
            $wpdb->query("TRUNCATE TABLE {$wpdb->prefix}{$table}");
        }
        
        $wpdb->query("COMMIT;");
        $wpdb->query("SET FOREIGN_KEY_CHECKS=1;");
        $wpdb->query("SET AUTOCOMMIT = 1;");
    }
}
