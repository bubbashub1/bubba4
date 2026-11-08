<?php
/**
 * Bubba Hub 4.0 — read-only booking compatibility contract.
 *
 * No hooks, bookings, payments, database writes or plugin bootstrapping.
 * Intended for a future isolated integration harness.
 */
defined( 'ABSPATH' ) || exit;

final class BH4_Booking_Compatibility {
    /**
     * A contract checklist, NOT a live integration result.
     *
     * @return array<string,mixed>|WP_Error
     */
    public static function inspect() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return new WP_Error( 'bh4_forbidden', 'Administrator permission required.' );
        }

        $requirements = array(
            'activity_and_session_selection',
            'multiple_children_single_booking',
            'guest_and_member_checkout',
            'guardian_details_and_consents',
            'one_stripe_payment',
            'single_displayed_booking_fee',
            'leader_receives_advertised_price',
            'capacity_and_duplicate_prevention',
            'confirmation_and_cancellation',
            'refunds_and_failed_payments',
            'leader_dashboard_visibility',
            'privacy_and_retention',
        );

        $checks = array();
        foreach ( $requirements as $key ) {
            $checks[ $key ] = array(
                'status' => 'not_tested',
                'evidence' => null,
            );
        }

        return array(
            'mode' => 'read_only',
            'legacy_checkout_registered' => shortcode_exists( 'bh_parent_booking' ),
            'legacy_checkout_preserved' => true,
            'requirements' => $checks,
            'ready_to_replace_legacy_checkout' => false,
        );
    }

    /**
     * Return only requirements with verified evidence, without assuming
     * success from the presence of a plugin or shortcode.
     */
    public static function summarize( $checks ) {
        if ( ! is_array( $checks ) ) {
            return array( 'passed' => 0, 'failed' => 0, 'not_tested' => 0 );
        }

        $totals = array( 'passed' => 0, 'failed' => 0, 'not_tested' => 0 );
        foreach ( $checks as $check ) {
            $status = is_array( $check ) ? ( $check['status'] ?? 'not_tested' ) : 'not_tested';
            if ( ! in_array( $status, array( 'passed', 'failed', 'not_tested' ), true ) ) {
                $status = 'not_tested';
            }
            ++$totals[ $status ];
        }
        return $totals;
    }
}
