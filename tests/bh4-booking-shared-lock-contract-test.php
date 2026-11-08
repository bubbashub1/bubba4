<?php
define('ABSPATH', __DIR__);
require dirname(__DIR__) . '/plugin/bubba-hub-manager/includes/class-bh4-booking-shared-lock-contract.php';
$lock = BH4_Booking_Shared_Lock_Contract::name('wp_',42);
if ($lock === '' || $lock !== BH4_Booking_Shared_Lock_Contract::name('wp_',42)
    || $lock === BH4_Booking_Shared_Lock_Contract::name('wp_',43)
    || $lock === BH4_Booking_Shared_Lock_Contract::name('other_',42)
    || strlen($lock) > 64) {
    fwrite(STDERR,"FAIL: stable scoped lock name\n"); exit(1);
}
$cases = array(
    array(42,true,true,true,'verified_paid','confirm_payment_only'),
    array(42,true,true,true,'verified_expired_unpaid','expire_hold_only'),
    array(42,true,true,true,'stripe_processing','manual_review_without_mutation'),
    array(42,true,true,false,'verified_expired_unpaid','manual_review_without_mutation'),
    array(42,true,false,true,'verified_expired_unpaid','manual_review_without_mutation'),
    array(42,false,true,true,'verified_expired_unpaid','retry_without_mutation'),
    array(0,true,true,true,'verified_paid','retry_without_mutation'),
);
foreach ($cases as $i => $c) {
    $got = BH4_Booking_Shared_Lock_Contract::sequence($c[0],$c[1],$c[2],$c[3],$c[4]);
    if ($got !== $c[5]) { fwrite(STDERR,"FAIL: race fixture ".($i+1)." got $got\n"); exit(1); }
}
echo "PASS: shared lock naming and seven payment/expiry race decisions (no DB)\n";
