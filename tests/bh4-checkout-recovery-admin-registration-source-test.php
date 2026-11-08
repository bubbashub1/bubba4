<?php
$bootstrap=file_get_contents(dirname(__DIR__).'/plugin/bubba-hub-manager/bubba-hub-manager.php');
$review=file_get_contents(dirname(__DIR__).'/plugin/bubba-hub-manager/includes/class-bh4-checkout-recovery-admin-review.php');
foreach(array('BH4_ENABLE_RECOVERY_ADMIN_REVIEW','WP_DEBUG','admin_menu',
 'BH4_Checkout_Recovery_Admin_Review','wp_nonce_url','current_user_can') as $needle){
 if(strpos($bootstrap,$needle)===false){exit("FAIL missing bootstrap guard $needle\n");}
}
foreach(array('manage_options','wp_verify_nonce','BH4_Reconciliation_Access_Contract::authorize',
 'is_admin()') as $needle){
 if(strpos($review,$needle)===false){exit("FAIL missing render guard $needle\n");}
}
echo "PASS: optional recovery admin registration and nonce/capability checks\n";
