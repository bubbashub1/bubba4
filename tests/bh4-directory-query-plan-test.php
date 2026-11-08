<?php
define( 'ABSPATH', __DIR__ . '/' );
class WP_Error { public function __construct( $code, $message ) {} }
require_once dirname( __DIR__ ) . '/plugin/bubba-hub-manager/includes/class-bh4-directory-filter-contract.php';
require_once dirname( __DIR__ ) . '/plugin/bubba-hub-manager/includes/class-bh4-directory-query-plan.php';
function check_plan( $ok, $name ) { if ( ! $ok ) { fwrite( STDERR, "FAIL: {$name}\n" ); exit( 1 ); } echo "PASS: {$name}\n"; }
$tables = array( 'activities' => 'wp_activities', 'sessions' => 'wp_sessions', 'venues' => 'wp_venues', 'activity_venues' => 'wp_activity_venues' );
$p = BH4_Directory_Query_Plan::build( array( 'free_only' => true, 'max_price' => 20, 'day' => 2, 'term_time' => true, 'town' => 'Torquay', 'page' => 2 ), $tables );
check_plan( is_array( $p ) && $p['execution_allowed'] === false, 'planner cannot execute' );
check_plan( strpos( $p['where'], 'a.is_free = 1' ) !== false && strpos( $p['where'], 'price_from' ) === false, 'free-only wins over price' );
check_plan( $p['limit'] === 13 && $p['offset'] === 12, 'extra row for pagination and offset' );
check_plan( strpos( $p['where'], 'term_time_only=1' ) !== false, 'term-time session predicate' );
check_plan( in_array( '%Torquay%', $p['params'], true ), 'town value parameterised' );
$injection = BH4_Directory_Query_Plan::build( array( 'search' => "x%' OR 1=1 --" ), $tables );
check_plan( strpos( $injection['where'], 'OR 1=1' ) === false, 'keyword not interpolated into SQL' );
check_plan( count( $injection['params'] ) === 2, 'keyword passed as two parameters' );
$bad = $tables;
$bad['venues'] = 'wp_venues;DROP TABLE';
check_plan( BH4_Directory_Query_Plan::build( array(), $bad ) instanceof WP_Error, 'unsafe table name rejected' );
check_plan( BH4_Directory_Query_Plan::build( array( 'day' => 9 ), $tables ) instanceof WP_Error, 'invalid filter rejected' );
$newborn = BH4_Directory_Query_Plan::build( array( 'age_months' => 0 ), $tables );
check_plan( substr_count( $newborn['where'], 'age_min_months' ) === 1 && in_array( 0, $newborn['params'], true ), 'explicit newborn age applies age predicate' );
$no_age = BH4_Directory_Query_Plan::build( array(), $tables );
check_plan( strpos( $no_age['where'], 'age_min_months' ) === false, 'omitted age does not filter' );
$town_order = BH4_Directory_Query_Plan::build( array( 'sort' => 'town' ), $tables );
check_plan( strpos( $town_order['order_by'], 'v_sort.town' ) !== false && strpos( $town_order['order_by'], 'v_sort.name' ) !== false, 'town sort follows venue selection' );
check_plan( strpos( $town_order['order_by'], 'a.id ASC' ) !== false, 'town sort has stable tie breaker' );
echo "BH4 directory query plan fixtures complete.\n";
