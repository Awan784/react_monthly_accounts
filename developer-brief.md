# Income Tracker App Developer Brief

Build a private income tracking dashboard for creator income across platforms, brand deals, expenses, and external income.

## Required Stack

Preferred:
- Frontend: Next.js / React
- Hosting: Vercel
- Database: Supabase Postgres
- Auth: Supabase Auth
- File Storage: Supabase Storage

## Access

- Login required.
- Private single-user app.
- No public signup.
- Only the owner can view/edit data.
- Receipts and contracts must not be public.

## Core Calculation Rules

### Monthly Totals

Monthly total is the current base total for:

- Month
- Year
- Platform
- Account

If a new monthly total is entered for the same month/year/platform/account, replace the old one.

Do not stack duplicate monthly totals.

### Daily Entries

Daily entries add on top of the monthly total.

Example:
- April TikTok Shop DynamiteFinds monthly total = 10000
- Daily entry added later = 500
- Final platform income = 10500

This applies to:
- Income
- GMV
- Videos
- Items sold

### Brand Deals

Only status `Paid` counts toward income.

Statuses:
- Paid
- Pending
- Overdue
- Follow-Up

Pending/Overdue/Follow-Up should show under Payments Owed only.

### Formulas

```text
Platform Income = Monthly Totals + Daily Entries
Paid Brand Deals = Brand Deals where status = Paid
Payments Owed = Brand Deals where status != Paid
Total Income = Platform Income + Paid Brand Deals + External Income
Estimated Taxes = Total Income * Tax Rate
Net = Total Income - Estimated Taxes - Expenses
```

## Required Pages

### Dashboard
KPI cards:
- Total Income
- Net Total
- Estimated Taxes
- GMV
- Paid Brand Deals
- Payments Owed
- Total Videos
- Items Sold

Visuals:
- Monthly income trend graph
- Platform income mix
- Monthly income cards
- Payments owed panel
- Key signals
- Recent activity

Filters:
- Year
- Month
- Platform
- Account optional

### Income Entry
Entry modes:
- Monthly Total
- Daily Entry
- External Income

### Brand Deals
Fields:
- Date
- Month
- Year
- Platform
- Account
- Brand
- Contact Person
- Product
- Amount
- Status
- Due Date
- Usage Rights
- Contract upload
- Notes / Deliverables

### Expenses
Fields:
- Date
- Month
- Year
- Category
- Platform
- Amount
- Receipt upload
- Notes

Support receipt uploads:
- JPG
- PNG
- WEBP
- HEIC
- HEIF
- PDF

### Reports
- Monthly report
- Annual summary
- Platform report
- Account report
- Payments owed report
- Expense report
- Data check / reconciliation report

### Settings
- Tax rate
- Income goal
- Monthly goal
- Accounts
- Platforms
- Expense categories
- Usage rights

### Backup / Export
- Export JSON
- Import JSON backup
- Export CSV reports

## JSON Mapping

Current JSON sections:
- daily
- brands
- expenses
- external
- monthlyAdjustments
- closeouts
- auditLog
- settings

Map:
- monthlyAdjustments -> monthly_totals
- daily -> daily_entries
- brands -> brand_deals
- expenses -> expenses
- external -> external_income
- settings -> settings
- auditLog -> audit_log
