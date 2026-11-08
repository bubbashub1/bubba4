<?php
/** Static safety checks for known schema-changing WordPress admin endpoints. */
$root=dirname(__DIR__).'/plugin/bubba-hub-manager/';
$cases=array(
 'includes/class-bh-family-db-setup.php'=>array('public static function create('),
 'includes/class-bh-schema.php'=>array('public static function install(', 'public static function install_missing_booking_tables('),
);
foreach($cases as $relative=>$methods) {
 $source=file_get_contents($root.$relative);
 if(!is_string($source)) {fwrite(STDERR,"FAIL unreadable: $relative\n");exit(1);}
 foreach($methods as $signature) {
  $start=strpos($source,$signature);
  if($start===false) {fwrite(STDERR,"FAIL missing schema entrypoint: $signature\n");exit(1);}
  $body=substr($source,$start,1800);
  if(strpos($body,'BH4_ALLOW_STAGING_SCHEMA_CHANGES')===false) {
   fwrite(STDERR,"FAIL missing explicit schema change gate near $signature\n");exit(1);
  }
 }
}
$manager=file_get_contents($root.'includes/class-bh-manager.php');
foreach(array('admin_post_bh3_install_schema','admin_post_bh3_install_missing_booking_tables') as $hook) {
 if(strpos($manager,$hook)===false) {fwrite(STDERR,"FAIL missing known schema hook $hook\n");exit(1);}
}
echo "PASS: known family/core/booking schema entrypoints retain explicit staging-write gate\n";
echo "NOTE: static source check only; dynamic hooks and runtime DB writes remain unverified\n";
