<?php
/**
 * Formats rows supplied by the validated, published-only directory SELECT.
 * Pure transformer; no database calls, hooks, routes or writes.
 */
defined( 'ABSPATH' ) || exit;
final class BH4_Directory_Select_Response {
    public static function format( $rows, $page_size = 12 ) {
        if ( ! is_array( $rows ) || ! is_int( $page_size ) || $page_size < 1 || $page_size > 24 || count( $rows ) > $page_size + 1 ) {
            return new WP_Error( 'bh4_invalid_select_rows', 'Invalid directory SELECT result.' );
        }
        if ( ! class_exists( 'BH4_Activity_API_Contract', false ) ) {
            return new WP_Error( 'bh4_missing_contract', 'Public activity contract unavailable.' );
        }
        $items = array();
        $seen = array();
        $has_more = count( $rows ) > $page_size;
        foreach ( array_slice( $rows, 0, $page_size ) as $row ) {
            if ( ! is_array( $row ) ) { return new WP_Error( 'bh4_invalid_select_row', 'Invalid activity row.' ); }
            // The caller must guarantee rows originate from the published-only SELECT.
            // Fail closed if a status is provided and contradicts that guarantee.
            if ( isset( $row['status'] ) && ! in_array( $row['status'], array( 'published', 'publish' ), true ) ) {
                return new WP_Error( 'bh4_unpublished_row', 'Non-public activity row.' );
            }
            $id = $row['id'] ?? null;
            if ( isset( $seen[ (string) $id ] ) ) { return new WP_Error( 'bh4_duplicate_row', 'Duplicate activity ID.' ); }
            $seen[ (string) $id ] = true;
            $is_free = isset( $row['is_free'] ) && (string) $row['is_free'] === '1';
            $price = $row['price_from'] ?? null;
            $price_label = null;
            if ( $is_free ) {
                $price_label = 'Free';
            } elseif ( is_numeric( $price ) && (float) $price >= 0 && is_finite( (float) $price ) ) {
                $price_label = 'From £' . number_format( (float) $price, 2, '.', '' );
            }
            $activity = BH4_Activity_API_Contract::format_activity( array(
                'id' => $id,
                'title' => $row['title'] ?? null,
                'slug' => $row['slug'] ?? null,
                'category' => $row['category'] ?? null,
                'town' => $row['town'] ?? null,
                'price_label' => $price_label,
            ) );
            if ( $activity instanceof WP_Error ) { return $activity; }
            $items[] = $activity;
        }
        return array(
            'schema_version' => 1,
            'type' => 'activity_collection',
            'items' => $items,
            'returned' => count( $items ),
            'has_more' => $has_more,
            'source' => 'validated_published_only_select_rows',
            'execution_allowed' => false,
        );
    }
}
