<?php
define('ABSPATH',__DIR__);
require dirname(__DIR__).'/plugin/bubba-hub-manager/includes/class-bh4-checkout-recovery-cas-contract.php';
$base=array('booking_id'=>17,'request_fingerprint'=>str_repeat('b',64),
 'created_at'=>100,'state'=>'creating','session_id'=>null);
$timeout=array_merge($base,array('state'=>'needs_reconciliation'));
$saved=array_merge($base,array('state'=>'saved','session_id'=>'cs_test_abc'));
$cases=array(
 array($base,$timeout,'creating',true,true),
 array($base,$saved,'creating',true,true),
 array($timeout,$saved,'needs_reconciliation',true,true),
 array($timeout,array_merge($timeout,array('state'=>'manual_review')),'needs_reconciliation',true,true),
 array($base,$timeout,'creating',false,false),
 array($base,$timeout,'saved',true,false),
 array($base,array_merge($timeout,array('booking_id'=>18)),'creating',true,false),
 array($base,array_merge($timeout,array('request_fingerprint'=>str_repeat('a',64))),'creating',true,false),
 array($base,array_merge($timeout,array('created_at'=>101)),'creating',true,false),
 array($base,array_merge($saved,array('session_id'=>'cs_live_bad')),'creating',true,false),
 array($base,array_merge($saved,array('session_id'=>null)),'creating',true,false),
 array($saved,$timeout,'saved',true,false),
 array($base,array_merge($base,array('state'=>'manual_review')),'creating',true,false),
);
foreach($cases as $i=>$case) {
 $expected=array_pop($case);
 if(BH4_Checkout_Recovery_CAS_Contract::assess(...$case)!==$expected) {exit("FAIL CAS case $i\n");}
}
echo "PASS: 13 recovery CAS validation scenarios\n";
