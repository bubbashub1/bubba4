<?php
$source=file_get_contents(dirname(__DIR__).'/plugin/bubba-hub-manager/includes/class-bh-marketplace-settings.php');
$start=strpos($source,'function create_test_checkout(');
$end=strpos($source,'Internal-only signed Stripe event verifier',$start);
if($start===false||$end===false){exit("FAIL missing checkout\n");}
$body=substr($source,$start,$end-$start);
$cases=array(
 array("wp_remote_retrieve_response_code( \$response ) !== 200","'remote_rejected'"),
 array("if ( \$saved !== 1 )","'Checkout may exist but could not be saved."),
 array("if ( is_wp_error( \$response ) )","'remote_timeout'")
);
foreach($cases as $i=>$case) {
 $start=strpos($body,str_replace('\\$','$',$case[0]));
 if($start===false){exit("FAIL branch $i missing\n");}
 $slice=substr($body,$start,1700);
 if(strpos($slice,$case[1])===false){exit("FAIL branch $i does not reconcile\n");}
}
echo "PASS: timeout, invalid Stripe response, and unsaved session fail closed\n";
