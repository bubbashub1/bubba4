<?php
define( 'ABSPATH', __DIR__ . '/' );
class WP_Error { public function __construct( $code, $message ) {} }
require_once dirname( __DIR__ ) . '/plugin/bubba-hub-manager/includes/class-bh4-directory-map-markers.php';
function check_map( $ok, $label ) { if ( ! $ok ) { fwrite( STDERR, "FAIL: {$label}\n" ); exit( 1 ); } echo "PASS: {$label}\n"; }
$rows = array(
 array( 'activity_id' => 12, 'venue_id' => 5, 'latitude' => '50.46', 'longitude' => '-3.53', 'private_address' => 'do not expose' ),
 array( 'activity_id' => 12, 'venue_id' => 5, 'latitude' => '50.46', 'longitude' => '-3.53' ),
 array( 'activity_id' => 12, 'venue_id' => 6, 'latitude' => null, 'longitude' => null ),
);
$r = BH4_Directory_Map_Markers::project( $rows, array( 12 ) );
check_map( $r['returned'] === 1, 'duplicate markers removed and missing coordinates skipped' );
check_map( $r['items'][0]['latitude'] === 50.46 && $r['items'][0]['longitude'] === -3.53, 'valid venue coordinates retained' );
check_map( ! isset( $r['items'][0]['private_address'] ) && $r['staging_verified'] === false, 'private fields excluded and unverified location flagged' );
check_map( BH4_Directory_Map_Markers::project( array( array( 'activity_id' => 99, 'venue_id' => 5, 'latitude' => 50, 'longitude' => -3 ) ), array( 12 ) ) instanceof WP_Error, 'unrelated activity rejected' );
check_map( BH4_Directory_Map_Markers::project( array( array( 'activity_id' => 12, 'venue_id' => 5, 'latitude' => 999, 'longitude' => 0 ) ), array( 12 ) )['returned'] === 0, 'invalid coordinates omitted' );
echo "BH4 directory map marker fixtures complete.\n";
