<?php
define( 'ABSPATH', __DIR__ . '/' );
class WP_Error { public function __construct( $code, $message ) {} }
require_once dirname( __DIR__ ) . '/plugin/bubba-hub-manager/includes/class-bh4-directory-timetable.php';
function check_timetable( $ok, $label ) { if ( ! $ok ) { fwrite( STDERR, "FAIL: {$label}\n" ); exit( 1 ); } echo "PASS: {$label}\n"; }
$rows = array(
 array( 'activity_id' => 12, 'day_of_week' => 2, 'start_time' => '11:30:00', 'term_time_only' => 1, 'frequency' => 'weekly', 'status' => 'scheduled', 'child_name' => 'private' ),
 array( 'activity_id' => '12', 'day_of_week' => '1', 'start_time' => '09:00', 'term_time_only' => 0 ),
 array( 'activity_id' => 12, 'day_of_week' => 2, 'start_time' => '11:30:00', 'term_time_only' => 1 ),
);
$r = BH4_Directory_Timetable::project( $rows, array( 12 ) );
check_timetable( count( $r['by_activity'][12] ) === 2, 'deduplicated weekly sessions' );
check_timetable( $r['by_activity'][12][0]['day_of_week'] === 1 && $r['by_activity'][12][1]['start_time'] === '11:30', 'weekday and time ordering' );
check_timetable( $r['by_activity'][12][1]['term_time_only'] === true, 'term time indicator' );
check_timetable( ! isset( $r['by_activity'][12][0]['child_name'] ) && $r['availability'] === 'not_checked', 'private fields and booking availability excluded' );
$bad = $rows;
$bad[0]['status'] = 'cancelled';
check_timetable( BH4_Directory_Timetable::project( $bad, array( 12 ) ) instanceof WP_Error, 'cancelled session rejected' );
check_timetable( BH4_Directory_Timetable::project( array( array( 'activity_id' => 13, 'day_of_week' => 1, 'start_time' => '10:00' ) ), array( 12 ) ) instanceof WP_Error, 'unrelated activity rejected' );
echo "BH4 directory timetable fixtures complete.\n";
