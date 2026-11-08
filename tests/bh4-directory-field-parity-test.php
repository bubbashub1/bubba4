<?php
define( 'ABSPATH', __DIR__ . '/' );
class WP_Error { public function __construct( $code, $message ) {} }
require_once dirname( __DIR__ ) . '/plugin/bubba-hub-manager/includes/class-bh4-directory-field-parity.php';
function check_fields( $ok, $label ) { if ( ! $ok ) { fwrite( STDERR, "FAIL: {$label}\n" ); exit( 1 ); } echo "PASS: {$label}\n"; }
$legacy = array( array( 'id' => 10, 'title' => 'Baby Sensory', 'slug' => 'baby-sensory', 'category' => 'Baby', 'town' => 'Torquay', 'price_label' => 'From £5.00', 'parent_email' => 'private' ) );
$identical = BH4_Directory_Field_Parity::compare( $legacy, $legacy );
check_fields( $identical['matching_fields'] === true && $identical['staging_verified'] === false, 'matching public fields' );
$changed = $legacy;
$changed[0]['town'] = 'Paignton';
$changed[0]['price_label'] = 'Free';
$diff = BH4_Directory_Field_Parity::compare( $legacy, $changed );
check_fields( count( $diff['differences'] ) === 2, 'town and price differences found' );
check_fields( $diff['differences'][0]['field'] === 'town' && $diff['differences'][1]['field'] === 'price_label', 'differences identified by field' );
check_fields( ! isset( $diff['differences'][0]['parent_email'] ), 'private fields not reported' );
$extra = BH4_Directory_Field_Parity::compare( $legacy, array( array( 'id' => 20, 'title' => 'Other' ) ) );
check_fields( $extra['missing_from_candidate'] === array( 10 ) && $extra['unexpected_in_candidate'] === array( 20 ), 'missing and unexpected IDs' );
check_fields( BH4_Directory_Field_Parity::compare( $legacy, array( $legacy[0], $legacy[0] ) ) instanceof WP_Error, 'duplicate IDs rejected' );
echo "BH4 directory field parity fixtures complete.\n";
