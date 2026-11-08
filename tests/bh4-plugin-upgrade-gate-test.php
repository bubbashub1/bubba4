<?php
define( 'ABSPATH', __DIR__ . '/' );
class WP_Error { public function __construct( $code, $message ) {} }
require_once dirname( __DIR__ ) . '/plugin/bubba-hub-manager/includes/class-bh4-plugin-upgrade-gate.php';
function check_upgrade( $ok, $label ) { if ( ! $ok ) { fwrite( STDERR, "FAIL: {$label}\n" ); exit( 1 ); } echo "PASS: {$label}\n"; }
$r = BH4_Plugin_Upgrade_Gate::assess( array() );
check_upgrade( count( $r['missing_evidence'] ) === 10, 'all upgrade gates pending by default' );
check_upgrade( $r['activation_authorized'] === false && $r['deployment_authorized'] === false, 'never automatically authorize activation or deployment' );
$facts = array_fill_keys( $r['required_evidence'], true );
$complete = BH4_Plugin_Upgrade_Gate::assess( $facts );
check_upgrade( $complete['evidence_complete'] === true && $complete['execution_allowed'] === false, 'complete evidence still cannot execute' );
$facts['no_second_active_copy'] = false;
$blocked = BH4_Plugin_Upgrade_Gate::assess( $facts );
check_upgrade( in_array( 'no_second_active_copy', $blocked['missing_evidence'], true ), 'duplicate plugin activation blocks upgrade' );
check_upgrade( BH4_Plugin_Upgrade_Gate::assess( 'yes' ) instanceof WP_Error, 'invalid input rejected' );
echo "BH4 plugin upgrade gate fixtures complete.\n";
