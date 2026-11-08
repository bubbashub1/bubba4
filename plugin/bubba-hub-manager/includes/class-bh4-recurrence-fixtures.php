<?php
/**
 * BH4 pure calendar occurrence fixtures; no WP or Events Manager dependencies.
 *
 * Generates candidate local dates only. Does not create events or bookings.
 */
defined( 'ABSPATH' ) || exit;

final class BH4_Recurrence_Fixtures {
    /**
     * @param string $start ISO local date YYYY-MM-DD
     * @param string $end ISO local date YYYY-MM-DD
     * @param int $weekday ISO weekday (1=Mon, 7=Sun)
     * @param array<int,string> $excluded_dates Explicit holiday/cancellation dates.
     * @return array<int,string>|WP_Error
     */
    public static function weekly_dates( $start, $end, $weekday, $excluded_dates = array() ) {
        if ( ! is_string( $start ) || ! is_string( $end ) || ! is_int( $weekday )
            || $weekday < 1 || $weekday > 7 || ! is_array( $excluded_dates ) ) {
            return new WP_Error( 'bh4_invalid_recurrence', 'Invalid recurrence input.' );
        }
        $first = self::date( $start );
        $last = self::date( $end );
        if ( null === $first || null === $last || $first > $last ) {
            return new WP_Error( 'bh4_invalid_range', 'Invalid date range.' );
        }
        if ( $first->diff( $last )->days > 366 ) {
            return new WP_Error( 'bh4_range_too_large', 'Maximum range is 366 days.' );
        }
        $skip = array();
        foreach ( $excluded_dates as $date ) {
            if ( ! is_string( $date ) || null === self::date( $date ) ) {
                return new WP_Error( 'bh4_invalid_exclusion', 'Invalid excluded date.' );
            }
            $skip[ $date ] = true;
        }
        $result = array();
        for ( $day = $first; $day <= $last; $day = $day->modify( '+1 day' ) ) {
            $date = $day->format( 'Y-m-d' );
            if ( (int) $day->format( 'N' ) === $weekday && ! isset( $skip[ $date ] ) ) {
                $result[] = $date;
            }
        }
        return $result;
    }

    private static function date( $date ) {
        if ( ! preg_match( '/^\\d{4}-\\d{2}-\\d{2}$/D', $date ) ) {
            return null;
        }
        $parsed = DateTimeImmutable::createFromFormat( '!Y-m-d', $date, new DateTimeZone( 'Europe/London' ) );
        return $parsed && $parsed->format( 'Y-m-d' ) === $date ? $parsed : null;
    }
}
