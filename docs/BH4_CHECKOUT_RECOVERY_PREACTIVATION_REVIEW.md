# Checkout recovery pre-activation review (8 November 2026)

**Decision: NOT READY TO ENABLE.** No production or staging activation, merge, or deployment is authorized by this review.

## Implemented in development branch
- Shared booking lock around checkout creation and persisted session.
- Disabled-by-default `BH4_ENABLE_CHECKOUT_RECOVERY` records a durable, insert-once recovery attempt before Stripe HTTP.
- Remote timeout, invalid Stripe response and failed booking-session persistence all attempt to transition to `needs_reconciliation`; existing attempts block automatic replay.
- Read-only administrator diagnostics require `BH4_ENABLE_RECOVERY_ADMIN_REVIEW === true`, `WP_DEBUG === true`, administrator capability, GET, and a valid nonce. The hidden admin page is reached via a signed notice link.
- Isolated source/pure contract tests run in CI. These are not WordPress/Stripe integration tests.

## Blocking gaps
1. **No verified Stripe reconciliation implementation.** Neither Stripe session lookup nor idempotency-key request verification is wired. An interrupted booking can remain blocked indefinitely.
2. **No administrator resolution action.** The review page only shows triage metadata, not verified payment status, refund decisions, or a guarded way to resolve an attempt. Never manually edit option values to unblock payment.
3. **No real MySQL concurrency test.** Atomic writer uses connection-owned advisory lock and byte-for-byte option-value compare-and-swap; verify on actual hosting MySQL, WordPress object cache, and parallel PHP requests.
4. **No end-to-end staging payment test.** Confirm create, Stripe redirect, webhook, confirmation, expiry, simultaneous requests, timeout, failed DB save and no duplicate charges.
5. **Incomplete handling of metadata-save failures.** A Stripe session may be persisted while recovery record remains `creating`; the user gets an error and requires human investigation.
6. **No confirmed staging package isolation.** The copied Bubba4 plugin retains BubbaWP 3.x identifiers and cannot safely run beside the original plugin until activation/bootstrap collisions are resolved.
7. **No durable reconciliation of changed request payloads.** The existing Stripe idempotency key is booking-ID based; never issue a new create request until the prior attempt is resolved and payload equivalence verified.
8. **Privacy and operational QA pending.** Review role boundaries, nonce expiry, retained records, support workflow and audit trail before release.

## Next implementation checkpoint
Build a read-only, authenticated Stripe test-mode verification service with strict session-to-booking matching and no payment creation or automatic retry. Test with mocked Stripe responses, then run a human-reviewed staging verification before any admin resolution mechanism.

**Do not enable either BH4 switch or merge until blockers are addressed.**
