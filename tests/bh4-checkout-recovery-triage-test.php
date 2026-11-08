<?php
define('ABSPATH',__DIR__);
require dirname(__DIR__).'/plugin/bubba-hub-manager/includes/class-bh4-checkout-recovery-triage.php';
$base=array('created_at'=>100);
$cases=array(
 array(array_merge($base,array('state'=>'creating')),150,'pending'),
 array(array_merge($base,array('state'=>'creating')),280,'urgent'),
 array(array_merge($base,array('state'=>'needs_reconciliation')),101,'urgent'),
 array(array_merge($base,array('state'=>'manual_review')),101,'urgent'),
 array(array_merge($base,array('state'=>'saved')),101,'resolved'),
 array(array_merge($base,array('state'=>'unknown')),101,'review'),
 array(array('state'=>'creating','created_at'=>999),101,'review'),
 array(array('state'=>'creating','created_at'=>'100'),101,'review')
);
foreach($cases as $i=>$case) {
 if(BH4_Checkout_Recovery_Triage::classify($case[0],$case[1])['priority']!==$case[2]){
  exit("FAIL triage case $i\n");
 }
}
echo "PASS: 8 recovery triage classifications\n";
