<?php
define('ABSPATH',__DIR__);
$source=file_get_contents(dirname(__DIR__).'/plugin/bubba-hub-manager/includes/class-bh4-checkout-recovery-admin-review.php');
$checks=array(
 'No unsafe maybe_unserialize'=>'maybe_unserialize(',
 'No payment confirmation'=>'update_post_meta(',
 'No Stripe create request'=>'wp_remote_post(',
 'No direct option update'=>'update_option('
);
foreach($checks as $label=>$forbidden){
 if(strpos($source,$forbidden)!==false){exit("FAIL $label\n");}
}
foreach(array("allowed_classes'=>false","strlen(\$raw)>8192","wp_verify_nonce(\$nonce,'bh4_recovery_review')!==false","Stripe payment status has not been independently verified") as $required){
 if(strpos($source,$required)===false){exit("FAIL missing guard $required\n");}
}
echo "PASS: recovery review deserialization, nonce and no-mutation source guards\n";
