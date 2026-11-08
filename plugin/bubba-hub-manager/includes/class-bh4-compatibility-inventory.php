<?php
/**
 * Bubba Hub 4.0 — passive plugin compatibility inventory.
 *
 * This file is intentionally NOT included by the BH3 bootstrap. It must be
 * loaded explicitly by a future administrator-only diagnostics screen.
 * No writes, migrations, plugin activation or external calls.
 */
defined( 'ABSPATH' ) || exit;

final class BH4_Compatibility_Inventory {
    /**
     * Return read-only plugin information. Requires an administrator context.
     *
     * @return array<string,mixed>|WP_Error
     */
    public static function inspect() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return new WP_Error( 'bh4_forbidden', 'Administrator permission required.' );
        }

        if ( ! function_exists( 'get_plugins' ) ) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        $installed = get_plugins();
        $active    = (array) get_option( 'active_plugins', array() );
        $network   = is_multisite() ? array_keys( (array) get_site_option( 'active_sitewide_plugins', array() ) ) : array();

        $candidates = array(
            'simple_membership' => array( 'simple-membership/simple-wp-membership.php' ),
            'events_manager'    => array( 'events-manager/events-manager.php' ),
            'ninja_forms'       => array( 'ninja-forms/ninja-forms.php' ),
            'fluent_forms'      => array( 'fluentform/fluentform.php' ),
            'fluent_auth'       => array( 'fluent-auth/fluent-auth.php', 'fluentauth/fluentauth.php' ),
            'fluent_smtp'       => array( 'fluent-smtp/fluent-smtp.php' ),
            'action_scheduler'  => array( 'action-scheduler/action-scheduler.php' ),
        );

        $result = array();
        foreach ( $candidates as $key => $possible_paths ) {
            $match = null;
            foreach ( $possible_paths as $path ) {
                if ( isset( $installed[ $path ] ) ) {
                    $match = $path;
                    break;
                }
            }
            $result[ $key ] = array(
                'detected' => null !== $match,
                'active'   => null !== $match && ( in_array( $match, $active, true ) || in_array( $match, $network, true ) ),
                'version'  => null !== $match ? (string) ( $installed[ $match ]['Version'] ?? '' ) : null,
            );
        }

        // NiroHelp plugin basename is intentionally not assumed. Detect it
        // through a confirmed basename once the actual plugin is evaluated.
        $result['nirohelp'] = array(
            'detected' => null,
            'active'   => null,
            'version'  => null,
            'note'     => 'Plugin basename not yet verified; no assumption made.',
        );

        return array(
            'plugins'                    => $result,
            'membership_payment_shortcode' => shortcode_exists( 'swpm_payment_button' ),
            'existing_booking_shortcode' => shortcode_exists( 'bh_parent_booking' ),
            'existing_family_shortcode'  => shortcode_exists( 'bh_family_hub' ),
            'existing_leader_shortcode'  => shortcode_exists( 'bh_leader_dashboard' ),
            'php_version'                => PHP_VERSION,
            'wp_version'                 => get_bloginfo( 'version' ),
        );
    }
}
