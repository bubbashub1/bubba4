<?php
/**
 * BH4 public directory adapter: converts caller-supplied public-safe records.
 *
 * No SQL, REST route registration, hooks or data mutations.
 */
defined( 'ABSPATH' ) || exit;

final class BH4_Directory_Adapter {
    /**
     * @param mixed $rows Already-authorised public activity projections.
     * @param mixed $limit Maximum response count, 1..100.
     * @return array<string,mixed>|WP_Error
     */
    public static function project( $rows, $limit = 20 ) {
        if ( ! is_array( $rows ) || ! is_int( $limit ) || $limit < 1 || $limit > 100 ) {
            return new WP_Error( 'bh4_invalid_directory_request', 'Invalid directory records or page size.' );
        }
        if ( ! class_exists( 'BH4_Activity_API_Contract', false ) ) {
            return new WP_Error( 'bh4_missing_contract', 'Activity API contract is unavailable.' );
        }
        $items = array();
        $seen = array();
        $skipped = 0;
        foreach ( $rows as $row ) {
            if ( ! is_array( $row ) || ( $row['status'] ?? null ) !== 'published' ) {
                ++$skipped;
                continue;
            }
            $projection = BH4_Activity_API_Contract::format_activity( $row );
            if ( $projection instanceof WP_Error ) {
                ++$skipped;
                continue;
            }
            $id = $projection['id'];
            if ( isset( $seen[ $id ] ) ) {
                ++$skipped;
                continue;
            }
            $seen[ $id ] = true;
            if ( count( $items ) < $limit ) {
                $items[] = $projection;
            }
        }
        return array(
            'schema_version' => 1,
            'type' => 'activity_collection',
            'items' => $items,
            'returned' => count( $items ),
            'has_more' => count( $seen ) > $limit,
            'skipped' => $skipped,
            'source' => 'caller_supplied_public_records',
        );
    }
}
