<?php
define('ABSPATH', __DIR__);
require dirname(__DIR__) . '/plugin/bubba-hub-manager/includes/class-bh4-upgrade-package-preflight.php';
$hash=str_repeat('a',64);
$valid=array('installed_plugin_basename'=>'bubbawp/bubbawp.php',
 'candidate_plugin_basename'=>'bubbawp/bubbawp.php','installed_package_sha256'=>$hash,
 'candidate_package_sha256'=>str_repeat('b',64),'rollback_package_sha256'=>$hash,
 'backup_verified'=>true,'rollback_restore_tested'=>true,'no_second_active_copy'=>true,
 'schema_writes_disabled'=>true,'staging_confirmed'=>true);
$cases=array(
 array($valid,true),
 array(array_replace($valid,array('candidate_plugin_basename'=>'bubba4/bubbawp.php')),false),
 array(array_replace($valid,array('installed_plugin_basename'=>'../bubbawp.php')),false),
 array(array_replace($valid,array('candidate_package_sha256'=>'not-a-hash')),false),
 array(array_replace($valid,array('rollback_package_sha256'=>'')),false),
 array(array_replace($valid,array('backup_verified'=>false)),false),
 array(array_replace($valid,array('rollback_restore_tested'=>false)),false),
 array(array_replace($valid,array('no_second_active_copy'=>false)),false),
 array(array_replace($valid,array('schema_writes_disabled'=>false)),false),
 array(array_replace($valid,array('staging_confirmed'=>false)),false),
 array(array(),false),
 array('invalid',false),
);
foreach($cases as $i=>$case) {
 $result=BH4_Upgrade_Package_Preflight::assess($case[0]);
 if($result['ready']!==$case[1] || $result['execution_allowed']!==false) {
  fwrite(STDERR,"FAIL upgrade package preflight ".($i+1)."\n");exit(1);
 }
}
echo "PASS: 12 upgrade package and rollback preflight fixtures\n";
