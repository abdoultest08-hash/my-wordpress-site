import { useEffect, useState } from "react"
import { supabase } from "../lib/supabase"
import { fmt, fmtCurrency, fmtDate, effectiveStatus, STATUS_CONFIG, getFollowUpSettings } from "../lib/utils"
import { useNavigate } from "react-router-dom"
import { Mail, MessageSquare, ThumbsUp, CalendarCheck, DollarSign, Users, Zap, Clock, AlertTriangle } from "lucide-react"

const WARMUP_TIERS = [
  { days: 0,  limit: 10, label: "Tier 1 — Warm-up"  },
  { days: 7,  limit: 15, label: "Tier 2 — Ramping"   },
  { days: 14, limit: 20, label: "Tier 3 — Building"  },
  { days: 28, limit: 30, label: "Tier 4 — Active"    },
  { days: 42, limit: 40, label: "Tier 5 — Full Send" },
]

const ACCOUNTS = [
  { email: "sitesbyabs@gmail.com",     start: "2026-06-03" },
  { email: "pagesforlocals@gmail.com", start: "2026-06-03" },
]

function getAccountTier(startDateStr) {
  const start = new Date(startDateStr)
  const days  = Math.floor((Date.now() - start) / (1000 * 60 * 60 * 24))
  let tier = WARMUP_TIERS[0]
  for (const t of WARMUP_TIERS) { if (days >= t.days) tier = t }
  const tierIdx = WARMUP_TIERS.indexOf(tier)
  const next = WARMUP_TIERS[tierIdx + 1]
  return { ...tier, days, daysToNext: next ? next.days - days : null, nextLimit: next?.limit }
}

function StatCard({ icon: Icon, label, value, sub, color }) {
  return (
    <div className="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
      <div className="flex items-center justify-between mb-3">
        <span className="text-sm font-medium text-gray-500">{label}</span>
        <div className={`w-9 h-9 rounded-lg flex items-center justify-center ${color}`}>
          <Icon size={16} />
        </div>
      </div>
      <div className="text-2xl font-bold font-heading text-gray-900">{value}</div>
      {sub && <div className="text-xs text-gray-400 mt-1">{sub}</div>}
    </div>
  )
}

