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
