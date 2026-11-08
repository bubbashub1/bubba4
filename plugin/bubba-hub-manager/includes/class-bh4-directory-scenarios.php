<?php
/**
 * Representative directory search scenarios for isolated compatibility tests.
 * No database access or user location is involved; coordinates are synthetic.
 */
defined( 'ABSPATH' ) || exit;
final class BH4_Directory_Scenarios {
    public static function all() {
        return array(
            'newborn' => array( 'age_months' => 0, 'sort' => 'title' ),
            'baby_under_six_months' => array( 'age_months' => 5, 'category' => 'Baby' ),
            'free_only' => array( 'free_only' => true, 'max_price' => 20 ),
            'term_time_monday' => array( 'term_time' => true, 'day' => 1 ),
            'torquay_toddler' => array( 'town' => 'Torquay', 'category' => 'Toddler' ),
            'cornwall_region' => array( 'region' => 'Cornwall', 'sort' => 'town' ),
            'postcode_prefix' => array( 'postcode' => 'TQ1' ),
            'low_cost' => array( 'max_price' => 5 ),
            'keyword_organiser' => array( 'search' => 'Baby Sensory' ),
            'near_me_torbay_fixture' => array( 'latitude' => 50.46, 'longitude' => -3.53, 'radius_miles' => 10 ),
            'near_me_cornwall_fixture' => array( 'latitude' => 50.26, 'longitude' => -5.05, 'radius_miles' => 20 ),
            'paged_newest' => array( 'page' => 2, 'per_page' => 12, 'sort' => 'newest' ),
        );
    }
}
