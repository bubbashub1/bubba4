<?php
/**
 * Passive schema compatibility check for the BH4 directory SELECT contract.
 * Accepts caller-supplied column inventories; never queries or changes a database.
 */
defined( 'ABSPATH' ) || exit;
final class BH4_Directory_Schema_Check {
    public static function inspect( $columns ) {
        if ( ! is_array( $columns ) ) {
            return new WP_Error( 'bh4_schema_inventory_invalid', 'Schema inventory must be an array.' );
        }
        $required = array(
            'activities' => array( 'id', 'title', 'slug', 'status', 'description', 'category', 'is_free', 'price_from', 'age_min_months', 'age_max_months', 'organiser_id' ),
            'organisers' => array( 'id', 'name' ),
            'venues' => array( 'id', 'name', 'town', 'region', 'postcode', 'latitude', 'longitude' ),
            'activity_venues' => array( 'activity_id', 'venue_id' ),
            'sessions' => array( 'activity_id', 'day_of_week', 'term_time_only' ),
        );
        $missing = array();
        foreach ( $required as $table => $names ) {
            $found = $columns[ $table ] ?? null;
            if ( ! is_array( $found ) || array_filter( $found, 'is_string' ) !== $found ) {
                return new WP_Error( 'bh4_schema_inventory_invalid', 'Invalid or missing table inventory.' );
            }
            $missing[ $table ] = array_values( array_diff( $names, $found ) );
        }
        $compatible = true;
        foreach ( $missing as $fields ) {
            if ( $fields ) { $compatible = false; }
        }
        return array(
            'schema_version' => 1,
            'compatible_with_directory_select' => $compatible,
            'missing_columns' => $missing,
            'verified_against_staging' => false,
            'execution_allowed' => false,
            'note' => 'Column presence only; types, indexes, data quality and staging environment must be checked separately.',
        );
    }
}
