<?php
/**
 * BH4 public activity/session response shape — pure formatter only.
 *
 * Does not register REST routes, query tables, or alter any records.
 * Caller must supply an already authorised, public-safe activity projection.
 */
defined( 'ABSPATH' ) || exit;

final class BH4_Activity_API_Contract {
    public const VERSION = 1;

    /**
     * @param array<string,mixed> $activity Public-safe scalar fields.
     * @return array<string,mixed>|WP_Error
     */
    public static function format_activity( $activity ) {
        if ( ! is_array( $activity ) ) {
            return new WP_Error( 'bh4_invalid_activity', 'Activity projection must be an array.' );
        }
        $id = self::id( $activity['id'] ?? null );
        $title = $activity['title'] ?? null;
        $slug = $activity['slug'] ?? null;
        if ( null === $id || ! is_string( $title ) || '' === trim( $title )
            || ! is_string( $slug ) || ! preg_match( '/^[a-z0-9]+(?:-[a-z0-9]+)*$/D', $slug ) ) {
            return new WP_Error( 'bh4_invalid_activity', 'Missing or invalid public activity fields.' );
        }
        return array(
            'schema_version' => self::VERSION,
            'type' => 'activity',
            'id' => $id,
            'title' => trim( $title ),
            'slug' => $slug,
            'category' => self::optional_text( $activity['category'] ?? null ),
            'town' => self::optional_text( $activity['town'] ?? null ),
            'price_label' => self::optional_text( $activity['price_label'] ?? null ),
        );
    }

    /**
     * @param array<string,mixed> $session Public-safe scalar fields.
     * @return array<string,mixed>|WP_Error
     */
    public static function format_session( $session ) {
        if ( ! is_array( $session ) ) {
            return new WP_Error( 'bh4_invalid_session', 'Session projection must be an array.' );
        }
        $id = self::id( $session['id'] ?? null );
        $activity_id = self::id( $session['activity_id'] ?? null );
        $date = $session['date'] ?? null;
        $time = $session['start_time'] ?? null;
        if ( null === $id || null === $activity_id || ! self::valid_date( $date )
            || ! is_string( $time ) || ! preg_match( '/^(?:[01][0-9]|2[0-3]):[0-5][0-9]$/D', $time ) ) {
            return new WP_Error( 'bh4_invalid_session', 'Missing or invalid public session fields.' );
        }
        return array(
            'schema_version' => self::VERSION,
            'type' => 'session_occurrence',
            'id' => $id,
            'activity_id' => $activity_id,
            'date' => $date,
            'start_time' => $time,
            'timezone' => 'Europe/London',
            'availability' => 'unknown',
        );
    }

    private static function optional_text( $value ) {
        return is_string( $value ) && '' !== trim( $value ) ? trim( $value ) : null;
    }

    private static function id( $value ) {
        if ( ! is_int( $value ) && ! is_string( $value ) ) { return null; }
        if ( is_string( $value ) && ! preg_match( '/^[1-9][0-9]*$/D', $value ) ) { return null; }
        $id = filter_var( $value, FILTER_VALIDATE_INT, array( 'options' => array( 'min_range' => 1 ) ) );
        return false === $id ? null : $id;
    }

    private static function valid_date( $date ) {
        if ( ! is_string( $date ) || ! preg_match( '/^\\d{4}-\\d{2}-\\d{2}$/D', $date ) ) { return false; }
        $parsed = DateTimeImmutable::createFromFormat( '!Y-m-d', $date, new DateTimeZone( 'Europe/London' ) );
        return $parsed && $parsed->format( 'Y-m-d' ) === $date;
    }
}
