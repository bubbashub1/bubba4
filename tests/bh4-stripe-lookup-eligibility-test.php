<?php
define('ABSPATH',__DIR__);
require dirname(__DIR__).'/plugin/bubba-hub-manager/includes/class-bh4-stripe-lookup-eligibility.php';
$b=array('id'=>42,'stripe_checkout_session_id'=>'cs_test_ABC123');
$r=array('booking_id'=>42,'state'=>'needs_reconciliation','session_id'=>null);
if(BH4_Stripe_Lookup_Eligibility::assess($b,$r)['allowed']!==true){exit("FAIL known session\n");}
$cases=array(
 array(array('id'=>42,'stripe_checkout_session_id'=>null),$r),
 array(array('id'=>42,'stripe_checkout_session_id'=>'cs_live_X'),$r),
 array($b,array('booking_id'=>43,'state'=>'needs_reconciliation','session_id'=>null)),
 array($b,array('booking_id'=>42,'state'=>'unknown','session_id'=>null)),
 array($b,array('booking_id'=>42,'state'=>'saved','session_id'=>'cs_test_OTHER')),
 array(array('id'=>42,'stripe_checkout_session_id'=>''),$r),
 array(array('id'=>0,'stripe_checkout_session_id'=>'cs_test_ABC123'),$r)
);
foreach($cases as $i=>$case){
 if(BH4_Stripe_Lookup_Eligibility::assess($case[0],$case[1])['allowed']!==false){exit("FAIL eligibility $i\n");}
}
echo "PASS: known-session eligibility and 7 fail-closed cases\n";
