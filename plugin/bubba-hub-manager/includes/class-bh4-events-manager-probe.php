<?php
/**
 * BH4 Events Manager capability probe.
 *
 * Passive only: not registered by the existing bootstrap. No event or booking
 * reads, writes, payments, hooks, shortcode registrations or external calls.
 */
defined( 'ABSPATH' ) || exit;

final class BH4_Events_Manager_Probe {
    public static function inspect() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return new WP_Error( 'bh4_forbidden', 'Administrator permission required.' );
        }

        if ( ! function_exists( 'get_plugins' ) ) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        $basename = 'events-manager/events-manager.php';
        $plugins = get_plugins();
        $active = (array) get_option( 'active_plugins', array() );
        $network = is_multisite()
            ? array_keys( (array) get_site_option( 'active_sitewide_plugins', array() ) )
            : array();
        $installed = isset( $plugins[ $basename ] );
        $enabled = $installed && ( in_array( $basename, $active, true ) || in_array( $basename, $network, true ) );

        return array(
            'installed' => $installed,
            'active' => $enabled,
            'version' => $installed ? (string) ( $plugins[ $basename ]['Version'] ?? '' ) : null,
            'em_events_class' => class_exists( 'EM_Events', false ),
            'em_bookings_class' => class_exists( 'EM_Bookings', false ),
            'em_event_class' => class_exists( 'EM_Event', false ),
            'em_booking_class' => class_exists( 'EM_Booking', false ),
            'existing_checkout_shortcode' => shortcode_exists( 'bh_parent_booking' ),
            'ready_for_booking_migration' => false,
            'unverified_requirements' => array(
                'multi_child_single_checkout',
                'booking_fee_and_stripe_total',
                'consent_and_guardian_data',
                'leader_payout_and_refunds',
                'confirmation_and_cancellation',
                'session_mapping_and_capacity',
            ),
        );
    }
}
