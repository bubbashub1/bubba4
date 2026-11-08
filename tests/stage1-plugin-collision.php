<?php
/**
 * Stage 1 isolated bootstrap collision regression.
 * Run: php tests/stage1-plugin-collision.php
 * No WordPress installation, database or network required.
 */
error_reporting( E_ALL );
define( 'ABSPATH', __DIR__ . '/' );
$actions = array();
function add_action( $hook, $callback ) {
    global $actions;
    $actions[ $hook ][] = $callback;
}
function current_user_can( $capability ) { return true; }
function esc_html__( $message, $domain ) { return $message; }

// Simulate an already-active legacy BubbaWP installation.
final class BH_Manager_3 {}
require dirname( __DIR__ ) . '/plugin/bubba-hub-manager/bubba-hub-manager.php';

if ( defined( 'BUBBA_HUB_MANAGER_PATH' ) || defined( 'BUBBA_HUB_MANAGER_VERSION' ) ) {
    fwrite( STDERR, "Collision guard allowed a second plugin bootstrap.\n" );
    exit( 1 );
}
if ( ! isset( $actions['admin_notices'] ) || count( $actions['admin_notices'] ) !== 1 ) {
    fwrite( STDERR, "Collision guard did not register an admin notice.\n" );
    exit( 1 );
}
ob_start();
$actions['admin_notices'][0]();
$notice = ob_get_clean();
if ( strpos( $notice, 'another Bubba Hub Manager installation' ) === false ) {
    fwrite( STDERR, "Collision notice is missing or incorrect.\n" );
    exit( 1 );
}
echo "Stage 1 collision guard: PASS\n";
