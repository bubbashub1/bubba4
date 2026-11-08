<?php
define('ABSPATH', __DIR__);
require dirname(__DIR__) . '/plugin/bubba-hub-manager/includes/class-bh4-reservation-expiry-contract.php';
$cases = array(
    array('reserved','pending',100,99,'expired_unpaid','no_change'),
    array('reserved','pending',100,100,'no_checkout_session','eligible_after_locked_recheck'),
    array('reserved','pending',100,110,'expired_unpaid','eligible_after_locked_recheck'),
    array('reserved','pending',100,110,'open','manual_review'),
    array('reserved','pending',100,110,'processing','manual_review'),
    array('reserved','pending',100,110,'paid','manual_review'),
    array('reserved','pending',100,110,'complete_paid','manual_review'),
    array('reserved','paid',100,110,'expired_unpaid','no_change'),
    array('confirmed','paid',100,110,'expired_unpaid','no_change'),
    array('reserved','failed',100,110,'expired_unpaid','manual_review'),
    array('reserved','pending',0,110,'expired_unpaid','manual_review'),
    array('reserved','pending',100,110,'unknown','manual_review'),
);
foreach ($cases as $i => $case) {
    $result = BH4_Reservation_Expiry_Contract::decide($case[0],$case[1],$case[2],$case[3],$case[4]);
    if ($result['action'] !== $case[5]) {
        fwrite(STDERR, 'FAIL fixture ' . ($i+1) . ': ' . $result['action'] . "\n"); exit(1);
    }
}
echo "PASS: 12 fail-closed reservation expiry decisions (no Stripe or DB)\n";
