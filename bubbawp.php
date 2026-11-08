<?php
/**
 * Plugin Name: Bubba Hub 4 — Safe Staging Preview
 * Description: Isolated BH4 activation preview. Does not load BH3 modules, alter tables, or handle bookings.
 * Version: 0.4.0-preview
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
    $search = isset( $_GET['bh4_search'] ) && is_string( $_GET['bh4_search'] ) ? sanitize_text_field( wp_unslash( $_GET['bh4_search'] ) ) : '';
    $status = isset( $_GET['bh4_status'] ) && is_string( $_GET['bh4_status'] ) ? sanitize_key( wp_unslash( $_GET['bh4_status'] ) ) : '';
    $page = isset( $_GET['bh4_page'] ) ? max( 1, min( 10000, absint( $_GET['bh4_page'] ) ) ) : 1;
    $per_page = 20;
    $candidates = array_unique( array( $wpdb->prefix . 'activities', 'bh_activities' ) );
    echo '<div class="wrap"><h1>Bubba Hub 4 — Activity Inventory</h1>';
    echo '<p>Read-only access to existing activity tables. No records are imported, edited, published, or migrated.</p>';
    echo '<form method="get"><input type="hidden" name="page" value="bubba4-activities" />';
    echo '<label for="bh4-search">Search title or slug: </label> <input id="bh4-search" type="search" name="bh4_search" value="' . esc_attr( $search ) . '" /> ';
    echo '<label for="bh4-status">Status: </label> <select id="bh4-status" name="bh4_status">';
    foreach ( array( '' => 'All statuses', 'published' => 'Published', 'draft' => 'Draft', 'pending' => 'Pending', 'private' => 'Private' ) as $value => $label ) {
        echo '<option value="' . esc_attr( $value ) . '"' . selected( $status, $value, false ) . '>' . esc_html( $label ) . '</option>';
    }
    echo '</select> <button class="button button-primary" type="submit">Filter</button></form>';
    $found = false;
    foreach ( $candidates as $candidate ) {
        $table = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $candidate ) ) );
        if ( $table !== $candidate ) {
            continue;
        }
        $found = true;
        $quoted = '`' . str_replace( '`', '``', $table ) . '`';
        $columns = $wpdb->get_col( 'SHOW COLUMNS FROM ' . $quoted, 0 );
        if ( ! is_array( $columns ) || ! in_array( 'id', $columns, true ) || ! in_array( 'title', $columns, true ) ) {
            echo '<p>' . esc_html( $table ) . ': required columns missing or unreadable.</p>';
            continue;
        }
        $has_slug = in_array( 'slug', $columns, true );
        $has_status = in_array( 'status', $columns, true );
        $where = array();
        $params = array();
        if ( $search !== '' ) {
            $like = '%' . $wpdb->esc_like( $search ) . '%';
            $where[] = $has_slug ? '(title LIKE %s OR slug LIKE %s)' : 'title LIKE %s';
            $params[] = $like;
            if ( $has_slug ) {
                $params[] = $like;
            }
        }
        if ( $status !== '' && $has_status ) {
            $where[] = 'status = %s';
            $params[] = $status;
        }
        $clause = $where ? ' WHERE ' . implode( ' AND ', $where ) : '';
        $count_sql = 'SELECT COUNT(*) FROM ' . $quoted . $clause;
        $count = $wpdb->get_var( $params ? $wpdb->prepare( $count_sql, $params ) : $count_sql );
        echo '<h2>' . esc_html( $table ) . '</h2>';
        if ( ! is_numeric( $count ) ) {
            echo '<p>Unable to count matching activities.</p>';
            continue;
        }
        echo '<p>Matching records: <strong>' . esc_html( number_format_i18n( (int) $count ) ) . '</strong></p>';
        $fields = 'id, title' . ( $has_slug ? ', slug' : '' ) . ( $has_status ? ', status' : '' );
        $sql = 'SELECT ' . $fields . ' FROM ' . $quoted . $clause . ' ORDER BY id DESC LIMIT %d OFFSET %d';
        $query_params = array_merge( $params, array( $per_page, ( $page - 1 ) * $per_page ) );
        $rows = $wpdb->get_results( $wpdb->prepare( $sql, $query_params ), ARRAY_A );
        if ( ! is_array( $rows ) ) {
            echo '<p>Unable to read matching activities.</p>';
            continue;
        }
        echo '<table class="widefat striped"><thead><tr><th>ID</th><th>Activity</th><th>Slug</th><th>Status</th></tr></thead><tbody>';
        foreach ( $rows as $row ) {
            echo '<tr><td>' . esc_html( (string) $row['id'] ) . '</td><td>' . esc_html( (string) $row['title'] ) . '</td><td>' . esc_html( (string) ( $row['slug'] ?? '—' ) ) . '</td><td>' . esc_html( (string) ( $row['status'] ?? '—' ) ) . '</td></tr>';
        }
        echo '</tbody></table>';
        $pages = (int) ceil( (int) $count / $per_page );
        if ( $pages > 1 ) {
            echo '<p>Page ' . esc_html( (string) $page ) . ' of ' . esc_html( (string) $pages ) . ' ';
            foreach ( array( 'Previous' => $page - 1, 'Next' => $page + 1 ) as $label => $target ) {
                if ( $target >= 1 && $target <= $pages ) {
                    $url = add_query_arg( array( 'page' => 'bubba4-activities', 'bh4_page' => $target, 'bh4_search' => $search, 'bh4_status' => $status ), admin_url( 'admin.php' ) );
                    echo ' <a class="button" href="' . esc_url( $url ) . '">' . esc_html( $label ) . '</a> ';
                }
            }
            echo '</p>';
        }
    }
    if ( ! $found ) {
        echo '<div class="notice notice-warning"><p>No supported activity table detected.</p></div>';
    }
    echo '</div>';
}
