<?php
define('ABSPATH',__DIR__);
$GLOBALS['bh4_options']=array();
function add_option($key,$value,$deprecated='',$autoload='yes') {
 if(array_key_exists($key,$GLOBALS['bh4_options'])) {return false;}
 if($autoload!=='no') {exit("FAIL autoload must be disabled\n");}
 $GLOBALS['bh4_options'][$key]=$value;return true;
}
function get_option($key,$default=false) {
 return $GLOBALS['bh4_options'][$key]??$default;
}
require dirname(__DIR__).'/plugin/bubba-hub-manager/includes/class-bh4-checkout-recovery-option-store.php';
require dirname(__DIR__).'/plugin/bubba-hub-manager/includes/class-bh4-checkout-recovery-record-contract.php';
$r=BH4_Checkout_Recovery_Record_Contract::create(123,str_repeat('a',64),100)['record'];
if(!BH4_Checkout_Recovery_Option_Store::insert_once($r)) {exit("FAIL first insert\n");}
if(BH4_Checkout_Recovery_Option_Store::insert_once($r)) {exit("FAIL duplicate insert\n");}
if(BH4_Checkout_Recovery_Option_Store::read(123)!==$r) {exit("FAIL read\n");}
if(BH4_Checkout_Recovery_Option_Store::read(999)!==null) {exit("FAIL missing read\n");}
if(BH4_Checkout_Recovery_Option_Store::insert_once(array_merge($r,array('state'=>'saved')))) {exit("FAIL invalid insert\n");}
if(BH4_Checkout_Recovery_Option_Store::update_if_state(123,'creating',array_merge($r,array('state'=>'saved')))) {exit("FAIL unsafe update\n");}
if(count($GLOBALS['bh4_options'])!==1) {exit("FAIL unexpected write\n");}
echo "PASS: insert-only, no overwrite, no autoload, no unsafe transition\n";