export default function Dashboard() {
  const [stats, setStats]             = useState({})
  const [recent, setRecent]           = useState([])
  const [accountSends, setAccountSends] = useState({})
  const [followUps, setFollowUps]     = useState([])
  const navigate = useNavigate()

  useEffect(() => {
    async function load() {
      const today = new Date().toISOString().split("T")[0]
      const { followup1Days } = getFollowUpSettings()
      const cutoff = new Date(Date.now() - followup1Days * 24 * 60 * 60 * 1000).toISOString()

      const [{ data: leads }, { data: replies }, { data: counts }, { data: rec }, { data: fu }] = await Promise.all([
        supabase.from("leads").select("status, email_sent_from, deal_value, created_at, email_sent_at"),
        supabase.from("replies").select("is_positive, received_at"),
        supabase.from("send_counts").select("account, count, send_date").eq("send_date", today),
        supabase.from("leads").select("*").order("created_at", { ascending: false }).limit(6),
        supabase.from("leads").select("*").eq("status", "email_sent").lte("email_sent_at", cutoff).order("email_sent_at", { ascending: true }).limit(5),
      ])

      const sentLeads = leads?.filter(l => !["pending","site_generated"].includes(l.status)) ?? []
      const s = {
        total:    leads?.length ?? 0,
        drafts:   leads?.filter(l => l.status === "site_generated" && l.email_sent_from).length ?? 0,
        sent:     sentLeads.length,
        replied:  replies?.length ?? 0,
        positive: replies?.filter(r => r.is_positive).length ?? 0,
        meetings: leads?.filter(l => l.status === "meeting_booked").length ?? 0,
        closed:   leads?.filter(l => l.status === "closed").length ?? 0,
        revenue:  leads?.filter(l => l.status === "closed").reduce((a, l) => a + (l.deal_value || 0), 0) ?? 0,
      }
      s.replyRate    = s.sent    ? ((s.replied  / s.sent)    * 100).toFixed(1) : "0.0"
      s.positiveRate = s.replied ? ((s.positive / s.replied) * 100).toFixed(1) : "0.0"
      setStats(s)

      const map = {}
      counts?.forEach(c => { map[c.account] = (map[c.account] || 0) + c.count })
      setAccountSends(map)
      setRecent(rec ?? [])
      setFollowUps(fu ?? [])
    }
    load()
  }, [])

  const STATUS_COLORS = {
    pending: "bg-gray-100 text-gray-500", site_generated: "bg-blue-100 text-blue-600",
    draft_ready: "bg-orange-100 text-orange-700",
    email_sent: "bg-yellow-100 text-yellow-700", replied: "bg-purple-100 text-purple-700",
    positive: "bg-green-100 text-green-700", meeting_booked: "bg-indigo-100 text-indigo-700",
    closed: "bg-emerald-100 text-emerald-700", not_interested: "bg-red-100 text-red-500",
  }

  const todaySent = Object.values(accountSends).reduce((a, v) => a + v, 0)
  const daysSince = (d) => d ? Math.floor((Date.now() - new Date(d)) / 86400000) : null

  return (
    <div>
      <div className="mb-6">
        <h1 className="text-2xl font-bold font-heading text-gray-900">Dashboard</h1>
        <p className="text-gray-500 text-sm mt-1">Today's sends: <strong className="text-[#0D1B2A]">{todaySent}</strong> emails · <strong className="text-orange-600">{stats.drafts ?? 0}</strong> drafts ready to send</p>
      </div>

      {/* Follow-up alert */}
      {followUps.length > 0 && (
        <div className="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-5 flex items-center gap-3 cursor-pointer hover:bg-amber-100 transition-colors"
          onClick={() => navigate("/followup")}>
          <AlertTriangle size={18} className="text-amber-600 flex-shrink-0" />
          <p className="text-sm text-amber-800 flex-1">
            <strong>{followUps.length} lead{followUps.length !== 1 ? "s" : ""}</strong> overdue for a follow-up — click to review
          </p>
          <Clock size={15} className="text-amber-500" />
        </div>
      )}

      {/* KPI Grid */}
      <div className="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4 mb-6">
        <StatCard icon={Mail}          label="Emails Sent"  value={fmt(stats.sent)}     sub={`${stats.replyRate}% reply rate`}    color="bg-yellow-50 text-yellow-600" />
        <StatCard icon={MessageSquare} label="Replies"      value={fmt(stats.replied)}  sub={`${stats.positiveRate}% positive`}   color="bg-purple-50 text-purple-600" />
        <StatCard icon={ThumbsUp}      label="Positive"     value={fmt(stats.positive)} sub="Hot leads"                           color="bg-green-50 text-green-600"   />
        <StatCard icon={CalendarCheck} label="Meetings"     value={fmt(stats.meetings)} sub="Booked"                              color="bg-indigo-50 text-indigo-600" />
        <StatCard icon={Users}         label="Closed"       value={fmt(stats.closed)}   sub="Sales won"                           color="bg-emerald-50 text-emerald-600"/>
        <StatCard icon={DollarSign}    label="Revenue"      value={fmtCurrency(stats.revenue)} sub="Total closed"                color="bg-[#F5A623]/10 text-[#D4901F]"/>
      </div>

      {/* Account warm-up */}
      <div className="bg-white rounded-xl shadow-sm border border-gray-100 mb-6">
        <div className="px-6 py-4 border-b border-gray-100 flex items-center gap-2">
          <Zap size={16} className="text-[#F5A623]" />
          <h2 className="font-semibold font-heading text-gray-900">Account Warm-up Status</h2>
        </div>
        <div className="grid grid-cols-1 md:grid-cols-2 divide-y md:divide-y-0 md:divide-x divide-gray-100">
          {ACCOUNTS.map(acc => {
            const tier = getAccountTier(acc.start)
            const sent = accountSends[acc.email] || 0
            const pct  = Math.min(100, Math.round((sent / tier.limit) * 100))
            return (
              <div key={acc.email} className="px-6 py-5">
                <div className="flex items-center justify-between mb-2">
                  <div>
                    <div className="font-medium text-sm text-gray-900">{acc.email.split("@")[0]}</div>
                    <div className="text-xs text-gray-400">{acc.email}</div>
                  </div>
                  <div className="text-right">
                    <div className="text-lg font-bold text-[#0D1B2A]">{sent}<span className="text-sm font-normal text-gray-400">/{tier.limit}</span></div>
                    <div className="text-xs text-gray-400">today</div>
                  </div>
                </div>
                <div className="h-2 bg-gray-100 rounded-full overflow-hidden mb-2">
                  <div className="h-full bg-[#F5A623] rounded-full transition-all" style={{ width: `${pct}%` }} />
                </div>
                <div className="flex items-center justify-between text-xs">
                  <span className="font-medium text-[#0D1B2A]">{tier.label}</span>
                  <span className="text-gray-400">
                    {tier.daysToNext !== null ? `Next tier (${tier.nextLimit}/day) in ${tier.daysToNext}d` : "Max tier reached 🎉"}
                  </span>
                </div>
                <div className="text-xs text-gray-300 mt-0.5">{tier.days} days old</div>
              </div>
            )
          })}
        </div>
      </div>

      {/* Follow-up due */}
      {followUps.length > 0 && (
        <div className="bg-white rounded-xl shadow-sm border border-gray-100 mb-6">
          <div className="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <div className="flex items-center gap-2">
              <Clock size={16} className="text-amber-500" />
              <h2 className="font-semibold font-heading text-gray-900">Follow-ups Due</h2>
            </div>
            <button onClick={() => navigate("/followup")} className="text-sm text-[#F5A623] font-medium hover:underline">View all →</button>
          </div>
          <div className="divide-y divide-gray-50">
            {followUps.map(lead => (
              <div key={lead.id} onClick={() => navigate(`/leads/${lead.id}`)}
                className="flex items-center gap-4 px-6 py-3 hover:bg-gray-50 cursor-pointer transition-colors">
                <div className="w-8 h-8 rounded-full bg-amber-100 flex items-center justify-center text-amber-700 font-bold text-sm flex-shrink-0">
                  {lead.business_name?.charAt(0)}
                </div>
                <div className="flex-1 min-w-0">
                  <div className="font-medium text-sm text-gray-900 truncate">{lead.business_name}</div>
                  <div className="text-xs text-gray-400">{lead.industry} · {lead.city}</div>
                </div>
                <span className="text-xs text-amber-600 font-semibold bg-amber-50 px-2 py-1 rounded-full">
                  {daysSince(lead.email_sent_at)}d ago
                </span>
              </div>
            ))}
          </div>
        </div>
      )}

      {/* Recent leads */}
      <div className="bg-white rounded-xl shadow-sm border border-gray-100">
        <div className="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
          <h2 className="font-semibold font-heading text-gray-900">Recent Leads</h2>
          <a href="/leads" className="text-sm text-[#F5A623] font-medium hover:underline">View all →</a>
        </div>
        <div className="divide-y divide-gray-50">
          {recent.length === 0 && (
            <div className="px-6 py-12 text-center text-gray-400 text-sm">No leads yet — run the pipeline to import your first batch</div>
          )}
          {recent.map(lead => {
            const eff = effectiveStatus(lead)
            const cfg = STATUS_CONFIG[eff] ?? STATUS_CONFIG.pending
            return (
              <a key={lead.id} href={`/leads/${lead.id}`}
                className="flex items-center gap-4 px-6 py-3.5 hover:bg-gray-50 transition-colors">
                <div className="w-9 h-9 rounded-full bg-[#0D1B2A] flex items-center justify-center text-white font-bold text-sm flex-shrink-0">
                  {lead.business_name?.charAt(0)}
                </div>
                <div className="flex-1 min-w-0">
                  <div className="font-medium text-sm text-gray-900 truncate">{lead.business_name}</div>
                  <div className="text-xs text-gray-400">{lead.industry} · {lead.city}</div>
                </div>
                <span className={`text-xs px-2.5 py-1 rounded-full font-medium ${cfg.color}`}>{cfg.label}</span>
                <div className="text-xs text-gray-400 w-24 text-right hidden md:block">{fmtDate(lead.created_at)}</div>
              </a>
            )
          })}
        </div>
      </div>
    </div>
  )
}
