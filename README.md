# Income Tracker Developer Handoff

This package contains the current HTML prototype, sample JSON data, database schema, and starter code for rebuilding the tracker as a private web app.

## Files Included

1. `current-html-prototype.html`
   - Current working HTML prototype.
   - Use this as the UI/functionality reference, not as the final production app.

2. `sample-json-backup.json`
   - Real backup structure/sample data.
   - Use this to understand the current data model and to test importer logic.

3. `developer-brief.md`
   - Product requirements, business rules, app structure, and build priorities.

4. `schema.sql`
   - Suggested Supabase/Postgres schema.

5. `calculationEngine.ts`
   - Centralized calculation logic.
   - All dashboard totals, reports, and charts should use this logic.

6. `jsonImporter.ts`
   - Starter importer that maps the JSON backup structure into database-ready objects.

## Main Calculation Rules

- Monthly totals are the current base total for a month/year/platform/account.
- A new monthly total for the same month/year/platform/account replaces the previous one.
- Daily entries add on top of monthly totals.
- Pending, Overdue, and Follow-Up brand deals do NOT count as income.
- Only Paid brand deals count toward income.
- Payments Owed = brand deals where status is not Paid.
- Total Income = Platform Income + Paid Brand Deals + External Income.
- Net = Total Income - Estimated Taxes - Expenses.

## Recommended Stack

- Next.js / React
- Supabase Auth
- Supabase Postgres
- Supabase Storage
- Vercel Hosting

## Privacy Requirement

This should be a private single-user app. No public signup.
