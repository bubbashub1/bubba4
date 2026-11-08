<?php
define( 'ABSPATH', __DIR__ . '/' );
class WP_Error { public function __construct( $code, $message ) {} }
require_once dirname( __DIR__ ) . '/plugin/bubba-hub-manager/includes/class-bh4-directory-filter-contract.php';
function check_filter( $ok, $label ) { if ( ! $ok ) { fwrite( STDERR, "FAIL: {$label}\n" ); exit( 1 ); } echo "PASS: {$label}\n"; }
$default = BH4_Directory_Filter_Contract::normalize( array() );
check_filter( is_array( $default ) && $default['page'] === 1 && $default['per_page'] === 12, 'default pagination' );
$free = BH4_Directory_Filter_Contract::normalize( array( 'free_only' => true, 'max_price' => 20, 'day' => 7, 'age_months' => 0 ) );
check_filter( $free['max_price'] === 0 && $free['free_only'] === true, 'free-only supersedes price' );
check_filter( $free['day'] === 7 && $free['age_months'] === 0, 'Sunday and newborn age supported' );
foreach ( array( array( 'page' => 0 ), array( 'per_page' => 25 ), array( 'day' => 8 ), array( 'age_months' => 217 ), array( 'sort' => 'random' ), array( 'max_price' => 7 ), array( 'term_time' => '1' ), array( 'category' => array( 'x' ) ), array( 'unknown' => 'x' ) ) as $invalid ) {
    check_filter( BH4_Directory_Filter_Contract::normalize( $invalid ) instanceof WP_Error, 'invalid filter rejected' );
}
echo "BH4 directory filter fixtures complete.\n";
