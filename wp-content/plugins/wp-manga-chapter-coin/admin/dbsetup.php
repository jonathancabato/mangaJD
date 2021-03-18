<?php

/**
 * Setup database structure
 **/
function wmcc_setup_db(){
	wmcc_create_table('manga_chapter_coin', array(
			'chapter_id' => 'bigint(20) NOT NULL PRIMARY KEY',
			'manga_id' => 'bigint(20) NOT NULL',
			'coin'    => 'int NOT NULL'
		));
}

function wmcc_maybe_create_table( $table_name, $create_ddl ) {

	global $wpdb;

	if( wmcc_table_exists( $table_name ) ){
		update_option('wp_manga_chapter_coin_db_ver', '1.0');
		return true;
	}

	// Didn't find it try to create it..
	$wpdb->query($create_ddl);

	// We cannot directly tell that whether this succeeded!
	if( wmcc_table_exists( $table_name ) ){
		update_option('wp_manga_chapter_coin_db_ver', '1.0');
		return true;
	}

	return false;
}

function wmcc_table_exists( $table_name ){
	global $wpdb;
	
	$query =$wpdb->prepare( "SHOW TABLES LIKE %s", $wpdb->esc_like( $table_name ) );

	if ( $wpdb->get_var( $query ) == $table_name ) {
		return true;
	}

	return false;

}

function wmcc_is_index_exists($table_name, $index){
	global $wpdb;
	
	$query = $wpdb->prepare("SHOW INDEX FROM %s WHERE COLUMN_NAME = %s", $table_name, $index);
	return !empty( $wpdb->query( $query ) );
}

function wmcc_column_exists( $table_name, $column_name ){
	global $wpdb;
	$query = $wpdb->prepare(
		"SELECT *
		FROM INFORMATION_SCHEMA.COLUMNS
		WHERE TABLE_NAME = %s
		AND COLUMN_NAME = %s",
		$table_name,
		$column_name
	);

	return !empty( $wpdb->query( $query ) );

}

function wmcc_alter_add_column( $table_name, $column_name, $column_data ){
	global $wpdb;
	return !empty( $wpdb->query( "ALTER TABLE {$table_name}
	ADD COLUMN {$column_name} {$column_data}" ) );

}

/**
 * Create a database table
 **/
function wmcc_create_table( $name, $args, $indexs = array() ) {
	global $wpdb;
	if ( ! is_array( $args ) || empty( $args ) ) {
		return false;
	}

	$charset_collate = $wpdb->get_charset_collate();
	$table_name      = $wpdb->prefix . $name;

	if( wmcc_table_exists( $table_name ) ){
		update_option('wp_manga_chapter_coin_db_ver', '1.0');
		foreach( $args as $column => $data ){
			if( ! wmcc_column_exists( $table_name, $column ) ){
				wmcc_alter_add_column( $table_name, $column, $data );
			}
		}
	} else {
		$query_args = array();

		foreach( $args as $column => $data ){
			$query_args[] = "{$column} {$data}";
		}

		$sql = "CREATE TABLE $table_name (
			" . implode( ', ', $query_args );
		
		if(count($indexs) > 0) {
			$str = '';
			foreach($indexs as $key => $val){
				$str .= ",INDEX $key $val";
			}
			$sql .= $str;
		};
		
		$sql .= ") $charset_collate;";

		wmcc_maybe_create_table( $table_name, $sql );
	}
	
	if(count($indexs) > 0){
		foreach($indexs as $key => $val){
			if(!wmcc_is_index_exists($table_name, $key)){
				// create index
				$sql = "CREATE INDEX $key ON $table_name $val";
				$wpdb->query($sql);
			}
		}
	}
}