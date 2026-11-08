<?php
/** Isolated directory adapter fixture tests. */
define( 'ABSPATH', __DIR__ . '/' );
class WP_Error { public function __construct( $code, $message ) {} }
require_once dirname( __DIR__ ) . '/plugin/bubba-hub-manager/includes/class-bh4-activity-api-contract.php';
require_once dirname( __DIR__ ) . '/plugin/bubba-hub-manager/includes/class-bh4-directory-adapter.php';
function assert_directory( $ok, $label ) {
    if ( ! $ok ) { fwrite( STDERR, "FAIL: {$label}\n" ); exit( 1 ); }
    echo "PASS: {$label}\n";
}
$rows = array(
    array( 'id' => 1, 'title' => 'Baby Group', 'slug' => 'baby-group', 'status' => 'published', 'guardian_email' => 'PRIVATE' ),
    array( 'id' => 2, 'title' => 'Draft', 'slug' => 'draft', 'status' => 'draft' ),
    array( 'id' => 1, 'title' => 'Duplicate', 'slug' => 'duplicate', 'status' => 'published' ),
    array( 'id' => 3, 'title' => 'Toddler Time', 'slug' => 'toddler-time', 'status' => 'published' ),
    array( 'id' => 4, 'title' => 'Invalid', 'slug' => 'BAD SLUG', 'status' => 'published' ),
);
$result = BH4_Directory_Adapter::project( $rows, 1 );
assert_directory( $result['returned'] === 1 && $result['has_more'] === true, 'limit and has_more' );
assert_directory( $result['items'][0]['title'] === 'Baby Group', 'first valid published item retained' );
assert_directory( ! isset( $result['items'][0]['guardian_email'] ), 'private fields excluded' );
assert_directory( $result['skipped'] === 3, 'draft duplicate and invalid rows skipped' );
$full = BH4_Directory_Adapter::project( $rows, 20 );
assert_directory( $full['returned'] === 2 && $full['has_more'] === false, 'deduplicated published results' );
assert_directory( BH4_Directory_Adapter::project( $rows, 101 ) instanceof WP_Error, 'oversized request rejected' );
assert_directory( BH4_Directory_Adapter::project( 'bad' ) instanceof WP_Error, 'non-array source rejected' );
echo "BH4 directory adapter fixtures complete.\n";
