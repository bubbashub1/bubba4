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
        foreach ( array( 'activities', 'sessions', 'venues', 'activity_venues', 'organisers' ) as $key ) {
            if ( ! isset( $tables[ $key ] ) || ! is_string( $tables[ $key ] ) || ! preg_match( '/^[A-Za-z0-9_]+$/D', $tables[ $key ] ) ) {
                return new WP_Error( 'bh4_invalid_table', 'Unsafe or missing table identifier.' );
            }
        }
        $conditions = array( "a.status IN ('published','publish')" );
        $params = array();
        if ( $f['search'] !== '' ) {
            $conditions[] = '(a.title LIKE %s OR a.description LIKE %s OR EXISTS (SELECT 1 FROM `' . $tables['organisers'] . '` os WHERE os.id=a.organiser_id AND os.name LIKE %s))';
            $pattern = '%' . self::escape_like( $f['search'] ) . '%';
            $params[] = $pattern;
            $params[] = $pattern;
            $params[] = $pattern;
        }
        if ( $f['category'] !== '' ) { $conditions[] = 'a.category = %s'; $params[] = $f['category']; }
        if ( $f['free_only'] ) { $conditions[] = 'a.is_free = 1'; }
        elseif ( $f['max_price'] ) {
            $conditions[] = '(a.is_free = 1 OR (a.price_from IS NOT NULL AND a.price_from <= %d))';
            $params[] = $f['max_price'];
        }
        if ( array_key_exists( 'age_months', $filters ) ) {
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
        if ( isset( $f['latitude'], $f['longitude'] ) ) {
            $lat = $f['latitude'];
            $lng = $f['longitude'];
            $radius = $f['radius_miles'];
            $lat_delta = $radius / 69.0;
            $lng_delta = $radius / max( 0.01, 69.0 * cos( deg2rad( $lat ) ) );
            $distance = '(3958.7613 * 2 * ASIN(SQRT(POW(SIN(RADIANS(vn.latitude - %f) / 2),2) + COS(RADIANS(%f)) * COS(RADIANS(vn.latitude)) * POW(SIN(RADIANS(vn.longitude - %f) / 2),2))))';
            $conditions[] = 'EXISTS (SELECT 1 FROM `' . $tables['activity_venues'] . '` an INNER JOIN `' . $tables['venues'] . '` vn ON vn.id=an.venue_id WHERE an.activity_id=a.id AND vn.latitude BETWEEN %f AND %f AND vn.longitude BETWEEN %f AND %f AND ' . $distance . ' <= %f)';
            array_push( $params, max( -90, $lat - $lat_delta ), min( 90, $lat + $lat_delta ), max( -180, $lng - $lng_delta ), min( 180, $lng + $lng_delta ), $lat, $lat, $lng, $radius );
        }
        $town_sort = '(SELECT v_sort.town FROM `' . $tables['activity_venues'] . '` av_sort INNER JOIN `' . $tables['venues'] . '` v_sort ON v_sort.id=av_sort.venue_id WHERE av_sort.activity_id=a.id ORDER BY v_sort.name LIMIT 1)';
        $order = $f['sort'] === 'newest' ? 'a.id DESC' : ( $f['sort'] === 'town' ? $town_sort . ' ASC, a.title ASC, a.id ASC' : 'a.title ASC, a.id ASC' );
        return array(
            'where' => implode( ' AND ', $conditions ),
            'params' => $params,
            'order_by' => $order,
            'limit' => $f['per_page'] + 1,
            'offset' => ( $f['page'] - 1 ) * $f['per_page'],
            'execution_allowed' => false,
            'source' => 'read_only_query_plan',
            'limitations' => array( 'antimeridian_crossing_not_yet_supported' ),
        );
    }
    private static function escape_like( $value ) {
        return addcslashes( $value, '_%\\' );
    }
}
