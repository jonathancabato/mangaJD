<?php declare(strict_types=1);

namespace ActiveTools\Database\Models;

use ActiveTools\Database;

class IPDataModel extends Model {
    
    public function __construct() {
        parent::__construct( Database\DBTABLES::IP_DATA );
    }
    
    public function get_ip( string $ip ) {
        
        $result = $this->get_row( [ 'ip' => [ 'value' => $ip, 'format' => Model::FORMAT_STRING ] ] );
        
        if ( empty( $result ) ) {
            return [];
        }
        
        $result['proxy_level'] = floatval( $result['proxy_level'] );
        $result['id'] = absint( $result['id'] );
        $result['geo_last_updated'] = new \DateTime( $result['geo_last_updated'] );
        $result['proxy_last_updated'] = new \DateTime( $result['proxy_last_updated'] );
        
        return $result;
    }
    
    public function add_ip( string $ip, string $country = '', string $region = '', string $city = '', float $proxy_level = 0 ) {
        
        $date = new \DateTime();
        
        $payload = [
            'ip' => [
                'value' => $ip,
                'format' => Model::FORMAT_STRING
            ],
            'geo_last_updated' => [
                'value' => $date,
                'format' => Model::FORMAT_STRING
            ],
            'proxy_last_updated' => [
                'value' => $date,
                'format' => Model::FORMAT_STRING
            ],
            'country' => [
                'value' => $country,
                'format' => Model::FORMAT_STRING
            ],
            'region' => [
                'value' => $region,
                'format' => Model::FORMAT_STRING
            ],
            'city' => [
                'value' => $city,
                'format' => Model::FORMAT_STRING
            ],
            'proxy_level' => [
                'value' => $proxy_level,
                'format' => Model::FORMAT_FLOAT
            ],
        ];
        
        return parent::insert( $payload );
    }
    
    public function update_ip( string $ip, ?string $country = null, ?string $region = null, ?string $city = null, ?float $proxy_level = null ) {
    
        $date = new \DateTime();
        $updating_geo = false;
        
        $payload = [
            'ip' => [
                'value' => $ip,
                'format' => Model::FORMAT_STRING
            ],
        ];
        
        if ( $country !== null ) {
            $updating_geo = true;
            $payload['country'] = [
                'value' => $country,
                'format' => Model::FORMAT_STRING
            ];
        }
        if ( $region !== null ) {
            $updating_geo = true;
            $payload['region'] = [
                'value' => $region,
                'format' => Model::FORMAT_STRING
            ];
        }
        if ( $city !== null ) {
            $updating_geo = true;
            $payload['city'] = [
                'value' => $city,
                'format' => Model::FORMAT_STRING
            ];
        }
    
        if ( $proxy_level !== null ) {
            $payload['proxy_level'] = [
                'value' => $proxy_level,
                'format' => Model::FORMAT_FLOAT
            ];
            $payload['proxy_last_updated'] = [
                'value' => $date,
                'format' => Model::FORMAT_STRING
            ];
        }
        
        if ( $updating_geo ) {
            $payload['geo_last_updated'] = [
                'value' => $date,
                'format' => Model::FORMAT_STRING
            ];
        }
        
        return parent::update( $payload, [ 'ip' => [ 'value' => $ip, 'format' => Model::FORMAT_STRING ] ] );
    }
    
    public function delete_ip( int $ip ) {
        return $this->delete_rows( [ 'ip' => [ 'value' => $ip, 'format' => Model::FORMAT_STRING ] ] );
    }
}
