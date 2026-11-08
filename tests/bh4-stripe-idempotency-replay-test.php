<?php
define('ABSPATH', __DIR__);
require dirname(__DIR__) . '/plugin/bubba-hub-manager/includes/class-bh4-stripe-idempotency-replay-contract.php';
$cases=array(
 array('reserved','pending','',true,5,'ambiguous_timeout','reconcile_without_replay'),
 array('reserved','pending','',true,60,'not_found','reconcile_without_replay'),
 array('reserved','pending','',true,86400,'not_found','reconcile_without_replay'),
 array('reserved','pending','',true,5,'lookup_failed','reconcile_without_replay'),
 array('reserved','pending','',true,5,'verified_existing_session','verify_and_persist_existing'),
 array('reserved','pending','cs_test_saved',true,5,'unknown','verify_saved_session'),
 array('reserved','pending','',false,5,'ambiguous_timeout','manual_review'),
 array('confirmed','paid','',true,5,'unknown','block'),
 array('expired','pending','',true,5,'unknown','block'),
 array('reserved','paid','',true,5,'unknown','block'),
 array('reserved','pending','',true,-1,'unknown','manual_review'),
 array('reserved','pending','',true,5,'unexpected','manual_review'),
);
foreach($cases as $i=>$c) {
 $r=BH4_Stripe_Idempotency_Replay_Contract::decide($c[0],$c[1],$c[2],$c[3],$c[4],$c[5]);
 if($r['action']!==$c[6]) { fwrite(STDERR,"FAIL replay fixture ".($i+1)."\n");exit(1); }
}
echo "PASS: 12 fail-closed idempotency replay fixtures\n";
