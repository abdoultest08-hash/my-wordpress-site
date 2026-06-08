-- ═══════════════════════════════════════════════════════
-- Schema v2 Migration — Run in Supabase SQL Editor
-- Adds copy_version + pitch_type for A/B tracking
-- ═══════════════════════════════════════════════════════

-- Track which copy version and pitch type was used on each lead
alter table leads
  add column if not exists copy_version  text default 'v1',
  add column if not exists pitch_type    text default 'new';

-- Track on email log too (for per-send granularity)
alter table email_log
  add column if not exists copy_version  text default 'v1',
  add column if not exists pitch_type    text default 'new';

-- Indexes for filtering by version in CRM/KPI queries
create index if not exists leads_copy_version_idx on leads(copy_version);
create index if not exists leads_pitch_type_idx   on leads(pitch_type);

-- Reply quality tags (Hot / Warm / Cold / Wrong person)
alter table replies
  add column if not exists quality_tag text;
