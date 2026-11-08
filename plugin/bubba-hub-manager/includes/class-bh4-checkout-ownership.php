<?php
/**
 * BH4 checkout ownership decision: pure, fail-closed contract.
 * Does not inspect payment records, read bookings, register hooks or write data.
 */
defined( 'ABSPATH' ) || exit;

final class BH4_Checkout_Ownership {
    /**
     * Determine whether a proposed checkout adapter may take ownership.
     *
     * @param array<string,mixed> $evidence Caller-supplied verified test evidence.
     * @return array<string,mixed>
     */
    public static function evaluate( $evidence = array() ) {
        $required = array(
            'session_mapping_verified',
            'capacity_reservation_verified',
            'single_payment_verified',
            'booking_fee_verified',
            'webhook_idempotency_verified',
            'consent_handling_verified',
            'confirmation_verified',
            'refunds_verified',
            'leader_payout_verified',
            'legacy_booking_reconciliation_verified',
        );

        $missing = array();
        foreach ( $required as $key ) {
            if ( ! is_array( $evidence ) || ! array_key_exists( $key, $evidence ) || true !== $evidence[ $key ] ) {
                $missing[] = $key;
            }
        }

        return array(
            'checkout_owner' => 'legacy_bubba_hub',
            'legacy_checkout_preserved' => true,
            'replacement_eligible_for_review' => count( $missing ) === 0,
            'replacement_enabled' => false,
            'missing_verified_evidence' => $missing,
        );
    }
}
