<?php
/**
 * Constructs a complete read-only SQL statement from a validated BH4 plan.
 * Never prepares or executes SQL; the caller must use wpdb->prepare with params.
 */
defined( 'ABSPATH' ) || exit;
final class BH4_Directory_Select_Contract {
    public static function build( $filters, $tables ) {
        if ( ! class_exists( 'BH4_Directory_Query_Plan', false ) ) {
            return new WP_Error( 'bh4_missing_query_plan', 'Directory query planner unavailable.' );
        }
        $plan = BH4_Directory_Query_Plan::build( $filters, $tables );
        if ( $plan instanceof WP_Error ) { return $plan; }
        // Table identifiers were validated by the planner, not user-interpolated.
        $a = $tables['activities'];
        $o = $tables['organisers'];
        $v = $tables['venues'];
        $av = $tables['activity_venues'];
        $sql = "SELECT a.id,a.title,a.slug,a.category,a.price_from,a.is_free,o.name AS organiser,"
            . " (SELECT v_first.town FROM `{$av}` av_first INNER JOIN `{$v}` v_first ON v_first.id=av_first.venue_id"
            . " WHERE av_first.activity_id=a.id ORDER BY v_first.name LIMIT 1) AS town"
            . " FROM `{$a}` a LEFT JOIN `{$o}` o ON o.id=a.organiser_id"
            . " WHERE " . $plan['where']
            . " ORDER BY " . $plan['order_by']
            . " LIMIT %d OFFSET %d";
        $params = $plan['params'];
        $params[] = $plan['limit'];
        $params[] = $plan['offset'];
        return array(
            'sql' => $sql,
            'params' => $params,
            'page_size' => $plan['limit'] - 1,
            'execution_allowed' => false,
            'contains_personal_data' => false,
            'requires_schema_verification' => true,
            'source' => 'non_executing_select_contract',
        );
    }
}
