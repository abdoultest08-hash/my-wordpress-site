import { useEffect, useState } from "react"
import { supabase } from "../lib/supabase"
import { fmtDate } from "../lib/utils"
import { useNavigate } from "react-router-dom"
import { Clock, Mail, AlertTriangle } from "lucide-react"

export default function FollowUp() {
  const [leads, setLeads] = useState([])
  const [loading, setLoading] = useState(true)
  const navigate = useNavigate()

  useEffect(() => {
    async function load() {
      const now = new Date()
      const threeDaysAgo = new Date(now - 3 * 24 * 60 * 60 * 1000).toISOString()
      const sevenDaysAgo = new Date(now - 7 * 24 * 60 * 60 * 1000).toISOString()

      // Leads emailed between 3–7 days ago, still in email_sent status (no reply)
      const { data } = await supabase
        .from("leads")
        .select("*")
        .eq("status", "email_sent")
        .gte("email_sent_at", sevenDaysAgo)
        .lte("email_sent_at", threeDaysAgo)
        .order("email_sent_at", { ascending: true })

      setLeads(data ?? [])
      setLoading(false)
    }
    load()
  }, [])

  function daysSince(dateStr) {
    if (!dateStr) return null
    return Math.floor((Date.now() - new Date(dateStr)) / (1000 * 60 * 60 * 24))
  }

  function urgencyColor(days) {
    if (days >= 6) return "text-red-600 bg-red-50 border-red-200"
    if (days >= 4) return "text-amber-600 bg-amber-50 border-amber-200"
    return "text-blue-600 bg-blue-50 border-blue-200"
  }

  if (loading) return <div className="flex items-center justify-center h-64 text-gray-400">Loading...</div>

  return (
    <div>
      <div className="mb-6">
        <h1 className="text-2xl font-bold font-heading text-gray-900">Follow-Up Tracker</h1>
        <p className="text-sm text-gray-500 mt-1">Leads emailed 3–7 days ago with no reply — ready for a nudge</p>
      </div>

      {leads.length === 0 ? (
        <div className="bg-white rounded-xl border border-gray-100 shadow-sm p-16 text-center">
          <Clock size={40} className="text-gray-200 mx-auto mb-3" />
          <p className="text-gray-400 font-medium">No follow-ups due right now</p>
          <p className="text-gray-300 text-sm mt-1">Check back once emails have been out 3+ days</p>
        </div>
      ) : (
        <>
          <div className="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-5 flex items-center gap-3">
            <AlertTriangle size={18} className="text-amber-600 flex-shrink-0" />
            <p className="text-sm text-amber-800">
              <strong>{leads.length} lead{leads.length !== 1 ? "s" : ""}</strong> ready for a follow-up email.
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
                  <th className="text-left px-4 py-3 font-semibold text-gray-500 text-xs uppercase tracking-wide hidden lg:table-cell">Copy</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-gray-50">
                {leads.map(lead => {
                  const days = daysSince(lead.email_sent_at)
                  const uc = urgencyColor(days)
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
                      <td className="px-4 py-3 text-gray-400 text-xs">
                        <div className="flex items-center gap-1.5">
                          <Mail size={12} />
                          {fmtDate(lead.email_sent_at)}
                        </div>
                      </td>
                      <td className="px-4 py-3">
                        <span className={`inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold border ${uc}`}>
                          <Clock size={11} />
                          {days}d ago
                        </span>
                      </td>
                      <td className="px-4 py-3 hidden lg:table-cell">
                        {lead.copy_version && (
                          <span className="px-2 py-0.5 rounded text-xs font-mono bg-gray-100 text-gray-600">{lead.copy_version}</span>
                        )}
                        {lead.pitch_type && (
                          <span className={`ml-1 px-2 py-0.5 rounded text-xs font-medium ${lead.pitch_type === "new" ? "bg-purple-100 text-purple-600" : "bg-blue-100 text-blue-600"}`}>
                            {lead.pitch_type}
                          </span>
                        )}
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
