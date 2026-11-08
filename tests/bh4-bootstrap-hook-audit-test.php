<?php
/** Static bootstrap regression check: no WordPress bootstrap or database. */
$source = file_get_contents( dirname( __DIR__ ) . '/plugin/bubba-hub-manager/includes/class-bh-manager.php' );
if ( ! is_string( $source ) ) { fwrite( STDERR, "Unable to read manager source\n" ); exit( 1 ); }
$action = "add_action( 'admin_post_bh3_update_activity', array( 'BH_Activity_Edit_3', 'save' ) );";
if ( substr_count( $source, $action ) !== 1 ) {
    fwrite( STDERR, "FAIL: activity update action must be registered exactly once\n" );
    exit( 1 );
}
foreach ( array( 'admin_post_bh3_install_schema', 'admin_post_bh3_install_missing_booking_tables' ) as $hook ) {
    if ( strpos( $source, $hook ) === false ) {
        fwrite( STDERR, "FAIL: legacy installer hook missing: {$hook}\n" );
        exit( 1 );
    }
}
echo "PASS: single activity update registration; legacy installer hooks retained.\n";
