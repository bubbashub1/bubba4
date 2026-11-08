<?php
/**
 * BH4 read-only directory SQL filter planner for existing prefixed tables.
 * Returns SQL fragments and parameters only. Never executes queries.
 */
defined( 'ABSPATH' ) || exit;
final class BH4_Directory_Query_Plan {
    public static function build( $filters, $tables ) {
        if ( ! class_exists( 'BH4_Directory_Filter_Contract', false ) ) {
            return new WP_Error( 'bh4_missing_filter_contract', 'Filter contract unavailable.' );
        }
        $f = BH4_Directory_Filter_Contract::normalize( $filters );
        if ( $f instanceof WP_Error ) { return $f; }
        foreach ( array( 'activities', 'sessions', 'venues', 'activity_venues' ) as $key ) {
            if ( ! isset( $tables[ $key ] ) || ! is_string( $tables[ $key ] ) || ! preg_match( '/^[A-Za-z0-9_]+$/D', $tables[ $key ] ) ) {
                return new WP_Error( 'bh4_invalid_table', 'Unsafe or missing table identifier.' );
            }
        }
        $conditions = array( "a.status IN ('published','publish')" );
        $params = array();
        if ( $f['search'] !== '' ) {
            $conditions[] = '(a.title LIKE %s OR a.description LIKE %s)';
            $pattern = '%' . self::escape_like( $f['search'] ) . '%';
            $params[] = $pattern;
            $params[] = $pattern;
        }
        if ( $f['category'] !== '' ) { $conditions[] = 'a.category = %s'; $params[] = $f['category']; }
        if ( $f['free_only'] ) { $conditions[] = 'a.is_free = 1'; }
        elseif ( $f['max_price'] ) {
            $conditions[] = '(a.is_free = 1 OR (a.price_from IS NOT NULL AND a.price_from <= %d))';
            $params[] = $f['max_price'];
        }
        if ( $f['age_months'] > 0 ) {
            $conditions[] = '(a.age_min_months IS NULL OR a.age_min_months <= %d) AND (a.age_max_months IS NULL OR a.age_max_months >= %d)';
            $params[] = $f['age_months'];
            $params[] = $f['age_months'];
        }
        if ( $f['day'] ) {
            $conditions[] = 'EXISTS (SELECT 1 FROM `' . $tables['sessions'] . '` sd WHERE sd.activity_id=a.id AND sd.day_of_week=%d)';
            $params[] = $f['day'];
        }
        if ( $f['term_time'] ) {
            $conditions[] = 'EXISTS (SELECT 1 FROM `' . $tables['sessions'] . '` st WHERE st.activity_id=a.id AND st.term_time_only=1)';
        }
        foreach ( array( 'town' => 'LIKE', 'region' => '=', 'postcode' => 'LIKE' ) as $key => $operator ) {
            if ( $f[ $key ] === '' ) { continue; }
            $conditions[] = 'EXISTS (SELECT 1 FROM `' . $tables['activity_venues'] . '` av INNER JOIN `' . $tables['venues'] . '` v ON v.id=av.venue_id WHERE av.activity_id=a.id AND v.' . $key . ' ' . $operator . ' %s)';
            $params[] = $operator === 'LIKE' ? ( $key === 'postcode' ? self::escape_like( $f[ $key ] ) . '%' : '%' . self::escape_like( $f[ $key ] ) . '%' ) : $f[ $key ];
        }
        $order = $f['sort'] === 'newest' ? 'a.id DESC' : ( $f['sort'] === 'town' ? 'a.title ASC' : 'a.title ASC' );
        // Town ordering requires a venue join; intentionally use stable title order
        // until the full directory query is reconciled.
        return array(
            'where' => implode( ' AND ', $conditions ),
            'params' => $params,
            'order_by' => $order,
            'limit' => $f['per_page'] + 1,
            'offset' => ( $f['page'] - 1 ) * $f['per_page'],
            'execution_allowed' => false,
            'source' => 'read_only_query_plan',
            'limitations' => array( 'age_months_zero_requires_explicit_presence', 'town_sort_not_yet_equivalent', 'geo_and_organiser_keyword_not_yet_supported' ),
        );
    }
    private static function escape_like( $value ) {
        return addcslashes( $value, '_%\\' );
    }
}
