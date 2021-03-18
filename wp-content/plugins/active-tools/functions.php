<?php

// Debug message
if ( ! function_exists( 'd' ) ) {
    function at_dbg( $var ) {
        echo '<pre>' . var_export( $var, true ) . '</pre>';
        die();
    }
}
