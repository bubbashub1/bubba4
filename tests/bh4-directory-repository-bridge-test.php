<?php
/** Isolated BH4 repository bridge tests; no database or WordPress bootstrap. */
define( 'ABSPATH', __DIR__ . '/' );
class WP_Error { public $code; public function __construct( $code, $message ) { $this->code = $code; } }
class BH_Activity_Repository_3 {
    public $rows = array();
    public $error = false;
    public $last_args = null;
    public function search( $args = array() ) {
        $this->last_args = $args;
        if ( $this->error ) { return new WP_Error( 'query_failed', 'Simulated error' ); }
        return $this->rows;
    }
}
require_once dirname( __DIR__ ) . '/plugin/bubba-hub-manager/includes/class-bh4-activity-api-contract.php';
require_once dirname( __DIR__ ) . '/plugin/bubba-hub-manager/includes/class-bh4-directory-adapter.php';
require_once dirname( __DIR__ ) . '/plugin/bubba-hub-manager/includes/class-bh4-directory-repository-bridge.php';
function bh4_bridge_assert( $condition, $name ) {
    if ( ! $condition ) { fwrite( STDERR, "FAIL: {$name}\n" ); exit( 1 ); }
    echo "PASS: {$name}\n";
}
$repo = new BH_Activity_Repository_3();
$repo->rows = array(
    array( 'id' => 12, 'title' => 'Baby Play', 'slug' => 'baby-play', 'private_email' => 'secret@example.invalid' ),
    array( 'id' => 13, 'title' => 'Toddler Time', 'slug' => 'toddler-time' ),
);
$preview = BH4_Directory_Repository_Bridge::preview( $repo, array( 'per_page' => 2, 'search' => 'baby' ) );
bh4_bridge_assert( is_array( $preview ) && $preview['returned'] === 2, 'repository rows projected' );
bh4_bridge_assert( $repo->last_args['search'] === 'baby', 'search args passed through' );
bh4_bridge_assert( $preview['has_more'] === null && $preview['pagination_verified'] === false, 'unverified pagination not claimed' );
bh4_bridge_assert( ! isset( $preview['items'][0]['private_email'] ), 'private repository fields excluded' );
bh4_bridge_assert( $preview['source'] === 'bh_activity_repository_3_published_search', 'source identified' );
$repo->error = true;
bh4_bridge_assert( BH4_Directory_Repository_Bridge::preview( $repo ) instanceof WP_Error, 'repository errors propagated' );
bh4_bridge_assert( BH4_Directory_Repository_Bridge::preview( new stdClass() ) instanceof WP_Error, 'wrong repository rejected' );
bh4_bridge_assert( BH4_Directory_Repository_Bridge::preview( $repo, array( 'per_page' => 25 ) ) instanceof WP_Error, 'oversized page rejected' );
$repo->error = false;
$repo->rows = array( 'bad' );
bh4_bridge_assert( BH4_Directory_Repository_Bridge::preview( $repo ) instanceof WP_Error, 'malformed rows rejected' );
echo "BH4 directory repository bridge fixtures complete.\n";
