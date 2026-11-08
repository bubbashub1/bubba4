<?php
/** Pure BH4 directory filter normalizer for future REST and web parity. No queries or hooks. */
defined( 'ABSPATH' ) || exit;
final class BH4_Directory_Filter_Contract {
    public static function normalize( $input ) {
        if ( ! is_array( $input ) ) { return new WP_Error( 'bh4_invalid_filters', 'Filters must be an array.' ); }
        $allowed = array( 'search', 'category', 'town', 'region', 'postcode', 'age_months', 'day', 'term_time', 'free_only', 'max_price', 'sort', 'page', 'per_page' );
        foreach ( $input as $key => $unused ) {
            if ( ! in_array( $key, $allowed, true ) ) { return new WP_Error( 'bh4_unknown_filter', 'Unknown filter.' ); }
        }
        $result = array();
        foreach ( array( 'search' => 100, 'category' => 150, 'town' => 120, 'region' => 100, 'postcode' => 12 ) as $key => $max ) {
            $value = $input[ $key ] ?? '';
            if ( ! is_string( $value ) || strlen( $value ) > $max ) { return new WP_Error( 'bh4_invalid_filter', 'Invalid text filter.' ); }
            $result[ $key ] = trim( $value );
        }
        foreach ( array( 'age_months' => array( 0, 216 ), 'day' => array( 0, 7 ), 'page' => array( 1, 100 ), 'per_page' => array( 1, 24 ) ) as $key => $bounds ) {
            $value = $input[ $key ] ?? ( $key === 'page' ? 1 : ( $key === 'per_page' ? 12 : 0 ) );
            if ( ! is_int( $value ) || $value < $bounds[0] || $value > $bounds[1] ) { return new WP_Error( 'bh4_invalid_filter', 'Invalid numeric filter.' ); }
            $result[ $key ] = $value;
        }
        foreach ( array( 'term_time', 'free_only' ) as $key ) {
            $value = $input[ $key ] ?? false;
            if ( ! is_bool( $value ) ) { return new WP_Error( 'bh4_invalid_filter', 'Invalid boolean filter.' ); }
            $result[ $key ] = $value;
        }
        $price = $input['max_price'] ?? 0;
        if ( ! is_int( $price ) || ! in_array( $price, array( 0, 5, 10, 20, 50 ), true ) ) { return new WP_Error( 'bh4_invalid_filter', 'Invalid price filter.' ); }
        $result['max_price'] = $result['free_only'] ? 0 : $price;
        $sort = $input['sort'] ?? 'title';
        if ( ! in_array( $sort, array( 'title', 'town', 'newest' ), true ) ) { return new WP_Error( 'bh4_invalid_filter', 'Invalid sort.' ); }
        $result['sort'] = $sort;
        return $result;
    }
}
