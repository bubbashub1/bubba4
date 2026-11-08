# BH4 Family Hub and Leader Portal staging smoke-test matrix

**Prepared only; no staging page has been opened or verified.** Run after backups, schema evidence and approved same-plugin replacement. Do not activate a second copy.

| Route | What must work | Critical checks |
| --- | --- | --- |
| /my-hub/ | Family dashboard and links | Signed-out redirect; correct family ownership |
| /my-hub/preferences/ | Interests, location, preferred days | Save/read parity; no cross-user reads |
| /my-hub/profile/ | Family account/profile editing | No unexpected form submissions or data loss |
| /my-hub/children/ | Child and bump add/edit | Gender/year fields; ownership; privacy |
| /my-hub/planner/ | Calendar/planned activities | Add/remove; correct activity/session |
| /my-hub/favourites/ | Saved and visited items | Save/unsave persistence and permissions |
| /leader/ | Dashboard navigation | Leader role only; admin not required |
| /leader/about/ | Leader onboarding/help | Correct membership tier messaging |
| /leader/new-activity/ | Bespoke activity editor | Draft safety; venues/sessions; permissions |
| /leader/bookings/ | Existing booking dashboard | [bh_leader_booking_dashboard], no customer leakage |
| /leader/messages/ | Enquiries/messages | Verified organiser email, consent, ownership |
| /leader/venues/ | Venue lookup/link/add | No duplicates; ownership and existing venues |
| /leader/business/ | Organisation/storefront | Edit only owned organiser |
| /leader/payment/ | Payout settings | No payment credential leakage; permission checks |

## Cross-cutting tests
- Guest, Family Free, Family Pro, Leader Free, Leader Pro and admin roles. Check unauthorised direct URLs and POST attempts, not only navigation visibility.
- Desktop and mobile layout, including responsive menu and header; no fatal PHP errors or missing CSS/JS.
- Preserve existing shortcode content and WordPress page IDs; do not replace pages to make tests pass.
- Checkout must still use the existing Form 8 staging route, multi-child selection, single payment, terms and confirmation. Do not switch payment providers during portal smoke tests.
- Confirm Family/Leader table reads against read-only schema inventory first; no production DB use.
- Record each result as **not tested / pass / fail / blocked**, with test account role, URL, date, error details and screenshot if needed. Never store child names, contact information or payment details in test logs.
- A failed permissions or payment check blocks activation and triggers rollback per the controlled upgrade plan.

## Known source findings
The manager's four feature flags are still false; the source-level shortcode inventory passed CI. This matrix is not evidence of WordPress runtime parity.
