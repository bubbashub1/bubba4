<?php
/**
 * Public directory card extension from already-authorised activity SELECT rows.
 * Pure formatting; no database access, media resolution, hooks or private contacts.
 */
defined( 'ABSPATH' ) || exit;
final class BH4_Directory_Card_Details {
    public static function project( $row ) {
        if ( ! is_array( $row ) ) { return new WP_Error( 'bh4_invalid_card', 'Invalid activity card row.' ); }
        $min = self::months( $row['age_min_months'] ?? null );
        $max = self::months( $row['age_max_months'] ?? null );
        if ( ( isset( $row['age_min_months'] ) && null === $min ) || ( isset( $row['age_max_months'] ) && null === $max )
            || ( null !== $min && null !== $max && $min > $max ) ) {
            return new WP_Error( 'bh4_invalid_age_range', 'Invalid age range.' );
        }
        $organiser = $row['organiser'] ?? null;
        if ( null !== $organiser && ( ! is_string( $organiser ) || strlen( $organiser ) > 160 ) ) {
            return new WP_Error( 'bh4_invalid_organiser', 'Invalid public organiser name.' );
        }
        $image = $row['image_path'] ?? null;
        if ( null !== $image && ( ! is_string( $image ) || strlen( $image ) > 500
            || ! preg_match( '#^/wp-content/uploads/[A-Za-z0-9_./-]+$#D', $image )
            || strpos( $image, '..' ) !== false ) ) {
            return new WP_Error( 'bh4_invalid_image', 'Unverified or unsafe image path.' );
        }
        return array(
            'schema_version' => 1,
            'type' => 'activity_card_details',
            'age_min_months' => $min,
            'age_max_months' => $max,
            'organiser_name' => is_string( $organiser ) && trim( $organiser ) !== '' ? trim( $organiser ) : null,
            'image_path' => $image,
            'image_url_verified' => false,
            'execution_allowed' => false,
        );
    }
    private static function months( $value ) {
        if ( null === $value || '' === $value ) { return null; }
        if ( ! is_int( $value ) && ! is_string( $value ) ) { return null; }
        if ( is_string( $value ) && ! preg_match( '/^(?:0|[1-9][0-9]*)$/D', $value ) ) { return null; }
        $age = filter_var( $value, FILTER_VALIDATE_INT );
        return false !== $age && $age >= 0 && $age <= 216 ? $age : null;
    }
}
