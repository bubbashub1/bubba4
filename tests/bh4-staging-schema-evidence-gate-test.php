<?php
define('ABSPATH', __DIR__);
require dirname(__DIR__) . '/plugin/bubba-hub-manager/includes/class-bh4-staging-schema-evidence-gate.php';
$empty=BH4_Staging_Schema_Evidence_Gate::assess(array());
if($empty['ready'] || count($empty['missing'])!==11 || $empty['writes_allowed']) {fwrite(STDERR,"FAIL: incomplete evidence allowed\n");exit(1);}
$all=array_fill_keys($empty['evidence_keys'],true);
$ok=BH4_Staging_Schema_Evidence_Gate::assess($all);
if(!$ok['ready'] || $ok['writes_allowed'] || $ok['migration_authorized']) {fwrite(STDERR,"FAIL: complete evidence authorised writes\n");exit(1);}
foreach($empty['evidence_keys'] as $key) {
 $missing=$all;unset($missing[$key]);
 $result=BH4_Staging_Schema_Evidence_Gate::assess($missing);
 if($result['ready'] || $result['missing']!==array($key)) {fwrite(STDERR,"FAIL missing key $key\n");exit(1);}
 $wrong=$all;$wrong[$key]=1;
 if(BH4_Staging_Schema_Evidence_Gate::assess($wrong)['ready']) {fwrite(STDERR,"FAIL nonboolean key $key\n");exit(1);}
}
echo "PASS: 11 read-only staging schema evidence gates\n";
