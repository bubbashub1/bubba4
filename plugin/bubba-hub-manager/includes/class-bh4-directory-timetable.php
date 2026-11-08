<?php
/**
 * Public, weekly timetable preview from already-authorised legacy session rows.
 * Pure formatting only. No bookings, database calls, or future availability claims.
 */
defined( 'ABSPATH' ) || exit;
final class BH4_Directory_Timetable {
    public static function project( $rows, $activity_ids ) {
        if ( ! is_array( $rows ) || ! is_array( $activity_ids ) || count( $rows ) > 1000 || count( $activity_ids ) > 24 ) {
            return new WP_Error( 'bh4_invalid_timetable', 'Invalid timetable input.' );
        }
        $allowed = array();
        foreach ( $activity_ids as $id ) {
            $parsed = self::id( $id );
            if ( null === $parsed ) { return new WP_Error( 'bh4_invalid_activity_id', 'Invalid activity ID.' ); }
            $allowed[ $parsed ] = true;
        }
        $grouped = array();
        foreach ( $rows as $row ) {
            if ( ! is_array( $row ) ) { return new WP_Error( 'bh4_invalid_session', 'Invalid session row.' ); }
            $id = self::id( $row['activity_id'] ?? null );
            $day = $row['day_of_week'] ?? null;
            $time = $row['start_time'] ?? null;
            if ( null === $id || ! isset( $allowed[ $id ] ) || ! is_numeric( $day ) || (int) $day < 1 || (int) $day > 7
                || ! is_string( $time ) || ! preg_match( '/^(?:[01][0-9]|2[0-3]):[0-5][0-9](?::[0-5][0-9])?$/D', $time ) ) {
                return new WP_Error( 'bh4_invalid_session', 'Invalid public weekly session.' );
            }
            // Caller must pass only scheduled weekly rows from the existing published-only directory query.
            if ( isset( $row['status'] ) && $row['status'] !== 'scheduled' ) { return new WP_Error( 'bh4_unscheduled_session', 'Unscheduled session.' ); }
            if ( isset( $row['frequency'] ) && $row['frequency'] !== 'weekly' ) { return new WP_Error( 'bh4_nonweekly_session', 'Nonweekly session.' ); }
            $entry = array(
                'day_of_week' => (int) $day,
                'start_time' => substr( $time, 0, 5 ),
                'term_time_only' => isset( $row['term_time_only'] ) && (string) $row['term_time_only'] === '1',
                'timezone' => 'Europe/London',
            );
            $key = $entry['day_of_week'] . ':' . $entry['start_time'];
            $grouped[ $id ][ $key ] = $entry;
        }
        foreach ( $grouped as &$entries ) {
            $entries = array_values( $entries );
            usort( $entries, static function( $a, $b ) {
                return $a['day_of_week'] <=> $b['day_of_week'] ?: strcmp( $a['start_time'], $b['start_time'] );
            } );
        }
        unset( $entries );
        return array(
            'schema_version' => 1,
            'type' => 'weekly_timetable_preview',
            'by_activity' => $grouped,
            'availability' => 'not_checked',
            'execution_allowed' => false,
        );
    }
    private static function id( $value ) {
        if ( ! is_int( $value ) && ! is_string( $value ) ) { return null; }
        if ( is_string( $value ) && ! preg_match( '/^[1-9][0-9]*$/D', $value ) ) { return null; }
        $id = filter_var( $value, FILTER_VALIDATE_INT, array( 'options' => array( 'min_range' => 1 ) ) );
        return false === $id ? null : $id;
    }
}
