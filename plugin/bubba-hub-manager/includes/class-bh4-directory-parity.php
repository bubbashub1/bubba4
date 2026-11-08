<?php
/**
 * Compare two caller-supplied public directory result sets without DB access.
 * Safe for synthetic fixtures; does not claim real staging parity.
 */
defined( 'ABSPATH' ) || exit;
final class BH4_Directory_Parity {
    public static function compare( $legacy, $candidate ) {
        if ( ! is_array( $legacy ) || ! is_array( $candidate ) ) {
            return new WP_Error( 'bh4_invalid_parity_input', 'Both result sets must be arrays.' );
        }
        $left = self::ids( $legacy );
        if ( $left instanceof WP_Error ) { return $left; }
        $right = self::ids( $candidate );
        if ( $right instanceof WP_Error ) { return $right; }
        return array(
            'schema_version' => 1,
            'matches' => $left === $right,
            'legacy_ids' => $left,
            'candidate_ids' => $right,
            'missing_from_candidate' => array_values( array_diff( $left, $right ) ),
            'unexpected_in_candidate' => array_values( array_diff( $right, $left ) ),
            'same_membership' => count( array_diff( $left, $right ) ) === 0 && count( array_diff( $right, $left ) ) === 0,
            'same_order' => $left === $right,
            'staging_verified' => false,
            'execution_allowed' => false,
        );
    }
    private static function ids( $rows ) {
        if ( count( $rows ) > 101 ) {
            return new WP_Error( 'bh4_parity_limit', 'Too many rows for parity fixture.' );
        }
        $ids = array();
        foreach ( $rows as $row ) {
            if ( ! is_array( $row ) || ! array_key_exists( 'id', $row ) ) {
                return new WP_Error( 'bh4_invalid_parity_row', 'Missing activity ID.' );
            }
            $value = $row['id'];
            if ( ! is_int( $value ) && ! is_string( $value ) ) {
                return new WP_Error( 'bh4_invalid_parity_row', 'Invalid activity ID.' );
            }
            $id = filter_var( $value, FILTER_VALIDATE_INT, array( 'options' => array( 'min_range' => 1 ) ) );
            if ( false === $id || isset( $ids[ $id ] ) ) {
                return new WP_Error( 'bh4_invalid_parity_row', 'Invalid or duplicate activity ID.' );
            }
            $ids[ $id ] = true;
        }
        return array_keys( $ids );
    }
}
