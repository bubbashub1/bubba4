<?php
/** Pure API response-shape fixtures; no WP site, database or REST routes. */
define( 'ABSPATH', __DIR__ . '/' );
class WP_Error { public function __construct( $code, $message ) {} }
require_once dirname( __DIR__ ) . '/plugin/bubba-hub-manager/includes/class-bh4-activity-api-contract.php';
function assert_api( $condition, $name ) {
    if ( ! $condition ) { fwrite( STDERR, "FAIL: {$name}\n" ); exit( 1 ); }
    echo "PASS: {$name}\n";
}
$activity = BH4_Activity_API_Contract::format_activity( array(
    'id' => '17', 'title' => '  Baby Group  ', 'slug' => 'baby-group',
    'category' => 'Baby', 'town' => 'Torquay', 'price_label' => 'Free',
    'child_name' => 'PRIVATE', 'guardian_email' => 'PRIVATE', 'stripe_secret' => 'PRIVATE',
) );
assert_api( is_array( $activity ) && $activity['schema_version'] === 1 && $activity['title'] === 'Baby Group', 'public activity projection formatted' );
assert_api( ! isset( $activity['child_name'] ) && ! isset( $activity['guardian_email'] ) && ! isset( $activity['stripe_secret'] ), 'unlisted private fields excluded' );
$session = BH4_Activity_API_Contract::format_session( array(
    'id' => 10, 'activity_id' => 17, 'date' => '2026-10-25', 'start_time' => '09:30',
    'booking_email' => 'PRIVATE',
) );
assert_api( is_array( $session ) && $session['timezone'] === 'Europe/London' && $session['availability'] === 'unknown', 'session has explicit local timezone and unknown availability' );
assert_api( ! isset( $session['booking_email'] ), 'booking details excluded' );
foreach ( array( '2026-02-30', '2026-13-01', 'bad' ) as $invalid ) {
    assert_api( BH4_Activity_API_Contract::format_session( array( 'id' => 1, 'activity_id' => 2, 'date' => $invalid, 'start_time' => '10:00' ) ) instanceof WP_Error, 'invalid session date rejected' );
}
assert_api( BH4_Activity_API_Contract::format_activity( array( 'id' => 1, 'title' => 'Test', 'slug' => '../private' ) ) instanceof WP_Error, 'invalid slug rejected' );
assert_api( BH4_Activity_API_Contract::format_session( array( 'id' => 1, 'activity_id' => 2, 'date' => '2026-11-08', 'start_time' => '25:00' ) ) instanceof WP_Error, 'invalid time rejected' );
echo "BH4 API contract fixtures complete.\n";
