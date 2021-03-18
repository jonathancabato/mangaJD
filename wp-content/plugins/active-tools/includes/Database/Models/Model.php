<?php

namespace ActiveTools\Database\Models;

use ActiveTools\Database;

abstract class Model {
    
    public const FORMAT_STRING = '%s';
    public const FORMAT_INT = '%d';
    public const FORMAT_FLOAT = '%f';
    public const FORMAT_ARRAY = '%f';
    
    protected $wpdb;
    
    protected $db_table = '';
    
    public function __construct( $db_table = '' ) {
        global $wpdb;
        
        $this->wpdb = $wpdb;
        $this->db_table = $db_table;
    }
    
    /**
     * Updates or inserts data into table
     *
     * @param $payload
     * @param $where_data
     * @param bool $update_only
     * @param string $index_column
     *
     * @return bool|int
     */
    protected function update( $payload, $where_data, $update_only = false, $index_column = 'id' ) {
        
        $data   = [];
        $formats = [];
        
        foreach ( $payload as $column => $d ) {
            if ( ! isset( $d['value'] ) ) {
                return false;
            }
            $format = isset( $d['format'] ) ? $d['format'] : $this->get_format_from_value( $d['value'] );
            $data[ $column ] = $this->sanitize( $d['value'] );
            $formats[] = $format;
        }
        
        if ( ! empty( $where_data ) ) {
            
            $update_where_format = [];
            $select_where_statement = '';
            $select_where_data      = [];
            
            // Prepare where data related to the select and update queries
            foreach ( $where_data as $column => $d ) {
                $format = isset( $d['format'] ) ? $d['format'] : $this->get_format_from_value( $d['value'] );
                $operator = isset( $d['operator'] ) && $this->valid_operator( $d['operator'] ) ? $d['operator'] : '=';
                $select_where_data[]    = $this->sanitize( $d['value'] );
                $select_where_statement .= ( $select_where_statement ? " AND" : " WHERE" ) . " {$column} {$operator} {$format}";
                $update_where_format[]  = $format;
            }
            
            // Try and find the item in the database first
            $return = $this->wpdb->get_row( $this->wpdb->prepare( "
				SELECT {$index_column}
				FROM {$this->wpdb->prefix}{$this->db_table}
					{$select_where_statement}
			", $select_where_data ) );
            
            // If the select query revealed an item, run an update operation
            if( !empty( $return ) ) {
                
                $this->wpdb->update(
                    "{$this->wpdb->prefix}{$this->db_table}",
                    $data,
                    array($index_column => $return->{$index_column}),
                    $formats,
                    $update_where_format
                );
                return $return->{$index_column};
            }
        }
        
        // Otherwise, insert
        if ( ! $update_only ) {
            if ( $this->wpdb->insert(
                "{$this->wpdb->prefix}{$this->db_table}",
                $data,
                $formats
            ) ) {
                return $this->wpdb->insert_id;
            } else {
                return false;
            }
        }
        
        return false;
    }
    
    
    /**
     * Inserts row into table
     *
     * Where possible, use insert if you know you'll only need to insert, for performance gain
     *
     * @param $payload
     *
     * @return bool|int
     */
    protected function insert( $payload ) {
    
        $data   = [];
        $formats = [];
    
        foreach ( $payload as $column => $d ) {
            if ( ! isset( $d['value'] ) ) {
                return false;
            }
            $data[ $column ] = $this->sanitize( $d['value'] );
            $formats[] = isset( $d['format'] ) ? $d['format'] : $this->get_format_from_value( $d['value'] );
        }
        
        if ( $this->wpdb->insert(
            "{$this->wpdb->prefix}{$this->db_table}",
            $data,
            $formats
        ) ) {
            return $this->wpdb->insert_id;
        } else {
            return false;
        }
    }
    
    protected function get_row( $where = [], $object= ARRAY_A ) {
    
        $where_data = [];
        $where_statement = '';
        
        if ( ! empty( $where ) ) {
    
            // Prepare where data related to the select and update queries
            foreach ( $where as $column => $d ) {
                $format                 = isset( $d['format'] ) ? $d['format'] : $this->get_format_from_value( $d['value'] );
                $operator = isset( $d['operator'] ) && $this->valid_operator( $d['operator'] ) ? $d['operator'] : '=';
                $where_data[]    = $this->sanitize( $d['value'] );
                $where_statement .= ( $where_statement ? " AND" : " WHERE" ) . " {$column} {$operator} {$format}";
            }
    
        }
        // Try and find the item in the database first
        $result = $this->wpdb->get_row( $this->wpdb->prepare( "
				SELECT *
				FROM {$this->wpdb->prefix}{$this->db_table}
					{$where_statement}
			", $where_data ), $object );
        
        if ( empty( $result ) ) {
            return [];
        }
        
        return $result;
    }
    
    protected function get_results( $where = [], $order_by = [], int $per_page = null, int $page = null, $return_type = OBJECT, $count_only = false ) {
        
        $order_by_statement = '';
        $paging_statement   = '';
        
        $select_what = $count_only ? 'count(*)' : '*';
        
        if ( $per_page != null ) {
            $paging_statement = 'LIMIT ' . $per_page;
            
            if ( $page != null ) {
                $paging_statement .= ' OFFSET ' . $page * $per_page;
            }
        }
        
        if ( is_array( $order_by )
             && ! empty( $order_by )
             && is_string( $order_by['column'] )
             && is_string( $order_by['direction'] ) ) {
            $order_by_statement = "ORDER BY {$order_by['column']} {$order_by['direction']}";
        }
        
        if ( ! empty( $where ) ) {
            
            $where_data      = [];
            $where_statement = '';
            
            // Prepare where data related to the select and update queries
            foreach ( $where as $column => $d ) {
                $format          = isset( $d['format'] ) ? $d['format'] : $this->get_format_from_value( $d['value'] );
                $operator        = isset( $d['operator'] ) && $this->valid_operator( $d['operator'] ) ? $d['operator'] : '=';
                $where_data[]    = $this->sanitize( $d['value'] );
                $where_statement .= ( $where_statement ? " AND" : " WHERE" ) . " {$column} {$operator} {$format}";
            }
            
            return $this->wpdb->get_results( $this->wpdb->prepare( "
				SELECT {$select_what}
				FROM {$this->wpdb->prefix}{$this->db_table}
					{$where_statement}
					{$order_by_statement}
					{$paging_statement}
			", $where_data ), $return_type );
        }
        
        
        return $this->wpdb->get_results( "
				SELECT *
				FROM {$this->wpdb->prefix}{$this->db_table}
					{$order_by_statement}
					{$paging_statement}
				", $return_type );
    }
    
    protected function delete_rows( $where = [] ) {
        
        if ( ! empty( $where ) ) {
    
            $where_statement = '';
            $where_data = [];
            
            // Prepare where data related to the select and update queries
            foreach ( $where as $column => $d ) {
                $format = isset( $d['format'] ) ? $d['format'] : $this->get_format_from_value( $d['value'] );
                $operator = isset( $d['operator'] ) && $this->valid_operator( $d['operator'] ) ? $d['operator'] : '=';
                $where_data[] = $this->sanitize( $d['value'] );
                $where_statement .= ( $where_statement ? " AND" : " WHERE" ) . " {$column} {$operator} {$format}";
            }
        
            return $this->wpdb->query( $this->wpdb->prepare( "
				DELETE
				FROM {$this->wpdb->prefix}{$this->db_table}
					{$where_statement}
			", $where_data ) );
        }
        
        return false;
    }
    
    /**
     * Non-coercive detection of format based on value
     *
     * @param $value
     *
     * @return string
     */
    protected function get_format_from_value( $value ) {
    
        if ( is_int( $value ) ) {
            return self::FORMAT_INT;
        }
        
        if ( is_float( $value ) ) {
            return self::FORMAT_FLOAT;
        }
        
        return self::FORMAT_STRING;
    }
    
    protected function sanitize( $value ) {
        
        if ( is_object( $value ) && get_class( $value ) == 'DateTime' ) {
            return $value->format( 'Y-m-d H:i:s' );
        }
        
        if ( is_array( $value ) ) {
            return maybe_serialize( $value );
        }
        
        return sanitize_text_field( $value );
    }
    
    protected function valid_operator( $operator ) {
        
        if ( ! is_string( $operator ) ) {
            return false;
        }
        
        $valid_operators = [
            '=',
            '>=',
            '<=',
            '!=',
        ];
        
        return in_array( $operator, $valid_operators );
    }
}
