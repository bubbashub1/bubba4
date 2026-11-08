# BH4 combined booking fee and abandoned checkout audit

Source-only review, 2026-11-08. No Stripe calls or WordPress writes.

## Combined fee
- Quote uses `unit_price_pence * places` as the **whole booking** class price.
- `parent_paid_fee_preview()` applies platform percentage to the combined class price, adds one fixed processing allowance and grosses up the whole payment for the processing percentage.
- Stripe Checkout uses **one** line item, `quantity=1`, with `unit_amount=parent_total`. The destination transfer is the combined advertised class price and application fee is the customer-facing booking fee.
- Stripe request has a booking-ID-based idempotency key and checks returned currency, test mode and amount.
- Estimates are not a guarantee of actual Stripe fees; confirm Stripe Connect fee payer and settlement rules before enabling payments.

## Abandoned checkout and capacity
- New reservation has a 35-minute expiry. Checkout requires at least 30 minutes remaining; Stripe Checkout expires at the earlier of 31 minutes from creation or reservation expiry.
- Capacity query counts **reserved** rows as occupied even after their timestamp passes, until a verified status transition marks them `expired`.
- `transition_booking_status()` supports an explicit, locked `reserved -> expired` transition only when payment remains pending and expiry has elapsed.
- Source inspection does **not** establish a scheduler invoking expiry transitions. Therefore **do not claim automatic capacity release** merely because the hold timestamp has elapsed.
- Stripe payment after expiry is rejected for automatic confirmation and requires manual review; ensure cancellation/expiry reconciliation cannot release a genuinely paid place.

## Staging QA still required
1. Quote one versus two places and confirm a single fixed processing allowance per booking.
2. Verify leader payout and customer total against actual Stripe test Connect account setup.
3. Abandon Stripe checkout; verify how and when pending holds become expired and capacity reopens.
4. Test delayed paid webhook versus expiry transition, and duplicate checkout creation.
5. Document who triggers expiry, how it is scheduled and how errors are retried before enabling checkout.

**Decision:** preserve existing runtime. Abandoned checkout release is an **open launch blocker** until a safe expiry worker and payment reconciliation are verified.
