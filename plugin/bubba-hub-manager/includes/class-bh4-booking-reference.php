<?php
/**
 * BH4 passive booking reference proposal.
 *
 * Stable references for future API clients. No booking reads, writes, hooks,
 * payment calls, customer details or automatic migration.
 */
defined( 'ABSPATH' ) || exit;

final class BH4_Booking_Reference {
    /**
     * @param mixed $booking_id Existing BH booking primary key.
     * @param mixed $activity_id Existing BH activity primary key.
     * @param mixed $session_id Existing BH session primary key.
     * @param mixed $event_uid Optional Events Manager event or event:timeslot UID.
     * @return array<string,mixed>|WP_Error
     */
    public static function propose( $booking_id, $activity_id, $session_id, $event_uid = null ) {
        if ( ! current_user_can( 'manage_options' ) ) {
            return new WP_Error( 'bh4_forbidden', 'Administrator permission required.' );
        }
        $booking = self::positive_id( $booking_id );
        $activity = self::positive_id( $activity_id );
        $session = self::positive_id( $session_id );
        if ( null === $booking || null === $activity || null === $session ) {
            return new WP_Error( 'bh4_invalid_booking_reference', 'Positive booking, activity and session IDs required.' );
        }

        $event = null;
        if ( null !== $event_uid ) {
            $event = self::event_uid( $event_uid );
            if ( null === $event ) {
                return new WP_Error( 'bh4_invalid_event_uid', 'Invalid Events Manager event UID.' );
            }
        }

        return array(
            'schema_version' => 1,
            'source' => 'bubba_hub',
            'booking_id' => $booking,
            'activity_id' => $activity,
            'session_id' => $session,
            'events_manager_uid' => $event,
            'verified' => false,
            'write_allowed' => false,
            'payment_owner' => 'legacy_bubba_hub',
            'contains_personal_data' => false,
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
        $id = filter_var( $value, FILTER_VALIDATE_INT, array( 'options' => array( 'min_range' => 1 ) ) );
        return false === $id ? null : $id;
    }
}
