<?php
/**
 * Plugin Name: Bubba Hub 4 — Safe Staging Preview
 * Description: Isolated BH4 activation preview. Does not load BH3 modules, alter tables, or handle bookings.
 * Version: 0.2.1-preview
 * Requires at least: 6.4
 * Requires PHP: 8.0
 * Author: Bubba Hub South West
 * License: GPL-2.0-or-later
 * Text Domain: bubba4
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
// Deliberately do NOT require the copied BH3 bootstrap. Its class names,
// hooks and constants collide with the existing Bubba Hub Manager plugin.
// This preview is safe to activate alongside BH3 for inspection only.
add_action( 'admin_notices', static function () {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }
    echo '<div class="notice notice-info"><p>' .
        esc_html( 'Bubba Hub 4 safe preview is active. BH3 remains in control; BH4 directory, bookings, family and leader modules are not enabled.' ) .
        '</p></div>';
} );

/**
 * Read-only migration dashboard. Does not include or register BH3 modules.
 * All features remain disabled until isolated staging acceptance tests pass.
 */
add_action( 'admin_menu', static function () {
    add_menu_page(
        'Bubba Hub 4', 'Bubba Hub 4', 'manage_options', 'bubba4-status',
        'bh4_render_preview_status', 'dashicons-admin-tools', 58
    );
} );

function bh4_render_preview_status() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( esc_html__( 'Not authorised.', 'bubba4' ) );
    }
    $plugins = (array) get_option( 'active_plugins', array() );
    $active  = static function ( $slug ) use ( $plugins ) {
        foreach ( $plugins as $plugin ) {
            if ( 0 === strpos( $plugin, $slug . '/' ) ) {
                return true;
            }
        }
        return false;
    };
    $fluent_auth_detected = $active( 'fluent-auth' ) || $active( 'fluentauth' ) || $active( 'fluent-auth-security' );
    $dependencies = array(
        'Bubba Hub Manager 3.0' => $active( 'bubbawp' ) || class_exists( 'BH_Manager_3', false ),
        'Events Manager'        => $active( 'events-manager' ),
        'Ninja Forms'           => $active( 'ninja-forms' ),
        'Simple Membership'     => $active( 'simple-membership' ),
        'FluentAuth'            => $fluent_auth_detected,
        'FluentSMTP'            => $active( 'fluent-smtp' ),
    );
    $modules = array(
        'Directory and activity pages',
        'Family Hub',
        'Leader portal',
        'Booking checkout and Stripe',
        'Events and ticketing',
        'Support and guidance',
        'Membership integration',
    );
    echo '<div class="wrap"><h1>Bubba Hub 4 — Migration Dashboard</h1>';
    echo '<p><strong>Safe preview 0.2.1.</strong> Bubba Hub 3.0 remains in control. This page does not change settings, database records, payments, or bookings.</p>';
    echo '<h2>Installed integration checks</h2>';
    echo '<p>These checks only detect active plugin slugs or the BH3 class. They do not prove integrations are configured or functional.</p>';
    echo '<table class="widefat striped"><thead><tr><th>Integration</th><th>Detected</th></tr></thead><tbody>';
    foreach ( $dependencies as $name => $detected ) {
        echo '<tr><td>' . esc_html( $name ) . '</td><td>' . esc_html( $detected ? 'Detected' : 'Not detected / check manually' ) . '</td></tr>';
    }
    echo '</tbody></table>';
    echo '<h2>Migration modules</h2>';
    echo '<table class="widefat striped"><thead><tr><th>Module</th><th>BH4 status</th></tr></thead><tbody>';
    foreach ( $modules as $module ) {
        echo '<tr><td>' . esc_html( $module ) . '</td><td>Disabled — requires isolated staging acceptance test</td></tr>';
    }
    echo '</tbody></table><p><strong>Next:</strong> verify the plugin entry point and test each module separately before enabling anything.</p></div>';
}
