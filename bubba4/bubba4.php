<?php
/**
 * Plugin Name: Bubba Hub 4 — Safe Staging Preview
 * Description: Isolated BH4 activation preview. Does not load BH3 modules, alter tables, or handle bookings.
 * Version: 0.1.0-preview
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
        wp_die( 'Not authorised.' );
    }
    $modules = array(
        'Directory and activity pages',
        'Family Hub',
        'Leader portal',
        'Booking checkout and Stripe',
        'Events and ticketing',
        'Support and guidance',
        'Membership integration',
    );
    echo '<div class="wrap"><h1>Bubba Hub 4 — Migration Status</h1>';
    echo '<p>This is an isolated staging preview. No BH4 modules are enabled; existing BH3 remains responsible for site features.</p>';
    echo '<table class="widefat striped"><thead><tr><th>Module</th><th>BH4 status</th></tr></thead><tbody>';
    foreach ( $modules as $module ) {
        echo '<tr><td>' . esc_html( $module ) . '</td><td>Not enabled — awaiting compatibility testing</td></tr>';
    }
    echo '</tbody></table><p>No settings or database records can be changed from this screen.</p></div>';
}
