import { useEffect, useState } from "react"
import { supabase } from "../lib/supabase"
import { fmt, fmtCurrency, fmtDate } from "../lib/utils"
import { Mail, MessageSquare, ThumbsUp, CalendarCheck, DollarSign, Users } from "lucide-react"

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
  const [stats, setStats] = useState({})
  const [recent, setRecent] = useState([])
  const [todaySent, setTodaySent] = useState(0)

  useEffect(() => {
    async function load() {
      const { data: leads } = await supabase.from("leads").select("status, deal_value, created_at, email_sent_at")
      const { data: replies } = await supabase.from("replies").select("is_positive, received_at")
      const { data: counts } = await supabase.from("send_counts").select("count, send_date").eq("send_date", new Date().toISOString().split("T")[0])

      const s = {
        total:     leads?.length ?? 0,
        sent:      leads?.filter(l => l.status !== "pending" && l.status !== "site_generated").length ?? 0,
        replied:   replies?.length ?? 0,
        positive:  replies?.filter(r => r.is_positive).length ?? 0,
        meetings:  leads?.filter(l => l.status === "meeting_booked").length ?? 0,
        closed:    leads?.filter(l => l.status === "closed").length ?? 0,
        revenue:   leads?.filter(l => l.status === "closed").reduce((a, l) => a + (l.deal_value || 0), 0) ?? 0,
      }
      s.replyRate    = s.sent    ? ((s.replied  / s.sent)    * 100).toFixed(1) : "0.0"
      s.positiveRate = s.replied ? ((s.positive / s.replied) * 100).toFixed(1) : "0.0"
      s.closeRate    = s.meetings? ((s.closed   / s.meetings)* 100).toFixed(1) : "0.0"

      setStats(s)
      setTodaySent(counts?.reduce((a, c) => a + c.count, 0) ?? 0)

      const { data: rec } = await supabase.from("leads").select("*").order("created_at", { ascending: false }).limit(6)
      setRecent(rec ?? [])
    }
    load()
  }, [])

  const STATUS_COLORS = {
    pending: "bg-gray-100 text-gray-500", site_generated: "bg-blue-100 text-blue-600",
    email_sent: "bg-yellow-100 text-yellow-700", replied: "bg-purple-100 text-purple-700",
    positive: "bg-green-100 text-green-700", meeting_booked: "bg-indigo-100 text-indigo-700",
    closed: "bg-emerald-100 text-emerald-700", not_interested: "bg-red-100 text-red-500",
  }

  return (
    <div>
      <div className="mb-6">
        <h1 className="text-2xl font-bold font-heading text-gray-900">Dashboard</h1>
        <p className="text-gray-500 text-sm mt-1">Today's sends: <strong className="text-[#0D1B2A]">{todaySent}</strong> emails across all accounts</p>
      </div>

      {/* KPI Grid */}
      <div className="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4 mb-8">
        <StatCard icon={Mail}         label="Emails Sent"   value={fmt(stats.sent)}     sub={`${stats.replyRate}% reply rate`}    color="bg-yellow-50 text-yellow-600" />
        <StatCard icon={MessageSquare}label="Replies"       value={fmt(stats.replied)}  sub={`${stats.positiveRate}% positive`}    color="bg-purple-50 text-purple-600" />
        <StatCard icon={ThumbsUp}     label="Positive"      value={fmt(stats.positive)} sub="Hot leads"                            color="bg-green-50 text-green-600"  />
        <StatCard icon={CalendarCheck}label="Meetings"      value={fmt(stats.meetings)} sub={`${stats.closeRate}% close rate`}     color="bg-indigo-50 text-indigo-600"/>
        <StatCard icon={Users}        label="Closed"        value={fmt(stats.closed)}   sub="Sales won"                            color="bg-emerald-50 text-emerald-600"/>
        <StatCard icon={DollarSign}   label="Revenue"       value={fmtCurrency(stats.revenue)} sub="Total closed value"            color="bg-[#F5A623]/10 text-[#D4901F]"/>
      </div>

      {/* Recent Leads */}
      <div className="bg-white rounded-xl shadow-sm border border-gray-100">
        <div className="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
          <h2 className="font-semibold font-heading text-gray-900">Recent Leads</h2>
          <a href="/leads" className="text-sm text-[#F5A623] font-medium hover:underline">View all →</a>
        </div>
        <div className="divide-y divide-gray-50">
          {recent.length === 0 && (
            <div className="px-6 py-12 text-center text-gray-400 text-sm">No leads yet — import your first batch</div>
          )}
          {recent.map(lead => (
            <a key={lead.id} href={`/leads/${lead.id}`}
               className="flex items-center gap-4 px-6 py-3.5 hover:bg-gray-50 transition-colors">
              <div className="w-9 h-9 rounded-full bg-[#0D1B2A] flex items-center justify-center text-white font-bold text-sm flex-shrink-0">
                {lead.business_name?.charAt(0)}
              </div>
              <div className="flex-1 min-w-0">
                <div className="font-medium text-sm text-gray-900 truncate">{lead.business_name}</div>
                <div className="text-xs text-gray-400">{lead.industry} · {lead.city}</div>
              </div>
              <span className={`text-xs px-2.5 py-1 rounded-full font-medium ${STATUS_COLORS[lead.status] ?? "bg-gray-100 text-gray-500"}`}>
                {lead.status?.replace(/_/g," ")}
              </span>
              <div className="text-xs text-gray-400 w-24 text-right hidden md:block">{fmtDate(lead.created_at)}</div>
            </a>
          ))}
        </div>
      </div>
    </div>
  )
}
