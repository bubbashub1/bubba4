<?php
/**
 * Plugin Name: Bubba Hub 4 — Safe Staging Preview
 * Description: Isolated BH4 activation preview. Does not load BH3 modules, alter tables, or handle bookings.
 * Version: 0.3.0-preview
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
    echo '<p><strong>Safe preview 0.3.0.</strong> Bubba Hub 3.0 remains in control. This page does not change settings, database records, payments, or bookings.</p>';
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

/**
 * Administrator-only read-only activity inventory. Never changes records.
 * Check both legacy and WordPress-prefixed table names, without guessing columns.
 */
add_action( 'admin_menu', static function () {
    add_submenu_page( 'bubba4-status', 'Activity Inventory', 'Activity Inventory', 'manage_options', 'bubba4-activities', 'bh4_render_activity_inventory' );
} );

function bh4_render_activity_inventory() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( esc_html__( 'Not authorised.', 'bubba4' ) );
    }
    global $wpdb;
    $candidates = array_unique( array( $wpdb->prefix . 'activities', 'bh_activities' ) );
    $found = array();
    foreach ( $candidates as $candidate ) {
        $table = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $candidate ) ) );
        if ( $table === $candidate ) {
            $found[] = $candidate;
        }
    }
    echo '<div class="wrap"><h1>Bubba Hub 4 — Activity Inventory</h1>';
    echo '<p>Read-only inspection of existing activity tables. No records are imported, edited, published, or migrated.</p>';
    if ( ! $found ) {
        echo '<div class="notice notice-warning"><p>No supported activity table was detected. Check the current staging database schema before proceeding.</p></div></div>';
        return;
    }
    foreach ( $found as $table ) {
        $quoted = '`' . str_replace( '`', '``', $table ) . '`';
        $columns = $wpdb->get_col( 'SHOW COLUMNS FROM ' . $quoted, 0 );
        if ( ! is_array( $columns ) ) {
            echo '<p>Unable to inspect ' . esc_html( $table ) . '.</p>';
            continue;
        }
        $has_id = in_array( 'id', $columns, true );
        $has_title = in_array( 'title', $columns, true );
        $has_slug = in_array( 'slug', $columns, true );
        $has_status = in_array( 'status', $columns, true );
        echo '<h2>' . esc_html( $table ) . '</h2>';
        echo '<p>Available listing fields: ' . esc_html( implode( ', ', array_intersect( array( 'id', 'title', 'slug', 'status' ), $columns ) ) ) . '</p>';
        if ( ! $has_id || ! $has_title ) {
            echo '<p>Required columns are missing. Listing is disabled for this table.</p>';
            continue;
        }
        $count = $wpdb->get_var( 'SELECT COUNT(*) FROM ' . $quoted );
        echo '<p>Records: <strong>' . esc_html( is_numeric( $count ) ? number_format_i18n( (int) $count ) : 'Unavailable' ) . '</strong></p>';
        $fields = 'id, title' . ( $has_slug ? ', slug' : '' ) . ( $has_status ? ', status' : '' );
        $rows = $wpdb->get_results( 'SELECT ' . $fields . ' FROM ' . $quoted . ' ORDER BY id DESC LIMIT 20', ARRAY_A );
        if ( ! is_array( $rows ) ) {
            echo '<p>Unable to read records.</p>';
            continue;
        }
        echo '<table class="widefat striped"><thead><tr><th>ID</th><th>Activity</th><th>Slug</th><th>Status</th></tr></thead><tbody>';
        foreach ( $rows as $row ) {
            echo '<tr><td>' . esc_html( (string) $row['id'] ) . '</td><td>' . esc_html( (string) $row['title'] ) . '</td><td>' . esc_html( (string) ( $row['slug'] ?? '—' ) ) . '</td><td>' . esc_html( (string) ( $row['status'] ?? '—' ) ) . '</td></tr>';
        }
        echo '</tbody></table><p>Showing at most 20 most recent IDs; no private or booking fields are queried.</p>';
    }
    echo '</div>';
}
