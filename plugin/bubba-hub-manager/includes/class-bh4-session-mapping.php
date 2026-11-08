<?php
/**
 * BH4 passive activity/session mapping proposal.
 *
 * Builds a candidate mapping from caller-supplied scalar identifiers only.
 * It does not query activities, sessions, Events Manager, or personal records.
 * No hooks, writes, event creation, booking creation or payments.
 */
defined( 'ABSPATH' ) || exit;

final class BH4_Session_Mapping {
    /**
     * @param mixed $activity_id Existing BH activity ID.
     * @param mixed $session_id Existing BH session ID.
     * @param mixed $event_id Optional, independently verified EM event ID.
     * @return array<string,mixed>|WP_Error
     */
    public static function propose( $activity_id, $session_id, $event_id = null ) {
        if ( ! current_user_can( 'manage_options' ) ) {
            return new WP_Error( 'bh4_forbidden', 'Administrator permission required.' );
        }

        $activity = self::positive_id( $activity_id );
        $session = self::positive_id( $session_id );
        if ( null === $activity || null === $session ) {
            return new WP_Error( 'bh4_invalid_ids', 'Activity and session must have positive integer IDs.' );
        }

        $event = null;
        if ( null !== $event_id ) {
            $event = self::event_uid( $event_id );
            if ( null === $event ) {
                return new WP_Error( 'bh4_invalid_event_id', 'Event UID must be a positive integer ID or event:timeslot UID.' );
            }
        }

        return array(
            'bh_activity_id' => $activity,
            'bh_session_id' => $session,
            'em_event_id' => $event,
            'status' => 'unverified',
            'event_creation_allowed' => false,
            'booking_migration_allowed' => false,
            'requires' => array(
                'source_activity_exists',
                'source_session_belongs_to_activity',
                'event_exists_and_matches_session',
                'schedule_timezone_and_recurrence_match',
                'capacity_and_price_match',
            ),
        );
    }

    private static function event_uid( $value ) {
        if ( is_string( $value ) && preg_match( '/^([1-9][0-9]*):([1-9][0-9]*)$/D', $value, $parts ) ) {
            $event = self::positive_id( $parts[1] );
            $slot = self::positive_id( $parts[2] );
            return null !== $event && null !== $slot ? $event . ':' . $slot : null;
        }
        return self::positive_id( $value );
    }

    private static function positive_id( $value ) {
        if ( ! is_int( $value ) && ! is_string( $value ) ) {
            return null;
        }
        if ( is_string( $value ) && ! preg_match( '/^[1-9][0-9]*$/D', $value ) ) {
            return null;
        }
        $number = filter_var( $value, FILTER_VALIDATE_INT, array( 'options' => array( 'min_range' => 1 ) ) );
        return false === $number ? null : $number;
    }
}
