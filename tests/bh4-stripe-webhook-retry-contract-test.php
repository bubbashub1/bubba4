<?php
define('ABSPATH', __DIR__);
require dirname(__DIR__) . '/plugin/bubba-hub-manager/includes/class-bh4-stripe-webhook-retry-contract.php';
$cases = array(
    array('missing','pending',false,'record_and_reconcile'),
    array('received','pending',false,'reconcile_payment'),
    array('processed','pending',false,'reconcile_payment'),
    array('received','paid',false,'retry_notifications'),
    array('processed','paid',false,'retry_notifications'),
    array('received','paid',true,'acknowledge_duplicate'),
    array('processed','paid',true,'acknowledge_duplicate'),
);
foreach ($cases as $case) {
    $result = BH4_Stripe_Webhook_Retry_Contract::decide($case[0], $case[1], $case[2]);
    if (!$result['ok'] || $result['action'] !== $case[3]) {
        fwrite(STDERR, 'FAIL: '.implode('/', array_slice($case,0,2))."\n"); exit(1);
    }
}
foreach (array(
    array('bogus','paid',true),
    array('received','refunded',true),
    array('processed','paid','yes')
) as $case) {
    $result = BH4_Stripe_Webhook_Retry_Contract::decide($case[0], $case[1], $case[2]);
    if ($result['ok'] || $result['action'] !== 'manual_review') {
        fwrite(STDERR, "FAIL: invalid state must fail closed\n"); exit(1);
    }
}
echo "PASS: webhook retry decision fixtures (pure contract; no Stripe calls)\n";
