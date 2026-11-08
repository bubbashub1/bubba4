<?php
define( 'ABSPATH', __DIR__ . '/' );
class WP_Error { public function __construct( $code, $message ) {} }
$base = dirname( __DIR__ ) . '/plugin/bubba-hub-manager/includes/';
require_once $base . 'class-bh4-activity-api-contract.php';
require_once $base . 'class-bh4-directory-select-response.php';
require_once $base . 'class-bh4-directory-card-details.php';
require_once $base . 'class-bh4-directory-timetable.php';
require_once $base . 'class-bh4-directory-combined-response.php';
function check_combined( $ok, $label ) { if ( ! $ok ) { fwrite( STDERR, "FAIL: {$label}\n" ); exit( 1 ); } echo "PASS: {$label}\n"; }
$rows = array(
 array( 'id' => 12, 'title' => 'Baby Group', 'slug' => 'baby-group', 'town' => 'Torquay', 'category' => 'Baby', 'is_free' => 1, 'age_min_months' => 0, 'age_max_months' => 12, 'organiser' => 'Baby Sensory', 'image_path' => '/wp-content/uploads/2026/11/baby.jpg', 'child_name' => 'private' ),
 array( 'id' => 13, 'title' => 'Toddler Group', 'slug' => 'toddler-group', 'town' => 'Exeter', 'price_from' => 5, 'is_free' => 0 ),
);
$sessions = array( array( 'activity_id' => 12, 'day_of_week' => 1, 'start_time' => '10:30:00', 'term_time_only' => 1 ) );
$r = BH4_Directory_Combined_Response::project( $rows, $sessions, 1 );
check_combined( $r['returned'] === 1 && $r['has_more'] === true, 'one card with lookahead pagination' );
check_combined( $r['items'][0]['details']['age_min_months'] === 0, 'newborn age retained' );
check_combined( $r['items'][0]['weekly_sessions'][0]['start_time'] === '10:30', 'weekly session attached to correct activity' );
check_combined( ! isset( $r['items'][0]['child_name'] ) && ! isset( $r['items'][0]['activity']['child_name'] ), 'private data excluded' );
check_combined( $r['booking_availability'] === 'not_checked' && $r['execution_allowed'] === false, 'no availability or execution claims' );
check_combined( BH4_Directory_Combined_Response::project( $rows, array( array( 'activity_id' => 99, 'day_of_week' => 1, 'start_time' => '10:30' ) ), 1 ) instanceof WP_Error, 'unrelated session rejected' );
echo "BH4 combined directory fixtures complete.\n";
