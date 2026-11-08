<?php
/**
 * Plugin Name: Bubba Hub Manager 3.0
 * Description: Safe, modular foundation for Bubba Hub 3.0. Functional modules are disabled until parity testing.
 * Version: 0.1.0
 * Requires at least: 6.4
 * Requires PHP: 8.0
 * Author: Bubba Hub South West
 * License: GPL-2.0-or-later
 * Text Domain: bubba-hub-manager
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
define( 'BUBBA_HUB_MANAGER_VERSION', '0.1.0' );
define( 'BUBBA_HUB_MANAGER_PATH', plugin_dir_path( __FILE__ ) );
require_once BUBBA_HUB_MANAGER_PATH . 'includes/class-bh-manager.php';
require_once BUBBA_HUB_MANAGER_PATH . 'includes/class-bh-development-tracker.php';
add_action( 'plugins_loaded', array( 'BH_Manager_3', 'boot' ) );

/**
 * Explicitly opt-in, administrator-only read-only recovery diagnostics.
 * Disabled by default; independent of payment creation feature switches.
 */
if ( defined( 'BH4_ENABLE_RECOVERY_ADMIN_REVIEW' ) && BH4_ENABLE_RECOVERY_ADMIN_REVIEW === true
    && defined( 'WP_DEBUG' ) && WP_DEBUG === true ) {
    require_once BUBBA_HUB_MANAGER_PATH . 'includes/class-bh4-reconciliation-access-contract.php';
    require_once BUBBA_HUB_MANAGER_PATH . 'includes/class-bh4-checkout-recovery-admin-review.php';
    add_action( 'admin_menu', array( 'BH4_Checkout_Recovery_Admin_Review', 'register' ) );
    add_action( 'admin_notices', static function () {
        if ( ! is_user_logged_in() || ! current_user_can( 'manage_options' ) ) {
            return;
        }
        $url = wp_nonce_url( admin_url( 'tools.php?page=bh4-checkout-recovery' ), 'bh4_recovery_review' );
        echo '<div class="notice notice-info"><p><a href="' . esc_url( $url ) .
            '">Open read-only Checkout Recovery Review</a></p></div>';
    } );
}
