<?php
define( 'ABSPATH', __DIR__ . '/' );
class WP_Error { public function __construct( $code, $message ) {} }
require_once dirname( __DIR__ ) . '/plugin/bubba-hub-manager/includes/class-bh4-directory-schema-check.php';
function check_schema( $ok, $name ) { if ( ! $ok ) { fwrite( STDERR, "FAIL: {$name}\n" ); exit( 1 ); } echo "PASS: {$name}\n"; }
$inventory = array(
 'activities' => array( 'id','title','slug','status','description','category','is_free','price_from','age_min_months','age_max_months','organiser_id' ),
 'organisers' => array( 'id','name' ),
 'venues' => array( 'id','name','town','region','postcode','latitude','longitude' ),
 'activity_venues' => array( 'activity_id','venue_id' ),
 'sessions' => array( 'activity_id','day_of_week','term_time_only' ),
);
$ok = BH4_Directory_Schema_Check::inspect( $inventory );
check_schema( $ok['compatible_with_directory_select'] === true, 'complete fixture inventory' );
check_schema( $ok['verified_against_staging'] === false && $ok['execution_allowed'] === false, 'never claims staging verification or execution' );
$missing = $inventory;
unset( $missing['venues'][5] );
$bad = BH4_Directory_Schema_Check::inspect( $missing );
check_schema( $bad['compatible_with_directory_select'] === false && in_array( 'latitude', $bad['missing_columns']['venues'], true ), 'missing coordinate reported' );
check_schema( BH4_Directory_Schema_Check::inspect( array() ) instanceof WP_Error, 'missing tables rejected' );
echo "BH4 directory schema fixtures complete.\n";
