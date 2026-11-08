# Bubba4 native-app readiness contract

**Decision:** Treat Android/iOS compatibility as an architectural requirement for all new Bubba4 custom modules, not a commitment to release native apps during the WordPress migration.

## Design rules

1. Keep domain logic in reusable PHP services rather than WordPress page templates, shortcode renderers, or browser-only JavaScript.
2. Provide future versioned REST interfaces (proposed namespace `bubba/v1`) with explicit permission callbacks, validation, rate-limiting strategy, and stable response schemas. **Do not register new public routes until reviewed.**
3. Use WordPress authentication and explicit capability/ownership checks; do not expose family/child records through public endpoints or assume browser cookies work for native clients.
4. Treat children, bumps, consent, contact information and booking records as sensitive. Apply data minimisation, parent/leader scoping, consent and retention rules.
5. Keep all payment totals, booking fees, capacity reservations, webhook verification and idempotency on the server. Native apps must not calculate authoritative prices.
6. Preserve existing database tables and IDs; use service adapters to separate Bubba Hub records from Events Manager event/occurrence IDs.
7. Keep timezones and occurrence identifiers explicit; support recurring sessions, exceptions, cancellations and UK daylight-saving boundaries.
8. Provide paginated, filterable read APIs for directory/search and controlled write APIs for favourites, planner, bookings and profile updates.
9. Define API versioning, backward compatibility, pagination, error contracts and audit logs before publishing app endpoints.
10. Consider mobile push notifications as an optional future channel; use server-side notification preferences and never put personal data in push payloads.
11. A PWA may be the low-cost first step, but native app-store distribution, payment rules and app security require a separate assessment.

## Initial modules to design for API reuse

- Activities, venues, organisers, session occurrences and directory search
- Family Hub: profile, children/bumps, preferences, favourites, planner, visited
- Booking checkout and consent: one server-authoritative booking lifecycle
- Leader dashboard: schedule, enrolments, capacity and messages
- Membership entitlements: Family/Leader Free and Pro, enforced server-side

## Migration constraints

- No automatic database migration, checkout replacement or native app build.
- No unreviewed API routes or new external paid services.
- Maintain compatibility with existing website interfaces while refactoring incrementally.
- Document API acceptance tests alongside each new custom module.
