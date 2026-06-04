-- ═══════════════════════════════════════════════════════
-- Outreach CRM — Database Schema
-- Run this in Supabase SQL Editor
-- ═══════════════════════════════════════════════════════

-- LEADS — core table
create table if not exists leads (
  id               uuid default gen_random_uuid() primary key,
  created_at       timestamptz default now(),
  imported_date    date default current_date,

  -- Business info
  business_name    text not null,
  owner_name       text,
  industry         text,
  city             text,
  state            text,
  email            text unique not null,
  phone            text,
  notes            text,
  logo_url         text,
  gmb_url          text,

  -- Pipeline stage
  status           text default 'pending'
                   check (status in (
                     'pending',
                     'site_generated',
                     'email_sent',
                     'replied',
                     'positive',
                     'meeting_booked',
                     'closed',
                     'not_interested'
                   )),

  -- Generated assets
  screenshot_path  text,
  mock_site_path   text,

  -- Tracking timestamps
  email_sent_at    timestamptz,
  email_sent_from  text,
  replied_at       timestamptz,
  reply_snippet    text,
  positive_at      timestamptz,
  meeting_at       timestamptz,
  closed_at        timestamptz,

  -- Deal value (filled when closed)
  deal_value       numeric default 0
);

-- EMAIL LOG — every email sent
create table if not exists email_log (
  id               uuid default gen_random_uuid() primary key,
  created_at       timestamptz default now(),
  lead_id          uuid references leads(id) on delete cascade,
  from_account     text not null,
  subject          text,
  gmail_message_id text,
  status           text default 'sent'
);

-- SEND COUNTS — daily warm-up tracker per account
create table if not exists send_counts (
  id           uuid default gen_random_uuid() primary key,
  account      text not null,
  send_date    date default current_date,
  count        integer default 0,
  unique(account, send_date)
);

-- REPLIES — incoming reply log
create table if not exists replies (
  id              uuid default gen_random_uuid() primary key,
  received_at     timestamptz default now(),
  lead_id         uuid references leads(id) on delete cascade,
  gmail_thread_id text,
  snippet         text,
  is_positive     boolean default false,
  reviewed        boolean default false
);

-- ── Indexes ──────────────────────────────────────────
create index if not exists leads_status_idx       on leads(status);
create index if not exists leads_imported_idx     on leads(imported_date);
create index if not exists email_log_lead_idx     on email_log(lead_id);
create index if not exists replies_lead_idx       on replies(lead_id);
create index if not exists send_counts_date_idx   on send_counts(send_date);

-- ── RLS (Row Level Security) — allow anon for now ────
alter table leads       enable row level security;
alter table email_log   enable row level security;
alter table send_counts enable row level security;
alter table replies     enable row level security;

create policy "Allow all" on leads       for all using (true) with check (true);
create policy "Allow all" on email_log   for all using (true) with check (true);
create policy "Allow all" on send_counts for all using (true) with check (true);
create policy "Allow all" on replies     for all using (true) with check (true);
