<?php
/** BH4 booking reference contract tests without WordPress or database. */
define( 'ABSPATH', __DIR__ . '/' );
class WP_Error {
    public $code;
    public function __construct( $code, $message ) { $this->code = $code; }
}
$GLOBALS['bh4_admin'] = true;
function current_user_can( $cap ) { return $GLOBALS['bh4_admin'] && $cap === 'manage_options'; }
require_once dirname( __DIR__ ) . '/plugin/bubba-hub-manager/includes/class-bh4-booking-reference.php';
function check_reference( $ok, $name ) {
    if ( ! $ok ) { fwrite( STDERR, "FAIL: {$name}\n" ); exit( 1 ); }
    echo "PASS: {$name}\n";
}
$proposal = BH4_Booking_Reference::propose( 101, 202, 303, '44:55' );
check_reference( is_array( $proposal ) && $proposal['events_manager_uid'] === '44:55', 'composite timeslot reference accepted' );
check_reference( $proposal['verified'] === false && $proposal['write_allowed'] === false, 'unverified and read-only' );
check_reference( $proposal['payment_owner'] === 'legacy_bubba_hub' && $proposal['schema_version'] === 1, 'stable versioned payment owner contract' );
check_reference( count( array_intersect( array_keys( $proposal ), array( 'child', 'email', 'name', 'phone', 'address' ) ) ) === 0, 'no personal details in reference payload' );
$numeric = BH4_Booking_Reference::propose( '1', '2', '3', 4 );
check_reference( is_array( $numeric ) && $numeric['events_manager_uid'] === 4, 'numeric event reference accepted' );
foreach ( array( 0, -1, '01', '1x', 1.5, null, array( 1 ) ) as $bad ) {
    check_reference( BH4_Booking_Reference::propose( $bad, 2, 3 ) instanceof WP_Error, 'invalid booking ID rejected' );
}
foreach ( array( '0:1', '1:0', '01:2', '1:02', '1:2:3', 'bad' ) as $bad ) {
    check_reference( BH4_Booking_Reference::propose( 1, 2, 3, $bad ) instanceof WP_Error, 'invalid event UID rejected' );
}
$GLOBALS['bh4_admin'] = false;
check_reference( BH4_Booking_Reference::propose( 1, 2, 3 ) instanceof WP_Error, 'non-admin denied' );
echo "BH4 booking reference fixtures complete.\n";
