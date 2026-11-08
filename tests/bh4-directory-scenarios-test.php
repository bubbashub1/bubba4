<?php
define( 'ABSPATH', __DIR__ . '/' );
class WP_Error { public function __construct( $code, $message ) {} }
require_once dirname( __DIR__ ) . '/plugin/bubba-hub-manager/includes/class-bh4-directory-filter-contract.php';
require_once dirname( __DIR__ ) . '/plugin/bubba-hub-manager/includes/class-bh4-directory-query-plan.php';
require_once dirname( __DIR__ ) . '/plugin/bubba-hub-manager/includes/class-bh4-directory-scenarios.php';
function check_scenario( $ok, $label ) { if ( ! $ok ) { fwrite( STDERR, "FAIL: {$label}\n" ); exit( 1 ); } echo "PASS: {$label}\n"; }
$tables = array( 'activities' => 'wp_activities', 'sessions' => 'wp_sessions', 'venues' => 'wp_venues', 'activity_venues' => 'wp_activity_venues', 'organisers' => 'wp_organisers' );
$scenarios = BH4_Directory_Scenarios::all();
check_scenario( count( $scenarios ) >= 12, 'representative scenario coverage' );
foreach ( $scenarios as $name => $filters ) {
    $plan = BH4_Directory_Query_Plan::build( $filters, $tables );
    check_scenario( is_array( $plan ) && $plan['execution_allowed'] === false, $name . ' generates non-executing plan' );
    check_scenario( strpos( $plan['where'], "a.status IN ('published','publish')" ) !== false, $name . ' enforces published only' );
}
$newborn = BH4_Directory_Query_Plan::build( $scenarios['newborn'], $tables );
check_scenario( strpos( $newborn['where'], 'age_min_months' ) !== false, 'newborn age zero retained' );
$free = BH4_Directory_Query_Plan::build( $scenarios['free_only'], $tables );
check_scenario( strpos( $free['where'], 'a.is_free = 1' ) !== false && strpos( $free['where'], 'price_from <=' ) === false, 'free only overrides price ceiling' );
$near = BH4_Directory_Query_Plan::build( $scenarios['near_me_torbay_fixture'], $tables );
check_scenario( strpos( $near['where'], '3958.7613' ) !== false, 'Near Me uses great-circle distance' );
$term = BH4_Directory_Query_Plan::build( $scenarios['term_time_monday'], $tables );
check_scenario( strpos( $term['where'], 'term_time_only=1' ) !== false, 'term-time predicate retained' );
echo "BH4 directory scenario fixtures complete.\n";
