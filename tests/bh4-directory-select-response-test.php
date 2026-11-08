<?php
define( 'ABSPATH', __DIR__ . '/' );
class WP_Error { public function __construct( $code, $message ) {} }
require_once dirname( __DIR__ ) . '/plugin/bubba-hub-manager/includes/class-bh4-activity-api-contract.php';
require_once dirname( __DIR__ ) . '/plugin/bubba-hub-manager/includes/class-bh4-directory-select-response.php';
function check_response( $ok, $label ) { if ( ! $ok ) { fwrite( STDERR, "FAIL: {$label}\n" ); exit( 1 ); } echo "PASS: {$label}\n"; }
$rows = array(
 array( 'id' => '10', 'title' => 'Baby Group', 'slug' => 'baby-group', 'category' => 'Baby', 'town' => 'Torquay', 'is_free' => 1, 'price_from' => 5, 'parent_email' => 'secret' ),
 array( 'id' => 11, 'title' => 'Toddler Play', 'slug' => 'toddler-play', 'town' => 'Exeter', 'is_free' => 0, 'price_from' => '4.50', 'leader_phone' => 'secret' ),
);
$result = BH4_Directory_Select_Response::format( $rows, 1 );
check_response( $result['returned'] === 1 && $result['has_more'] === true, 'pagination with lookahead' );
check_response( $result['items'][0]['price_label'] === 'Free', 'free activity label' );
check_response( ! isset( $result['items'][0]['parent_email'] ), 'private fields excluded' );
$all = BH4_Directory_Select_Response::format( $rows, 2 );
check_response( $all['items'][1]['price_label'] === 'From £4.50', 'paid activity label' );
check_response( ! isset( $all['items'][1]['leader_phone'] ), 'organiser private fields excluded' );
$draft = $rows;
$draft[0]['status'] = 'draft';
check_response( BH4_Directory_Select_Response::format( $draft, 2 ) instanceof WP_Error, 'unpublished row rejected' );
$duplicate = array( $rows[0], $rows[0] );
check_response( BH4_Directory_Select_Response::format( $duplicate, 2 ) instanceof WP_Error, 'duplicate id rejected' );
check_response( BH4_Directory_Select_Response::format( $rows, 0 ) instanceof WP_Error, 'invalid page size rejected' );
check_response( BH4_Directory_Select_Response::format( 'bad' ) instanceof WP_Error, 'invalid rows rejected' );
echo "BH4 directory SELECT response fixtures complete.\n";
