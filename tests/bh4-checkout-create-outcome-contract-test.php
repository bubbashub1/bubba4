<?php
define('ABSPATH',__DIR__);
require dirname(__DIR__).'/plugin/bubba-hub-manager/includes/class-bh4-checkout-create-outcome-contract.php';
$cases=array(
 array(true,null,false,false,'reconcile_without_replay'),
 array(true,200,true,false,'reconcile_without_replay'),
 array(false,200,true,true,'return_saved_checkout'),
 array(false,200,true,false,'reconcile_without_replay'),
 array(false,200,false,false,'reconcile_without_replay'),
 array(false,409,false,false,'reconcile_without_replay'),
 array(false,500,false,false,'reconcile_without_replay'),
 array(false,0,false,false,'reconcile_without_replay'),
 array(null,200,true,true,'manual_review'),
 array(false,'200',true,true,'manual_review'),
 array(false,200,1,true,'manual_review'),
 array(false,200,true,1,'manual_review'),
);
foreach($cases as $i=>$case) {
 $expected=array_pop($case);
 $actual=BH4_Checkout_Create_Outcome_Contract::decide(...$case);
 if($actual['action']!==$expected) {fwrite(STDERR,"FAIL Stripe outcome case $i\n");exit(1);}
}
echo "PASS: 12 Stripe create outcome safety cases\n";
