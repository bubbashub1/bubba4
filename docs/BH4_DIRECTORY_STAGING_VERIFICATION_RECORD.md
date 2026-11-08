# BH4 directory staging verification record (template)

**Not yet executed.** Complete only after explicit staging access approval. Keep this template free of personal data.

| Gate | Evidence to record | Status |
| --- | --- | --- |
| Staging host and database prefix verified | Host and prefix only, no credentials | Pending |
| Read-only access and backup confirmed | Reviewer/date | Pending |
| Actual five-table schema matches planner | Column/type mismatch summary | Pending |
| Published-only results | Aggregate count and IDs only | Pending |
| 12 search scenarios | Per-scenario pass/fail, ID parity | Pending |
| Public card details | Title, age, organiser, price, image parity | Pending |
| Weekly timetable | Day/time/term status parity | Pending |
| Map marker data | Venue ID/coordinate parity | Pending |
| Performance and indexes | EXPLAIN summary, no private rows | Pending |
| No personal data exposed | Privacy reviewer/date | Pending |
| No conflicting plugin activation | Upgrade plan approved | Pending |
| Booking checkout remains unchanged | Owner and evidence | Pending |

**Decision:** BLOCKED until every required gate is independently verified. A passing GitHub Actions workflow is not staging approval.

Record deviations and proposed fixes in this file before requesting a staging-only integration review. Never add database credentials, family profiles, booking contacts or live user coordinates.
