<?php
define( 'ABSPATH', __DIR__ . '/' );
class WP_Error { public function __construct( $code, $message ) {} }
require_once dirname( __DIR__ ) . '/plugin/bubba-hub-manager/includes/class-bh4-directory-filter-contract.php';
require_once dirname( __DIR__ ) . '/plugin/bubba-hub-manager/includes/class-bh4-directory-query-plan.php';
require_once dirname( __DIR__ ) . '/plugin/bubba-hub-manager/includes/class-bh4-directory-select-contract.php';
function check_select( $ok, $label ) { if ( ! $ok ) { fwrite( STDERR, "FAIL: {$label}\n" ); exit( 1 ); } echo "PASS: {$label}\n"; }
$tables = array( 'activities' => 'wp_activities', 'sessions' => 'wp_sessions', 'venues' => 'wp_venues', 'activity_venues' => 'wp_activity_venues', 'organisers' => 'wp_organisers' );
$select = BH4_Directory_Select_Contract::build( array( 'search' => 'baby', 'town' => 'Torquay', 'free_only' => true, 'page' => 2 ), $tables );
check_select( is_array( $select ) && $select['execution_allowed'] === false, 'SELECT never executes' );
check_select( strpos( $select['sql'], 'SELECT a.id,a.title,a.slug' ) === 0, 'public field projection' );
check_select( strpos( $select['sql'], 'a.description,' ) === false, 'description not returned' );
check_select( strpos( $select['sql'], 'LIMIT %d OFFSET %d' ) !== false, 'pagination bound' );
check_select( array_slice( $select['params'], -2 ) === array( 13, 12 ), 'page size and offset' );
check_select( $select['requires_schema_verification'] === true, 'schema verification required' );
check_select( BH4_Directory_Select_Contract::build( array( 'sort' => 'unsafe' ), $tables ) instanceof WP_Error, 'invalid sort rejected' );
echo "BH4 directory SELECT fixtures complete.\n";
