# BH4 Events Manager compatibility probe

`plugin/bubba-hub-manager/includes/class-bh4-events-manager-probe.php` provides a **passive, administrator-only** inspection method `BH4_Events_Manager_Probe::inspect()`.

It reports whether the expected Events Manager plugin basename is installed/active, its installed version, whether known Events Manager PHP classes are already loaded, and whether the legacy `bh_parent_booking` shortcode is registered. It never loads Events Manager classes on purpose (`class_exists(..., false)`), never queries bookings or personal data, and never changes plugin state or database records.

**Important:** The probe is not wired into the original BH3 bootstrap. It will not run on any WordPress site until a separately reviewed BH4 diagnostics screen explicitly loads and invokes it.

The `ready_for_booking_migration` field is deliberately always `false`. Detecting an active plugin is not evidence that Events Manager meets Bubba Hub's requirements.

## Required parity before replacing checkout

1. Choose class and session; select multiple children and optionally guests.
2. Collect guardian details and separate consents with appropriate data access.
3. One payment, transparent single customer-facing booking fee, leader receives listed class price.
4. Correct Stripe webhook, refund, cancellation and failure handling.
5. Accurate capacities, timetable and recurring session mapping.
6. Confirmation email and leader booking dashboard visibility.
7. Guest and member booking paths, duplicate submission protection, privacy and retention.
8. Demonstrate end-to-end behaviour on an isolated test installation.

Keep the existing booking shortcode and Fluent Form 8 in place until the above is verified. No deployment is authorised by this probe.
