# Bubba Hub 4.0 — Development

This repository is the isolated development destination for the Bubba Hub 4.0 migration.

## Source and safety
- Source: https://github.com/bubbashub1/bubbawp (main, baseline commit bb0df614e3f0eb7dfb8d8695ec932b0c7ff9f817)
- Do not modify the source repository as part of migration.
- Preserve existing custom activity, venue, organiser, session and family SQL tables, field mappings, shortcodes and URLs.
- Do not activate this alongside BubbaWP until plugin class/function/option collisions have been assessed.
- No automatic deployment or database migration during initial code transfer.

## Architecture candidates (require staging validation)
- Native BubbaWP: bespoke listings, Family Hub and integrated Leader Portal.
- Events Manager: bookings, recurring sessions and tickets.
- NiroHelp: knowledge base, support tickets and guided support.
- Ninja Forms: forms, consent and enquiries.
- Existing membership/authentication, SMTP and scheduled tasks retained where compatible.
- Leader statistics: report on reliable existing data, avoid unnecessary analytics plugins.

## Migration sequence
1. Import the complete BubbaWP source into this repository, preserving code and history where possible.
2. Audit dependency and activation conflicts before renaming any identifiers.
3. Add adapter modules incrementally, without removing working features.
4. Validate on isolated staging and compare booking, payment, permissions and data behavior.
5. Deploy only after explicit approval and rollback planning.

**Status:** Repository initialized; code import not yet completed.
