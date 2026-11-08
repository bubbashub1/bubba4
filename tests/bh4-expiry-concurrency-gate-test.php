<?php
define('ABSPATH',__DIR__);
require dirname(__DIR__).'/plugin/bubba-hub-manager/includes/class-bh4-expiry-concurrency-gate.php';
require dirname(__DIR__).'/plugin/bubba-hub-manager/includes/class-bh4-reservation-expiry-contract.php';
$keys=array('shared_booking_lock_held','booking_reloaded_under_lock',
 'booking_still_reserved_pending','hold_expired','no_successful_payment_event',
 'stripe_terminal_unpaid_verified','checkout_creation_not_in_flight');
$evidence=array_fill_keys($keys,true);
$eligible=BH4_Reservation_Expiry_Contract::decide('reserved','pending',100,200,'expired_unpaid');
$ok=BH4_Expiry_Concurrency_Gate::assess($eligible,$evidence);
if(!$ok['allowed']) {fwrite(STDERR,"FAIL verified terminal unpaid\n");exit(1);}
foreach($keys as $key) {
 $case=$evidence;unset($case[$key]);
 if(BH4_Expiry_Concurrency_Gate::assess($eligible,$case)['allowed']) {fwrite(STDERR,"FAIL missing $key\n");exit(1);}
 $case=$evidence;$case[$key]=1;
 if(BH4_Expiry_Concurrency_Gate::assess($eligible,$case)['allowed']) {fwrite(STDERR,"FAIL weak $key\n");exit(1);}
}
foreach(array(
 array('reserved','paid',100,200,'paid'),
 array('reserved','pending',100,200,'paid'),
 array('reserved','pending',100,200,'open'),
 array('confirmed','pending',100,200,'expired_unpaid'),
 array('reserved','pending',100,99,'expired_unpaid'),
) as $args) {
 if(BH4_Expiry_Concurrency_Gate::assess(BH4_Reservation_Expiry_Contract::decide(...$args),$evidence)['allowed']) {
  fwrite(STDERR,"FAIL unsafe payment/hold state\n");exit(1);
 }
}
echo "PASS: 20 expiry concurrency gate cases including paid and late payment\n";
