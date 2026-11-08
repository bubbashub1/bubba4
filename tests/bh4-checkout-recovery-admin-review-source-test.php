<?php
$s=file_get_contents(dirname(__DIR__).'/plugin/bubba-hub-manager/includes/class-bh4-checkout-recovery-admin-review.php');
foreach(array("manage_options","BH4_Reconciliation_Access_Contract::authorize","wp_verify_nonce","LIMIT 50","esc_html","Read-only") as $needle){
 if(strpos($s,$needle)===false){exit("FAIL missing $needle\n");}
}
foreach(array('update_option(', 'delete_option(', 'wp_remote_post(', 'stripe_checkout_session_id=%s') as $needle){
 if(strpos($s,$needle)!==false){exit("FAIL mutating operation $needle\n");}
}
if(strpos($s,'add_action(')!==false){exit("FAIL unexpected auto registration\n");}
echo "PASS: admin recovery view remains read-only, capability and nonce gated\n";
