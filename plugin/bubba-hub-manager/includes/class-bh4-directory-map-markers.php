<?php
/**
 * Pure public map marker projection from trusted published activity/venue rows.
 * No geocoding, database access, tracking, routes or user coordinates.
 */
defined( 'ABSPATH' ) || exit;
final class BH4_Directory_Map_Markers {
    public static function project( $rows, $allowed_activity_ids ) {
        if ( ! is_array( $rows ) || ! is_array( $allowed_activity_ids ) || count( $rows ) > 500 || count( $allowed_activity_ids ) > 24 ) {
            return new WP_Error( 'bh4_invalid_map_rows', 'Invalid map input.' );
        }
        $allowed = array();
        foreach ( $allowed_activity_ids as $id ) {
            $id = self::positive_id( $id );
            if ( null === $id ) { return new WP_Error( 'bh4_invalid_map_activity', 'Invalid activity ID.' ); }
            $allowed[ $id ] = true;
        }
        $markers = array();
        $seen = array();
        foreach ( $rows as $row ) {
            if ( ! is_array( $row ) ) { return new WP_Error( 'bh4_invalid_map_row', 'Invalid map row.' ); }
            $id = self::positive_id( $row['activity_id'] ?? null );
            $venue = self::positive_id( $row['venue_id'] ?? null );
            if ( null === $id || null === $venue || ! isset( $allowed[ $id ] ) ) {
                return new WP_Error( 'bh4_invalid_map_link', 'Unrelated activity or invalid venue.' );
            }
            $lat = self::coordinate( $row['latitude'] ?? null, 90 );
            $lng = self::coordinate( $row['longitude'] ?? null, 180 );
            if ( null === $lat || null === $lng ) { continue; } // Missing/invalid venue coordinates: no fabricated marker.
            $key = $id . ':' . $venue;
            if ( isset( $seen[ $key ] ) ) { continue; }
            $seen[ $key ] = true;
            $markers[] = array(
                'activity_id' => $id,
                'venue_id' => $venue,
                'latitude' => $lat,
                'longitude' => $lng,
                'location_accuracy' => 'unverified',
            );
        }
        return array(
            'schema_version' => 1,
            'type' => 'public_activity_venue_markers',
            'items' => $markers,
            'returned' => count( $markers ),
            'staging_verified' => false,
            'execution_allowed' => false,
        );
    }
    private static function positive_id( $value ) {
        if ( ! is_int( $value ) && ! is_string( $value ) ) { return null; }
        if ( is_string( $value ) && ! preg_match( '/^[1-9][0-9]*$/D', $value ) ) { return null; }
        $parsed = filter_var( $value, FILTER_VALIDATE_INT, array( 'options' => array( 'min_range' => 1 ) ) );
        return false === $parsed ? null : $parsed;
    }
    private static function coordinate( $value, $bound ) {
        if ( ! is_int( $value ) && ! is_float( $value ) && ! is_string( $value ) ) { return null; }
        if ( is_string( $value ) && ! preg_match( '/^-?(?:[0-9]+)(?:\\.[0-9]+)?$/D', $value ) ) { return null; }
        $number = (float) $value;
        return is_finite( $number ) && abs( $number ) <= $bound ? $number : null;
    }
}
