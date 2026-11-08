<?php
define('ABSPATH',__DIR__);
require dirname(__DIR__).'/plugin/bubba-hub-manager/includes/class-bh4-checkout-recovery-cas-contract.php';
require dirname(__DIR__).'/plugin/bubba-hub-manager/includes/class-bh4-checkout-recovery-atomic-writer.php';
function maybe_unserialize($x){return unserialize($x);}
function maybe_serialize($x){return serialize($x);}
function wp_cache_delete($key,$group){$GLOBALS['cache_deleted'][]=$key;}
class BH4_Fake_DB {
 public $options='wp_options',$prefix='wp_',$owner=true,$stale=false,$value,$updates=0;
 public function prepare($sql,...$args){return array($sql,$args);}
 public function get_var($query){
  if(is_string($query)) {return 44;}
  if(strpos($query[0],'IS_USED_LOCK')!==false) {return $this->owner?44:55;}
  return $this->value;
 }
 public function query($query){
  $args=$query[1];$old=$args[2];$new=$args[0];
  if($this->stale||$old!==$this->value) {return 0;}
  $this->value=$new;$this->updates++;return 1;
 }
}
$wpdb=new BH4_Fake_DB();
$base=array('booking_id'=>5,'request_fingerprint'=>str_repeat('c',64),
 'created_at'=>100,'state'=>'creating','session_id'=>null);
$next=array_merge($base,array('state'=>'needs_reconciliation'));
$wpdb->value=serialize($base);
$wpdb->owner=false;
if(BH4_Checkout_Recovery_Atomic_Writer::update(5,'creating',$next)) {exit("FAIL foreign lock\n");}
$wpdb->owner=true;$wpdb->stale=true;
if(BH4_Checkout_Recovery_Atomic_Writer::update(5,'creating',$next)) {exit("FAIL stale update\n");}
$wpdb->stale=false;
if(!BH4_Checkout_Recovery_Atomic_Writer::update(5,'creating',$next)) {exit("FAIL atomic transition\n");}
if(BH4_Checkout_Recovery_Atomic_Writer::update(5,'creating',$next)) {exit("FAIL duplicate transition\n");}
if($wpdb->updates!==1||count($GLOBALS['cache_deleted']??array())!==2) {exit("FAIL writes/cache\n");}
echo "PASS: lock ownership, stale row, atomic transition and replay rejection\n";
