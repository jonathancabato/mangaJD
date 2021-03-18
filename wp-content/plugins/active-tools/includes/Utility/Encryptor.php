<?php

namespace ActiveTools\Utility;


class Encryptor {
    
    public static function encrypt( string $string_to_encrypt, string $encryption_key ) {
        
        if ( mb_strlen( $encryption_key, '8bit' ) !== SODIUM_CRYPTO_SECRETBOX_KEYBYTES ) {
            throw new \RangeException( 'Key is not the correct size (must be 32 bytes).' );
        }
        
        $nonce = random_bytes( SODIUM_CRYPTO_SECRETBOX_NONCEBYTES );
        
        $cipher = base64_encode(
            $nonce .
            sodium_crypto_secretbox(
                $string_to_encrypt,
                $nonce,
                $encryption_key
            )
        );
        sodium_memzero( $string_to_encrypt );
        sodium_memzero( $encryption_key );
        
        return $cipher;
    }
    
    public static function decrypt( string $encrypted_string, string $encryption_key ): string {
        $decoded     = base64_decode( $encrypted_string );
        $nonce       = mb_substr( $decoded, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES, '8bit' );
        $cipher_text = mb_substr( $decoded, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES, null, '8bit' );
        
        $plain = sodium_crypto_secretbox_open(
            $cipher_text,
            $nonce,
            $encryption_key
        );
        if ( ! is_string( $plain ) ) {
            throw new \Exception( 'Invalid MAC' );
        }
        sodium_memzero( $cipher_text );
        sodium_memzero( $encryption_key );
        
        return $plain;
    }
    
    public static function generate_encryption_key() : string {

        $length    = 32;
        $key_space = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

        $pieces = [];
        $max    = mb_strlen( $key_space, '8bit' ) - 1;

        for ( $i = 0; $i < $length; ++ $i ) {
            $pieces [] = $key_space[ random_int( 0, $max ) ];
        }

        return implode( '', $pieces );
    }
}
