<?php
define('ABSPATH', __DIR__);
require dirname(__DIR__) . '/plugin/bubba-hub-manager/includes/class-bh4-reconciliation-access-contract.php';
$cases=array(
 array(true,true,true,'GET',true,true),
 array(false,true,true,'GET',true,false),
 array(true,false,true,'GET',true,false),
 array(true,true,false,'GET',true,false),
 array(true,true,true,'POST',true,false),
 array(true,true,true,'DELETE',true,false),
 array(true,true,true,'GET',false,false),
 array(true,true,true,'GET',null,false),
 array(1,true,true,'GET',true,false),
 array(true,1,true,'GET',true,false),
 array(true,true,1,'GET',true,false),
 array(true,true,true,'get',true,false),
);
foreach($cases as $i=>$c) {
 $r=BH4_Reconciliation_Access_Contract::authorize($c[0],$c[1],$c[2],$c[3],$c[4]);
 if($r['allowed']!==$c[5]) { fwrite(STDERR,"FAIL access fixture ".($i+1)."\n");exit(1); }
}
echo "PASS: 12 admin-only read-only reconciliation access fixtures\n";
