<?php
/**
 * Compares caller-supplied public activity fields after matching IDs.
 * Pure fixture tool; no database reads, personal data or hooks.
 */
defined( 'ABSPATH' ) || exit;
final class BH4_Directory_Field_Parity {
    public static function compare( $legacy, $candidate ) {
        $left = self::index( $legacy );
        if ( $left instanceof WP_Error ) { return $left; }
        $right = self::index( $candidate );
        if ( $right instanceof WP_Error ) { return $right; }
        $fields = array( 'title', 'slug', 'category', 'town', 'price_label' );
        $differences = array();
        foreach ( $left as $id => $record ) {
            if ( ! isset( $right[ $id ] ) ) { continue; }
            foreach ( $fields as $field ) {
                $before = self::text( $record[ $field ] ?? null );
                $after = self::text( $right[ $id ][ $field ] ?? null );
                if ( $before !== $after ) {
                    $differences[] = array( 'id' => (int) $id, 'field' => $field, 'legacy' => $before, 'candidate' => $after );
                }
            }
        }
        return array(
            'schema_version' => 1,
            'matching_fields' => count( $differences ) === 0,
            'differences' => $differences,
            'compared_activity_ids' => array_values( array_map( 'intval', array_intersect( array_keys( $left ), array_keys( $right ) ) ) ),
            'missing_from_candidate' => array_values( array_map( 'intval', array_diff( array_keys( $left ), array_keys( $right ) ) ) ),
            'unexpected_in_candidate' => array_values( array_map( 'intval', array_diff( array_keys( $right ), array_keys( $left ) ) ) ),
            'staging_verified' => false,
            'execution_allowed' => false,
        );
    }
    private static function index( $rows ) {
        if ( ! is_array( $rows ) || count( $rows ) > 101 ) { return new WP_Error( 'bh4_invalid_field_parity', 'Invalid result set.' ); }
        $indexed = array();
        foreach ( $rows as $row ) {
            if ( ! is_array( $row ) || ! isset( $row['id'] ) || ( ! is_int( $row['id'] ) && ! is_string( $row['id'] ) ) ) {
                return new WP_Error( 'bh4_invalid_field_parity', 'Invalid activity row.' );
            }
            $id = filter_var( $row['id'], FILTER_VALIDATE_INT, array( 'options' => array( 'min_range' => 1 ) ) );
            if ( false === $id || isset( $indexed[ $id ] ) ) { return new WP_Error( 'bh4_invalid_field_parity', 'Invalid or duplicate ID.' ); }
            $indexed[ $id ] = $row;
        }
        return $indexed;
    }
    private static function text( $value ) {
        return is_string( $value ) ? trim( $value ) : null;
    }
}
