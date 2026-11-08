<?php
/**
 * Pure composition of already-authorised published-only directory rows and weekly sessions.
 * No DB queries, REST routes, hooks or user information.
 */
defined( 'ABSPATH' ) || exit;
final class BH4_Directory_Combined_Response {
    public static function project( $activity_rows, $session_rows, $page_size = 12 ) {
        foreach ( array( 'BH4_Directory_Select_Response', 'BH4_Directory_Card_Details', 'BH4_Directory_Timetable' ) as $class ) {
            if ( ! class_exists( $class, false ) ) {
                return new WP_Error( 'bh4_missing_directory_contract', 'Required directory contract unavailable.' );
            }
        }
        $collection = BH4_Directory_Select_Response::format( $activity_rows, $page_size );
        if ( $collection instanceof WP_Error ) { return $collection; }
        $ids = array();
        $cards = array();
        foreach ( $collection['items'] as $index => $activity ) {
            $row = $activity_rows[ $index ];
            $details = BH4_Directory_Card_Details::project( $row );
            if ( $details instanceof WP_Error ) { return $details; }
            $ids[] = $activity['id'];
            $cards[] = array(
                'activity' => $activity,
                'details' => $details,
                'weekly_sessions' => array(),
            );
        }
        $timetable = BH4_Directory_Timetable::project( $session_rows, $ids );
        if ( $timetable instanceof WP_Error ) { return $timetable; }
        foreach ( $cards as &$card ) {
            $card['weekly_sessions'] = $timetable['by_activity'][ $card['activity']['id'] ] ?? array();
        }
        unset( $card );
        return array(
            'schema_version' => 1,
            'type' => 'directory_cards',
            'items' => $cards,
            'returned' => count( $cards ),
            'has_more' => $collection['has_more'],
            'booking_availability' => 'not_checked',
            'staging_verified' => false,
            'execution_allowed' => false,
        );
    }
}
