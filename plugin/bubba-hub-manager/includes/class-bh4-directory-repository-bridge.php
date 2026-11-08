<?php
/** Passive adapter for published-only BH_Activity_Repository_3 output. No hooks or writes. */
defined( 'ABSPATH' ) || exit;
final class BH4_Directory_Repository_Bridge {
    public static function preview( $repository, $args = array() ) {
        if ( ! is_object( $repository ) || ! ( $repository instanceof BH_Activity_Repository_3 ) || ! is_array( $args ) ) {
            return new WP_Error( 'bh4_invalid_repository', 'Invalid read-only repository.' );
        }
        $limit = $args['per_page'] ?? 12;
        if ( ! is_int( $limit ) || $limit < 1 || $limit > 24 ) {
            return new WP_Error( 'bh4_invalid_page_size', 'Page size must be 1..24.' );
        }
        $rows = $repository->search( $args );
        if ( $rows instanceof WP_Error ) { return $rows; }
        if ( ! is_array( $rows ) ) { return new WP_Error( 'bh4_invalid_rows', 'Invalid repository result.' ); }
        $public = array();
        foreach ( $rows as $row ) {
            if ( ! is_array( $row ) ) { return new WP_Error( 'bh4_invalid_row', 'Invalid repository row.' ); }
            // The concrete repository SQL already selects published/publish only.
            $public[] = array( 'id' => $row['id'] ?? null, 'title' => $row['title'] ?? null, 'slug' => $row['slug'] ?? null, 'status' => 'published' );
        }
        $result = BH4_Directory_Adapter::project( $public, $limit );
        if ( $result instanceof WP_Error ) { return $result; }
        // Repository search is paginated already: a next page is not proven.
        $result['has_more'] = null;
        $result['pagination_verified'] = false;
        $result['source'] = 'bh_activity_repository_3_published_search';
        return $result;
    }
}
