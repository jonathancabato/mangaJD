<?php

namespace ActiveTools\Database\Models;

use ActiveTools\Database;

class LoggerModel extends Model {
    
    public function __construct() {
        parent::__construct( Database\DBTABLES::LOGGER );
    }
    
    public function add_entry( $time, $category, $level, $message ) {
    
        $payload = [
            'time' => [
                'value' => $time,
                'format' => Model::FORMAT_STRING
            ],
            'category' => [
                'value' => $category,
                'format' => Model::FORMAT_INT
            ],
            'level' => [
                'value' => $level,
                'format' => Model::FORMAT_INT
            ],
            'message' => [
                'value' => $message,
                'format' => Model::FORMAT_STRING
            ],
        ];
        
        parent::insert( $payload );
    }
    
    public function get_entries() {
        return $this->get_results([], ['column' => 'id', 'direction' => 'DESC'], null, null, ARRAY_A);
    }
    
    public function prune_entries( int $age = 0 ) {
        
        if ( $age < 1 ) {
            return false;
        }
        $date = (new \DateTime())->modify("-{$age} days");
        
        return $this->delete_rows([
            'time' => [
                'format' => Model::FORMAT_STRING,
                'operator' => '<=',
                'value' => $date->format( 'Y-m-d H:i:s' )
            ]
        ]);
    }
}
