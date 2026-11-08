<?php
define( 'ABSPATH', __DIR__ . '/' );
class WP_Error { public function __construct( $code, $message ) {} }
require_once dirname( __DIR__ ) . '/plugin/bubba-hub-manager/includes/class-bh4-directory-parity.php';
function check_parity( $ok, $label ) { if ( ! $ok ) { fwrite( STDERR, "FAIL: {$label}\n" ); exit( 1 ); } echo "PASS: {$label}\n"; }
$legacy = array( array( 'id' => '12', 'title' => 'Baby' ), array( 'id' => 8, 'title' => 'Toddler' ) );
$same = BH4_Directory_Parity::compare( $legacy, array( array( 'id' => 12 ), array( 'id' => '8' ) ) );
check_parity( $same['matches'] === true && $same['staging_verified'] === false, 'identical ordered ids' );
$reordered = BH4_Directory_Parity::compare( $legacy, array( array( 'id' => 8 ), array( 'id' => 12 ) ) );
check_parity( $reordered['same_membership'] === true && $reordered['same_order'] === false, 'ordering difference detected' );
$changed = BH4_Directory_Parity::compare( $legacy, array( array( 'id' => 8 ), array( 'id' => 99 ) ) );
check_parity( $changed['missing_from_candidate'] === array( 12 ) && $changed['unexpected_in_candidate'] === array( 99 ), 'missing and extra ids identified' );
check_parity( BH4_Directory_Parity::compare( $legacy, array( array( 'id' => 8 ), array( 'id' => 8 ) ) ) instanceof WP_Error, 'duplicates rejected' );
check_parity( BH4_Directory_Parity::compare( $legacy, 'bad' ) instanceof WP_Error, 'invalid result set rejected' );
echo "BH4 directory parity fixtures complete.\n";
