<?php
/**
 * Strict bridge from legacy directory bh_* query parameters to typed BH4 filters.
 * Pure parsing only. Does not read superglobals, store coordinates or query DB.
 */
defined( 'ABSPATH' ) || exit;
final class BH4_Directory_Web_Filters {
    public static function from_query( $query ) {
        if ( ! is_array( $query ) ) { return new WP_Error( 'bh4_invalid_web_query', 'Invalid query.' ); }
        if ( ! class_exists( 'BH4_Directory_Filter_Contract', false ) ) { return new WP_Error( 'bh4_missing_filter_contract', 'Filter contract unavailable.' ); }
        $mapping = array(
            'bh_search' => 'search', 'bh_category' => 'category', 'bh_town' => 'town',
            'bh_region' => 'region', 'bh_postcode' => 'postcode', 'bh_sort' => 'sort',
        );
        $filters = array();
        foreach ( $mapping as $old => $new ) {
            if ( ! array_key_exists( $old, $query ) ) { continue; }
            if ( ! is_string( $query[ $old ] ) ) { return new WP_Error( 'bh4_invalid_web_filter', 'Expected text filter.' ); }
            $filters[ $new ] = $query[ $old ];
        }
        foreach ( array( 'bh_age' => 'age_months', 'bh_day' => 'day', 'bh_page' => 'page', 'bh_max_price' => 'max_price', 'bh_radius' => 'radius_miles' ) as $old => $new ) {
            if ( ! array_key_exists( $old, $query ) ) { continue; }
            $value = $query[ $old ];
            if ( ! is_string( $value ) && ! is_int( $value ) ) { return new WP_Error( 'bh4_invalid_web_filter', 'Expected integer filter.' ); }
            if ( ! preg_match( '/^(?:0|[1-9][0-9]*)$/D', (string) $value ) ) { return new WP_Error( 'bh4_invalid_web_filter', 'Invalid integer filter.' ); }
            $filters[ $new ] = (int) $value;
        }
        foreach ( array( 'bh_free' => 'free_only', 'bh_term' => 'term_time' ) as $old => $new ) {
            if ( ! array_key_exists( $old, $query ) ) { continue; }
            if ( ! in_array( $query[ $old ], array( '0', '1', 0, 1 ), true ) ) { return new WP_Error( 'bh4_invalid_web_filter', 'Invalid boolean filter.' ); }
            $filters[ $new ] = (string) $query[ $old ] === '1';
        }
        $has_lat = array_key_exists( 'bh_lat', $query );
        $has_lng = array_key_exists( 'bh_lng', $query );
        if ( $has_lat !== $has_lng ) { return new WP_Error( 'bh4_invalid_web_geo', 'Both coordinates required.' ); }
        if ( $has_lat ) {
            foreach ( array( 'bh_lat' => 'latitude', 'bh_lng' => 'longitude' ) as $old => $new ) {
                $value = $query[ $old ];
                if ( ! is_string( $value ) && ! is_int( $value ) && ! is_float( $value ) ) { return new WP_Error( 'bh4_invalid_web_geo', 'Invalid coordinate.' ); }
                if ( ! preg_match( '/^-?(?:[0-9]+)(?:\\.[0-9]+)?$/D', (string) $value ) ) { return new WP_Error( 'bh4_invalid_web_geo', 'Invalid coordinate.' ); }
                $filters[ $new ] = (float) $value;
            }
        }
        return BH4_Directory_Filter_Contract::normalize( $filters );
    }
}
