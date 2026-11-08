<?php
define('ABSPATH',__DIR__);
function maybe_unserialize($value){return unserialize($value);}
function maybe_serialize($value){return serialize($value);}
function wp_cache_delete($key,$group){$GLOBALS['cache_events'][]=$key;}
function add_option($key,$value,$deprecated='',$autoload='yes'){return false;}
function get_option($key,$default=null){return $default;}
class BH4_Recovery_Integration_DB {
 public $prefix='wp_',$options='wp_options',$value,$owner=true,$writes=0;
 public function prepare($query,...$args){return array($query,$args);}
 public function get_var($query){
  if(is_string($query)){return 88;}
  if(strpos($query[0],'IS_USED_LOCK')!==false){return $this->owner?88:89;}
  return $this->value;
 }
 public function query($query){
  if($query[1][2]!==$this->value){return 0;}
  $this->value=$query[1][0];$this->writes++;return 1;
 }
}
require dirname(__DIR__).'/plugin/bubba-hub-manager/includes/class-bh4-checkout-recovery-cas-contract.php';
require dirname(__DIR__).'/plugin/bubba-hub-manager/includes/class-bh4-checkout-recovery-atomic-writer.php';
require dirname(__DIR__).'/plugin/bubba-hub-manager/includes/class-bh4-checkout-recovery-option-store.php';
$wpdb=new BH4_Recovery_Integration_DB();
$before=array('booking_id'=>33,'request_fingerprint'=>str_repeat('d',64),'created_at'=>120,'state'=>'creating','session_id'=>null);
$after=array_merge($before,array('state'=>'needs_reconciliation'));
$wpdb->value=serialize($before);
$wpdb->owner=false;
if(BH4_Checkout_Recovery_Option_Store::update_if_state(33,'creating',$after)!==false){exit("FAIL foreign lock\n");}
$wpdb->owner=true;
if(BH4_Checkout_Recovery_Option_Store::update_if_state(33,'creating',$after)!==true){exit("FAIL valid update\n");}
if(BH4_Checkout_Recovery_Option_Store::update_if_state(33,'creating',$after)!==false){exit("FAIL stale replay\n");}
if($wpdb->writes!==1){exit("FAIL write count\n");}
echo "PASS: adapter delegates to lock-owned atomic writer and rejects replay\n";
