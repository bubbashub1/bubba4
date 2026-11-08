<?php
define( 'ABSPATH', __DIR__ . '/' );
class WP_Error { public function __construct( $code, $message ) {} }
require_once dirname( __DIR__ ) . '/plugin/bubba-hub-manager/includes/class-bh4-directory-filter-contract.php';
require_once dirname( __DIR__ ) . '/plugin/bubba-hub-manager/includes/class-bh4-directory-web-filters.php';
function check_web( $ok, $label ) { if ( ! $ok ) { fwrite( STDERR, "FAIL: {$label}\n" ); exit( 1 ); } echo "PASS: {$label}\n"; }
$f = BH4_Directory_Web_Filters::from_query( array( 'bh_age' => '0', 'bh_free' => '1', 'bh_town' => 'Torquay', 'bh_lat' => '50.46', 'bh_lng' => '-3.53', 'bh_radius' => '10' ) );
check_web( is_array( $f ) && $f['age_months'] === 0 && $f['free_only'] === true, 'newborn and free only' );
check_web( $f['town'] === 'Torquay' && $f['longitude'] === -3.53 && $f['radius_miles'] === 10, 'town and request-only coordinates' );
check_web( BH4_Directory_Web_Filters::from_query( array( 'bh_lat' => '50.46' ) ) instanceof WP_Error, 'partial coordinates rejected' );
check_web( BH4_Directory_Web_Filters::from_query( array( 'bh_free' => 'yes' ) ) instanceof WP_Error, 'invalid boolean rejected' );
check_web( BH4_Directory_Web_Filters::from_query( array( 'bh_page' => '-2' ) ) instanceof WP_Error, 'invalid page rejected' );
check_web( BH4_Directory_Web_Filters::from_query( array( 'bh_lat' => 'INF', 'bh_lng' => '0' ) ) instanceof WP_Error, 'nonfinite coordinate rejected' );
echo "BH4 directory web filter fixtures complete.\n";
