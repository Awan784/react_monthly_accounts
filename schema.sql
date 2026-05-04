-- Income Tracker Supabase/Postgres Schema

create table if not exists users (
  id uuid primary key,
  email text unique not null,
  created_at timestamptz default now()
);

create table if not exists accounts (
  id uuid primary key default gen_random_uuid(),
  user_id uuid references users(id) on delete cascade,
  name text not null,
  created_at timestamptz default now()
);

create table if not exists platforms (
  id uuid primary key default gen_random_uuid(),
  user_id uuid references users(id) on delete cascade,
  name text not null,
  created_at timestamptz default now()
);

create table if not exists monthly_totals (
  id uuid primary key default gen_random_uuid(),
  user_id uuid references users(id) on delete cascade,
  month text not null,
  year integer not null,
  platform_id uuid references platforms(id),
  account_id uuid references accounts(id),
  income numeric default 0,
  gmv numeric default 0,
  videos integer default 0,
  items_sold integer default 0,
  notes text,
  created_at timestamptz default now(),
  updated_at timestamptz default now(),
  unique(user_id, month, year, platform_id, account_id)
);

create table if not exists daily_entries (
  id uuid primary key default gen_random_uuid(),
  user_id uuid references users(id) on delete cascade,
  date date,
  month text not null,
  year integer not null,
  platform_id uuid references platforms(id),
  account_id uuid references accounts(id),
  income numeric default 0,
  gmv numeric default 0,
  videos integer default 0,
  items_sold integer default 0,
  notes text,
  created_at timestamptz default now(),
  updated_at timestamptz default now()
);

create table if not exists brand_deals (
  id uuid primary key default gen_random_uuid(),
  user_id uuid references users(id) on delete cascade,
  date date,
  month text not null,
  year integer not null,
  platform_id uuid references platforms(id),
  account_id uuid references accounts(id),
  brand text,
  contact text,
  product text,
  amount numeric default 0,
  status text not null default 'Pending',
  due_date date,
  usage_rights text,
  contract_file_path text,
  notes text,
  created_at timestamptz default now(),
  updated_at timestamptz default now(),
  check (status in ('Paid','Pending','Overdue','Follow-Up'))
);

create table if not exists expenses (
  id uuid primary key default gen_random_uuid(),
  user_id uuid references users(id) on delete cascade,
  date date,
  month text not null,
  year integer not null,
  category text,
  platform_id uuid references platforms(id),
  amount numeric default 0,
  receipt_file_path text,
  notes text,
  created_at timestamptz default now(),
  updated_at timestamptz default now()
);

create table if not exists external_income (
  id uuid primary key default gen_random_uuid(),
  user_id uuid references users(id) on delete cascade,
  date date,
  month text not null,
  year integer not null,
  source text,
  amount numeric default 0,
  notes text,
  created_at timestamptz default now(),
  updated_at timestamptz default now()
);

create table if not exists settings (
  id uuid primary key default gen_random_uuid(),
  user_id uuid references users(id) on delete cascade unique,
  tax_rate numeric default 22,
  income_goal numeric default 250000,
  monthly_goal numeric default 20000,
  created_at timestamptz default now(),
  updated_at timestamptz default now()
);

create table if not exists audit_log (
  id uuid primary key default gen_random_uuid(),
  user_id uuid references users(id) on delete cascade,
  action text,
  detail text,
  created_at timestamptz default now()
);

-- Recommended private storage buckets:
-- receipts
-- contracts

-- Supabase RLS should be enabled on all tables.
-- Policy concept:
-- user_id = auth.uid()
