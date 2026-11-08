<?php
define('ABSPATH', __DIR__);
require dirname(__DIR__) . '/plugin/bubba-hub-manager/includes/class-bh4-checkout-retry-contract.php';
$cases = array(
    array('reserved','pending','','not_attempted','not_checked','eligible_for_locked_creation'),
    array('reserved','pending','','timeout','unknown','reconcile_without_creation'),
    array('reserved','pending','','network_error','not_found','reconcile_without_creation'),
    array('reserved','pending','','unknown_response','processing','reconcile_without_creation'),
    array('reserved','pending','','timeout','matching_test_session_unpaid','persist_verified_session_under_lock'),
    array('reserved','pending','','verified_success','matching_test_session_unpaid','persist_verified_session_under_lock'),
    array('reserved','pending','cs_test_known','not_attempted','not_checked','reuse_existing_after_verification'),
    array('confirmed','paid','','timeout','unknown','block'),
    array('expired','pending','','not_attempted','not_checked','block'),
    array('reserved','paid','','not_attempted','not_checked','block'),
    array('reserved','pending','','verified_success','unknown','manual_review'),
    array('reserved','pending','','unexpected','unknown','manual_review'),
);
foreach ($cases as $i=>$c) {
    $result = BH4_Checkout_Retry_Contract::decide($c[0],$c[1],$c[2],$c[3],$c[4]);
    if ($result['action'] !== $c[5]) {
        fwrite(STDERR,"FAIL checkout retry fixture ".($i+1).": ".$result['action']."\n"); exit(1);
    }
}
echo "PASS: 12 fail-closed Stripe checkout retry fixtures\n";
