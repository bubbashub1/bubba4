<?php
define('ABSPATH',__DIR__);
require dirname(__DIR__).'/plugin/bubba-hub-manager/includes/class-bh4-checkout-timeout-recovery-gate.php';
$cases=array(
 array('reserved','pending',1,1,true,true,true,'persist_verified_existing'),
 array('reserved','pending',0,0,true,true,true,'reconcile_without_replay'),
 array('reserved','pending',2,2,true,true,true,'manual_review'),
 array('reserved','pending',2,1,true,true,true,'manual_review'),
 array('reserved','pending',1,0,true,true,true,'manual_review'),
 array('reserved','pending',1,1,false,true,true,'manual_review'),
 array('reserved','pending',1,1,true,false,true,'manual_review'),
 array('reserved','pending',1,1,true,true,false,'block'),
 array('confirmed','pending',1,1,true,true,true,'block'),
 array('reserved','paid',1,1,true,true,true,'block'),
 array('reserved','pending',-1,0,true,true,true,'manual_review'),
 array('reserved','pending',1,2,true,true,true,'manual_review'),
 array('reserved','pending','1',1,true,true,true,'manual_review'),
);
foreach($cases as $i=>$case) {
 $expected=array_pop($case);
 $result=BH4_Checkout_Timeout_Recovery_Gate::assess(...$case);
 if($result['action']!==$expected) {fwrite(STDERR,"FAIL timeout recovery case $i\n");exit(1);}
}
echo "PASS: 13 Stripe checkout timeout and duplicate candidate scenarios\n";
