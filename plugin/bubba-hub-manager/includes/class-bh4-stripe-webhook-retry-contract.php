<?php
/**
 * Pure webhook retry decision contract. No database access, email, Stripe calls or hooks.
 * A recorded event is not necessarily reconciled: retry until payment and notifications
 * have been confirmed, including events already marked processed.
 */
if (!defined('ABSPATH')) { exit; }
final class BH4_Stripe_Webhook_Retry_Contract {
    public static function decide($event_status, $payment_status, $notifications_complete) {
        if (!in_array($event_status, array('missing', 'received', 'processed'), true)
            || !in_array($payment_status, array('pending', 'paid'), true)
            || !is_bool($notifications_complete)) {
            return array('ok' => false, 'action' => 'manual_review');
        }
        if ($event_status === 'missing') {
            return array('ok' => true, 'action' => 'record_and_reconcile');
        }
        if ($payment_status === 'pending') {
            return array('ok' => true, 'action' => 'reconcile_payment');
        }
        if (!$notifications_complete) {
            return array('ok' => true, 'action' => 'retry_notifications');
        }
        return array('ok' => true, 'action' => 'acknowledge_duplicate');
    }
}
