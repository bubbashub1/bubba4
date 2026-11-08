<?php
define( 'ABSPATH', __DIR__ . '/' );
class WP_Error { public function __construct( $code, $message ) {} }
require_once dirname( __DIR__ ) . '/plugin/bubba-hub-manager/includes/class-bh4-directory-card-details.php';
function check_card( $ok, $label ) { if ( ! $ok ) { fwrite( STDERR, "FAIL: {$label}\n" ); exit( 1 ); } echo "PASS: {$label}\n"; }
$r = BH4_Directory_Card_Details::project( array(
 'age_min_months' => 0, 'age_max_months' => '13',
 'organiser' => 'Baby Sensory', 'image_path' => '/wp-content/uploads/2026/11/baby-group.jpg',
 'parent_email' => 'private', 'leader_phone' => 'private',
) );
check_card( $r['age_min_months'] === 0 && $r['age_max_months'] === 13, 'newborn age range retained' );
check_card( $r['organiser_name'] === 'Baby Sensory', 'public organiser name retained' );
check_card( $r['image_url_verified'] === false && $r['execution_allowed'] === false, 'image and execution not approved' );
check_card( ! isset( $r['parent_email'] ) && ! isset( $r['leader_phone'] ), 'private fields excluded' );
check_card( BH4_Directory_Card_Details::project( array( 'age_min_months' => 14, 'age_max_months' => 5 ) ) instanceof WP_Error, 'reversed age range rejected' );
check_card( BH4_Directory_Card_Details::project( array( 'image_path' => '/wp-content/uploads/../../private.txt' ) ) instanceof WP_Error, 'path traversal rejected' );
check_card( BH4_Directory_Card_Details::project( array( 'image_path' => 'https://other.example/photo.jpg' ) ) instanceof WP_Error, 'unverified external image rejected' );
check_card( BH4_Directory_Card_Details::project( array() )['age_min_months'] === null, 'unknown age remains unknown' );
echo "BH4 directory card details fixtures complete.\n";
