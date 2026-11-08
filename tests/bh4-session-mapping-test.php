<?php
/**
 * Isolated CLI fixture tests for BH4_Session_Mapping.
 * Run: php tests/bh4-session-mapping-test.php
 * No WordPress install, database or Events Manager required.
 */
define( 'ABSPATH', __DIR__ . '/' );
class WP_Error {
    public $code;
    public function __construct( $code, $message ) { $this->code = $code; }
    public function get_error_code() { return $this->code; }
}
$GLOBALS['bh4_test_admin'] = true;
function current_user_can( $capability ) {
    return $GLOBALS['bh4_test_admin'] && $capability === 'manage_options';
}
require_once dirname( __DIR__ ) . '/plugin/bubba-hub-manager/includes/class-bh4-session-mapping.php';

function bh4_assert( $condition, $description ) {
    if ( ! $condition ) {
        fwrite( STDERR, "FAIL: {$description}\n" );
        exit( 1 );
    }
    echo "PASS: {$description}\n";
}

$valid = BH4_Session_Mapping::propose( 12, '34', '56' );
bh4_assert( is_array( $valid ) && $valid['bh_activity_id'] === 12 && $valid['bh_session_id'] === 34 && $valid['em_event_id'] === 56, 'positive IDs map correctly' );
bh4_assert( $valid['status'] === 'unverified' && $valid['booking_migration_allowed'] === false && $valid['event_creation_allowed'] === false, 'no migration or creation authorised' );
$optional = BH4_Session_Mapping::propose( '1', 2 );
bh4_assert( is_array( $optional ) && $optional['em_event_id'] === null, 'optional event remains unset' );
foreach ( array( 0, -1, '0', '-4', '12abc', '01', '1.0', 1.5, array( 1 ), null ) as $invalid ) {
    bh4_assert( BH4_Session_Mapping::propose( $invalid, 2 ) instanceof WP_Error, 'invalid activity rejected' );
    bh4_assert( BH4_Session_Mapping::propose( 1, $invalid ) instanceof WP_Error, 'invalid session rejected' );
    if ( null !== $invalid ) {
        bh4_assert( BH4_Session_Mapping::propose( 1, 2, $invalid ) instanceof WP_Error, 'invalid event rejected' );
    }
}
$GLOBALS['bh4_test_admin'] = false;
$denied = BH4_Session_Mapping::propose( 1, 2 );
bh4_assert( $denied instanceof WP_Error && $denied->get_error_code() === 'bh4_forbidden', 'non-admin denied' );
echo "BH4 mapping fixture tests complete.\n";
