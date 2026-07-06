import { useEffect, useState } from "react"
import { supabase } from "../lib/supabase"
import { fmtDate, getFollowUpSettings } from "../lib/utils"
import { useNavigate } from "react-router-dom"
import { Clock, Mail, AlertTriangle, Settings } from "lucide-react"

export default function FollowUp() {
  const [leads, setLeads]   = useState([])
  const [loading, setLoading] = useState(true)
  const [settings, setSettings] = useState(getFollowUpSettings())
  const [tab, setTab] = useState("fu1")
  const navigate = useNavigate()

  useEffect(() => {
    const s = getFollowUpSettings()
    setSettings(s)
    load(s)
  }, [])

  async function load(s) {
    const now = new Date()
    const fu1From = new Date(now - s.followup2Days * 86400000).toISOString()
    const fu1To   = new Date(now - s.followup1Days * 86400000).toISOString()
    const fu2To   = new Date(now - s.followup2Days * 86400000).toISOString()

    const [{ data: fu1 }, { data: fu2 }] = await Promise.all([
      supabase.from("leads").select("*")
        .eq("status", "email_sent")
        .lte("email_sent_at", fu1To)
        .gte("email_sent_at", fu1From)
        .order("email_sent_at", { ascending: true }),
      supabase.from("leads").select("*")
        .eq("status", "email_sent")
        .lte("email_sent_at", fu2To)
        .order("email_sent_at", { ascending: true }),
    ])

    setLeads({ fu1: fu1 ?? [], fu2: fu2 ?? [] })
    setLoading(false)
  }

  function daysSince(d) {
    return d ? Math.floor((Date.now() - new Date(d)) / 86400000) : null
  }

  function urgencyColor(days, threshold) {
    const over = days - threshold
    if (over >= 3) return "text-red-600 bg-red-50 border-red-200"
    if (over >= 1) return "text-amber-600 bg-amber-50 border-amber-200"
    return "text-blue-600 bg-blue-50 border-blue-200"
  }

  if (loading) return <div className="flex items-center justify-center h-64 text-gray-400">Loading…</div>

  const current = tab === "fu1" ? leads.fu1 : leads.fu2
  const threshold = tab === "fu1" ? settings.followup1Days : settings.followup2Days
  const tabLabel = tab === "fu1"
    ? `Follow-up 1 (${settings.followup1Days}–${settings.followup2Days - 1} days)`
    : `Follow-up 2 (${settings.followup2Days}+ days)`

  return (
    <div>
      <div className="flex items-center justify-between mb-6">
        <div>
          <h1 className="text-2xl font-bold font-heading text-gray-900">Follow-Up Tracker</h1>
          <p className="text-sm text-gray-500 mt-1">Leads with no reply, sorted by urgency</p>
        </div>
        <button onClick={() => navigate("/settings")}
          className="flex items-center gap-2 text-sm text-gray-500 hover:text-gray-900 bg-white border border-gray-200 px-3 py-2 rounded-lg transition-colors">
          <Settings size={14} /> Configure timing
        </button>
      </div>

      {/* Tabs */}
      <div className="flex gap-2 mb-5">
        {["fu1","fu2"].map(t => (
          <button key={t} onClick={() => setTab(t)}
            className={`px-4 py-2 rounded-full text-sm font-medium transition-all ${
              tab === t ? "bg-[#0D1B2A] text-white" : "bg-white border border-gray-200 text-gray-600 hover:bg-gray-50"
            }`}>
            {t === "fu1" ? `Follow-up 1 (${leads.fu1?.length ?? 0})` : `Follow-up 2 (${leads.fu2?.length ?? 0})`}
          </button>
        ))}
      </div>

      {current.length === 0 ? (
        <div className="bg-white rounded-xl border border-gray-100 shadow-sm p-16 text-center">
          <Clock size={40} className="text-gray-200 mx-auto mb-3" />
          <p className="text-gray-400 font-medium">No {tab === "fu1" ? "follow-up 1s" : "follow-up 2s"} due</p>
          <p className="text-gray-300 text-sm mt-1">
            {tab === "fu1"
              ? `Leads sent ${settings.followup1Days}–${settings.followup2Days - 1} days ago with no reply will appear here`
              : `Leads sent ${settings.followup2Days}+ days ago with no reply will appear here`}
          </p>
        </div>
      ) : (
        <>
          <div className="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-5 flex items-center gap-3">
            <AlertTriangle size={18} className="text-amber-600 flex-shrink-0" />
            <p className="text-sm text-amber-800">
              <strong>{current.length} lead{current.length !== 1 ? "s" : ""}</strong> ready for {tab === "fu1" ? "a first" : "a second"} follow-up.
              Send a short, friendly nudge referencing the mock site.
            </p>
          </div>

          <div className="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
            <table className="w-full text-sm">
              <thead>
                <tr className="border-b border-gray-100 bg-gray-50">
                  <th className="text-left px-6 py-3 font-semibold text-gray-500 text-xs uppercase tracking-wide">Business</th>
                  <th className="text-left px-4 py-3 font-semibold text-gray-500 text-xs uppercase tracking-wide hidden md:table-cell">Industry</th>
                  <th className="text-left px-4 py-3 font-semibold text-gray-500 text-xs uppercase tracking-wide hidden lg:table-cell">City</th>
                  <th className="text-left px-4 py-3 font-semibold text-gray-500 text-xs uppercase tracking-wide">Sent</th>
                  <th className="text-left px-4 py-3 font-semibold text-gray-500 text-xs uppercase tracking-wide">Days Waiting</th>
                  <th className="text-left px-4 py-3 font-semibold text-gray-500 text-xs uppercase tracking-wide hidden lg:table-cell">Account / Copy</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-gray-50">
                {current.map(lead => {
                  const days = daysSince(lead.email_sent_at)
                  const uc   = urgencyColor(days, threshold)
                  return (
                    <tr key={lead.id} onClick={() => navigate(`/leads/${lead.id}`)}
                      className="hover:bg-gray-50 cursor-pointer transition-colors">
                      <td className="px-6 py-3">
                        <div className="flex items-center gap-3">
                          <div className="w-8 h-8 rounded-full bg-[#0D1B2A] flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                            {lead.business_name?.charAt(0)}
                          </div>
                          <div>
                            <div className="font-medium text-gray-900">{lead.business_name}</div>
                            <div className="text-xs text-gray-400">{lead.email}</div>
                          </div>
                        </div>
                      </td>
                      <td className="px-4 py-3 text-gray-500 hidden md:table-cell">{lead.industry}</td>
                      <td className="px-4 py-3 text-gray-500 hidden lg:table-cell">{lead.city}</td>
                      <td className="px-4 py-3 text-xs text-gray-400">
                        <div className="flex items-center gap-1.5"><Mail size={12} />{fmtDate(lead.email_sent_at)}</div>
                      </td>
                      <td className="px-4 py-3">
                        <span className={`inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold border ${uc}`}>
                          <Clock size={11} />{days}d ago
                        </span>
                      </td>
                      <td className="px-4 py-3 hidden lg:table-cell">
                        {lead.email_sent_from && <span className="text-xs text-gray-500">{lead.email_sent_from.split("@")[0]}</span>}
                        {lead.copy_version && <span className="ml-1.5 text-xs font-mono bg-gray-100 text-gray-600 px-1.5 py-0.5 rounded">{lead.copy_version}</span>}
                      </td>
                    </tr>
                  )
                })}
              </tbody>
            </table>
          </div>
        </>
      )}
    </div>
  )
}
