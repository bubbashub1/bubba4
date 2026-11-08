<?php
define('ABSPATH',__DIR__);
require dirname(__DIR__).'/plugin/bubba-hub-manager/includes/class-bh4-checkout-recovery-record-contract.php';
$f=str_repeat('a',64);
$valid=BH4_Checkout_Recovery_Record_Contract::create(12,$f,100);
if(!$valid['ok']||$valid['record']['state']!=='creating') {exit("FAIL create\n");}
foreach(array(array(0,$f,100),array(12,'abc',100),array(12,$f,0)) as $args) {
 if(BH4_Checkout_Recovery_Record_Contract::create(...$args)['ok']) {exit("FAIL invalid record\n");}
}
$timeout=BH4_Checkout_Recovery_Record_Contract::transition($valid['record'],'remote_timeout');
if(!$timeout['ok']||$timeout['record']['state']!=='needs_reconciliation') {exit("FAIL timeout\n");}
if(BH4_Checkout_Recovery_Record_Contract::may_create_new_session($timeout['record'])) {exit("FAIL duplicate creation\n");}
$review=BH4_Checkout_Recovery_Record_Contract::transition($timeout['record'],'review_required');
if(!$review['ok']||$review['record']['state']!=='manual_review') {exit("FAIL review\n");}
foreach(array($valid['record'],$timeout['record'],$review['record']) as $record) {
 $saved=BH4_Checkout_Recovery_Record_Contract::transition($record,'verified_session_saved','cs_test_abc123');
 if(!$saved['ok']||$saved['record']['state']!=='saved'||$saved['record']['session_id']!=='cs_test_abc123') {exit("FAIL verified save\n");}
 if(BH4_Checkout_Recovery_Record_Contract::transition($saved['record'],'remote_timeout')['ok']) {exit("FAIL saved regression\n");}
 if(BH4_Checkout_Recovery_Record_Contract::may_create_new_session($saved['record'])) {exit("FAIL saved duplicate\n");}
}
foreach(array(
 array($timeout['record'],'remote_timeout',null),
 array($valid['record'],'verified_session_saved','cs_live_123'),
 array($valid['record'],'verified_session_saved',null),
 array($valid['record'],'remote_timeout','cs_test_abc'),
 array($valid['record'],'unrecognised',null)
) as $case) {
 if(BH4_Checkout_Recovery_Record_Contract::transition(...$case)['ok']) {exit("FAIL unsafe transition\n");}
}
echo "PASS: recovery record validation, timeout, manual review, verified save and duplicate prevention\n";
