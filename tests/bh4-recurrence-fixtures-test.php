<?php
/** Isolated recurring-date tests; no WordPress database or Events Manager. */
define( 'ABSPATH', __DIR__ . '/' );
class WP_Error {
    public $code;
    public function __construct( $code, $message ) { $this->code = $code; }
}
require_once dirname( __DIR__ ) . '/plugin/bubba-hub-manager/includes/class-bh4-recurrence-fixtures.php';

function check_fixture( $condition, $name ) {
    if ( ! $condition ) { fwrite( STDERR, "FAIL: {$name}\n" ); exit( 1 ); }
    echo "PASS: {$name}\n";
}
$weekly = BH4_Recurrence_Fixtures::weekly_dates( '2026-11-02', '2026-11-30', 1 );
check_fixture( $weekly === array( '2026-11-02', '2026-11-09', '2026-11-16', '2026-11-23', '2026-11-30' ), 'weekly Mondays' );
$term = BH4_Recurrence_Fixtures::weekly_dates( '2026-10-05', '2026-11-09', 1, array( '2026-10-26' ) );
check_fixture( $term === array( '2026-10-05', '2026-10-12', '2026-10-19', '2026-11-02', '2026-11-09' ), 'half-term exclusion' );
$cancelled = BH4_Recurrence_Fixtures::weekly_dates( '2026-11-02', '2026-11-16', 1, array( '2026-11-09' ) );
check_fixture( $cancelled === array( '2026-11-02', '2026-11-16' ), 'one-off cancellation' );
$dst = BH4_Recurrence_Fixtures::weekly_dates( '2026-10-18', '2026-11-01', 7 );
check_fixture( $dst === array( '2026-10-18', '2026-10-25', '2026-11-01' ), 'weekly dates across UK DST boundary' );
foreach ( array( '2026-02-30', '2026-13-01', 'not-a-date' ) as $invalid ) {
    check_fixture( BH4_Recurrence_Fixtures::weekly_dates( $invalid, '2026-11-30', 1 ) instanceof WP_Error, 'invalid calendar date rejected' );
}
check_fixture( BH4_Recurrence_Fixtures::weekly_dates( '2026-11-30', '2026-11-01', 1 ) instanceof WP_Error, 'reverse range rejected' );
check_fixture( BH4_Recurrence_Fixtures::weekly_dates( '2026-11-01', '2026-11-30', 8 ) instanceof WP_Error, 'invalid weekday rejected' );
check_fixture( BH4_Recurrence_Fixtures::weekly_dates( '2026-11-01', '2026-11-30', 1, array( 'bad' ) ) instanceof WP_Error, 'invalid exclusion rejected' );
echo "BH4 recurrence fixture tests complete.\n";
