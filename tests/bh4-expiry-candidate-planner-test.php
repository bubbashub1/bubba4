<?php
define('ABSPATH', __DIR__);
require dirname(__DIR__) . '/plugin/bubba-hub-manager/includes/class-bh4-expiry-candidate-planner.php';
$rows = array(
    array('id'=>1,'booking_status'=>'reserved','payment_status'=>'pending','expires_at'=>99),
    array('id'=>2,'booking_status'=>'reserved','payment_status'=>'pending','expires_at'=>101),
    array('id'=>3,'booking_status'=>'confirmed','payment_status'=>'paid','expires_at'=>90),
    array('id'=>4,'booking_status'=>'reserved','payment_status'=>'paid','expires_at'=>90),
    array('id'=>5,'booking_status'=>'reserved','payment_status'=>'pending','expires_at'=>100),
    array('id'=>6,'booking_status'=>'expired','payment_status'=>'pending','expires_at'=>90),
);
$r = BH4_Expiry_Candidate_Planner::plan($rows,100);
if (!$r['ok'] || array_column($r['candidates'],'booking_id') !== array(1,5)
    || array_unique(array_column($r['candidates'],'action')) !== array('reconcile_only')) {
    fwrite(STDERR, "FAIL: eligible candidates must be reconciliation-only\n"); exit(1);
}
$limited = BH4_Expiry_Candidate_Planner::plan($rows,100,1);
if (count($limited['candidates']) !== 1 || $limited['candidates'][0]['booking_id'] !== 1) {
    fwrite(STDERR, "FAIL: bounded batch\n"); exit(1);
}
$invalid = BH4_Expiry_Candidate_Planner::plan($rows,100,101);
if ($invalid['ok'] || $invalid['candidates'] !== array()) {
    fwrite(STDERR, "FAIL: invalid limit must fail closed\n"); exit(1);
}
echo "PASS: read-only expiry candidate planner (no releases or payment requests)\n";
