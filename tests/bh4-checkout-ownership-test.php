<?php
/** Isolated checkout ownership contract tests; no WordPress runtime. */
define( 'ABSPATH', __DIR__ . '/' );
require_once dirname( __DIR__ ) . '/plugin/bubba-hub-manager/includes/class-bh4-checkout-ownership.php';
function bh4_owner_assert( $ok, $name ) {
    if ( ! $ok ) { fwrite( STDERR, "FAIL: {$name}\n" ); exit( 1 ); }
    echo "PASS: {$name}\n";
}
$empty = BH4_Checkout_Ownership::evaluate();
bh4_owner_assert( $empty['checkout_owner'] === 'legacy_bubba_hub' && $empty['replacement_enabled'] === false, 'existing checkout remains owner by default' );
bh4_owner_assert( count( $empty['missing_verified_evidence'] ) === 10, 'ten evidence gates required' );
$partial = BH4_Checkout_Ownership::evaluate( array( 'session_mapping_verified' => true, 'single_payment_verified' => 'true' ) );
bh4_owner_assert( count( $partial['missing_verified_evidence'] ) === 9, 'string true is not verification' );
$all = array_fill_keys( $empty['missing_verified_evidence'], true );
$complete = BH4_Checkout_Ownership::evaluate( $all );
bh4_owner_assert( $complete['replacement_eligible_for_review'] === true && $complete['replacement_enabled'] === false, 'all evidence only permits review, never automatic activation' );
bh4_owner_assert( $complete['checkout_owner'] === 'legacy_bubba_hub', 'legacy checkout still owns payment even after checks' );
echo "BH4 checkout ownership fixture tests complete.\n";
