<?php
define('ABSPATH',__DIR__);
require dirname(__DIR__).'/plugin/bubba-hub-manager/includes/class-bh4-reconciliation-access-contract.php';
$cases=array(
 array(true,true,true,'GET',true,true),
 array(false,true,true,'GET',true,false),
 array(true,false,true,'GET',true,false),
 array(true,true,false,'GET',true,false),
 array(true,true,true,'POST',true,false),
 array(true,true,true,'GET',false,false),
 array(true,true,true,'get',true,false),
 array(true,true,true,'DELETE',true,false)
);
foreach($cases as $i=>$case) {
 $expected=array_pop($case);
 $actual=BH4_Reconciliation_Access_Contract::authorize(...$case);
 if($actual['allowed']!==$expected){exit("FAIL role/access case $i\n");}
}
$view=file_get_contents(dirname(__DIR__).'/plugin/bubba-hub-manager/includes/class-bh4-checkout-recovery-admin-review.php');
if(strpos($view,"add_submenu_page(null,")===false){exit("FAIL menu exposes unsigned link\n");}
$boot=file_get_contents(dirname(__DIR__).'/plugin/bubba-hub-manager/bubba-hub-manager.php');
if(strpos($boot,"wp_nonce_url(")===false){exit("FAIL missing signed entry link\n");}
echo "PASS: 8 administrator, leader/family, method, and nonce policy cases; hidden signed-link navigation\n";
