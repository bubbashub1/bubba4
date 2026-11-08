<?php
/**
 * Pure metadata-level gate for controlled same-plugin replacement.
 * Does not inspect WordPress state, modify plugins, or authorize deployment.
 */
defined( 'ABSPATH' ) || exit;
final class BH4_Plugin_Upgrade_Gate {
    public static function assess( $facts ) {
        if ( ! is_array( $facts ) ) { return new WP_Error( 'bh4_invalid_upgrade_facts', 'Upgrade facts must be an array.' ); }
        $required = array(
            'same_plugin_entrypoint',
            'no_second_active_copy',
            'staging_backup_verified',
            'rollback_package_verified',
            'schema_parity_verified',
            'directory_parity_verified',
            'auth_membership_verified',
            'booking_checkout_unchanged',
            'staging_smoke_tests_passed',
            'explicit_deployment_approval',
        );
        $missing = array();
        foreach ( $required as $key ) {
            if ( ! array_key_exists( $key, $facts ) || true !== $facts[ $key ] ) { $missing[] = $key; }
        }
        return array(
            'schema_version' => 1,
            'strategy' => 'replace_same_plugin_identity',
            'required_evidence' => $required,
            'missing_evidence' => $missing,
            'evidence_complete' => count( $missing ) === 0,
            // Even a complete checklist cannot activate a plugin automatically.
            'activation_authorized' => false,
            'deployment_authorized' => false,
            'execution_allowed' => false,
        );
    }
}
