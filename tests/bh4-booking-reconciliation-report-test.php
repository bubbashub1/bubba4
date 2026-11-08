<?php
define('ABSPATH', __DIR__);
require dirname(__DIR__) . '/plugin/bubba-hub-manager/includes/class-bh4-booking-reconciliation-report.php';
$input=array(
 array('booking_id'=>42,'action'=>'manual_review','reason'=>'duplicate_sessions',
       'child_name'=>'Sensitive child','email'=>'parent@example.com','stripe_secret'=>'sk_test_sensitive'),
 array('booking_id'=>43,'action'=>'verify_unique_candidate','reason'=>'one_matching_unpaid_session',
       'parent_phone'=>'0000000'),
);
$r=BH4_Booking_Reconciliation_Report::build($input);
if (!$r['ok'] || count($r['rows'])!==2 || array_keys($r['rows'][0])!==array('booking_id','action','reason')) {
 fwrite(STDERR,"FAIL: report allowlist\n");exit(1);
}
$encoded=json_encode($r);
foreach(array('Sensitive child','parent@example.com','sk_test_sensitive','0000000') as $secret) {
 if(strpos($encoded,$secret)!==false) { fwrite(STDERR,"FAIL: personal data leak\n");exit(1); }
}
$limited=BH4_Booking_Reconciliation_Report::build($input,1);
if (!$limited['ok'] || count($limited['rows'])!==1) { fwrite(STDERR,"FAIL: row limit\n");exit(1); }
foreach(array(
 array('booking_id'=>0,'action'=>'manual_review','reason'=>'duplicate_sessions'),
 array('booking_id'=>42,'action'=>'approve_refund','reason'=>'duplicate_sessions'),
 array('booking_id'=>42,'action'=>'manual_review','reason'=>'unsafe reason!'),
) as $bad) {
 $failed=BH4_Booking_Reconciliation_Report::build(array($bad));
 if($failed['ok'] || $failed['rows']!==array()) { fwrite(STDERR,"FAIL: unsafe row accepted\n");exit(1); }
}
echo "PASS: privacy-minimised reconciliation report fixtures\n";
