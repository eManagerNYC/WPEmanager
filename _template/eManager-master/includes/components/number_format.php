<?php

/**
 * Make number formatting uniform through this site gateway
 *
 * @author  Jake Snyder
 * @since	3.0.17
 * @return  string User's name
 */
function eman_number_format( $int )
{
    if ( is_string($int) ) {
        if ( is_numeric($int) ) {
            $int = (int)$int;
        } else {
            return 0;
        }
    }
	return number_format($int, 2);
}