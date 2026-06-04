import { useEffect, useState } from "react"
import { useParams, useNavigate } from "react-router-dom"
import { supabase } from "../lib/supabase"
import { STATUS_CONFIG, fmtDate, fmtCurrency } from "../lib/utils"
import { ArrowLeft, Mail, Phone, MapPin, Building2, ExternalLink } from "lucide-react"

const STAGES = ["pending","site_generated","email_sent","replied","positive","meeting_booked","closed","not_interested"]

export default function LeadDetail() {
  const { id } = useParams()
  const navigate = useNavigate()
  const [lead, setLead] = useState(null)
  const [emails, setEmails] = useState([])
  const [replies, setReplies] = useState([])
  const [saving, setSaving] = useState(false)
  const [dealValue, setDealValue] = useState("")

  useEffect(() => {
    async function load() {
      const { data: l } = await supabase.from("leads").select("*").eq("id", id).single()
      const { data: e } = await supabase.from("email_log").select("*").eq("lead_id", id).order("created_at", { ascending: false })
      const { data: r } = await supabase.from("replies").select("*").eq("lead_id", id).order("received_at", { ascending: false })
      setLead(l)
      setEmails(e ?? [])
      setReplies(r ?? [])
      setDealValue(l?.deal_value || "")
    }
    load()
  }, [id])

  async function updateStatus(status) {
    setSaving(true)
    const updates = { status }
    if (status === "meeting_booked") updates.meeting_at = new Date().toISOString()
    if (status === "closed") { updates.closed_at = new Date().toISOString(); updates.deal_value = Number(dealValue) || 0 }
    await supabase.from("leads").update(updates).eq("id", id)
    setLead(prev => ({ ...prev, ...updates }))
    setSaving(false)
  }

  if (!lead) return <div className="flex items-center justify-center h-64 text-gray-400">Loading...</div>

  const cfg = STATUS_CONFIG[lead.status] ?? STATUS_CONFIG.pending

  return (
    <div className="max-w-4xl">
      {/* Back */}
      <button onClick={() => navigate(-1)} className="flex items-center gap-2 text-sm text-gray-500 hover:text-gray-900 mb-5 transition-colors">
        <ArrowLeft size={15} /> Back
      </button>

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-5">
        {/* Left — Profile */}
        <div className="lg:col-span-1 space-y-4">
          <div className="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <div className="flex items-center gap-3 mb-4">
              {lead.logo_url
                ? <img src={lead.logo_url} alt="Logo" className="w-12 h-12 rounded-full object-cover" />
                : <div className="w-12 h-12 rounded-full bg-[#0D1B2A] flex items-center justify-center text-white font-bold text-lg">{lead.business_name?.charAt(0)}</div>
              }
              <div>
                <h2 className="font-bold font-heading text-gray-900">{lead.business_name}</h2>
                <span className={`text-xs px-2 py-0.5 rounded-full font-medium ${cfg.color}`}>{cfg.label}</span>
              </div>
            </div>

            <div className="space-y-2.5 text-sm">
              {lead.owner_name && <div className="flex gap-2"><Building2 size={14} className="text-gray-400 mt-0.5 flex-shrink-0"/><span className="text-gray-600">{lead.owner_name}</span></div>}
              <div className="flex gap-2"><Mail size={14} className="text-gray-400 mt-0.5 flex-shrink-0"/><a href={`mailto:${lead.email}`} className="text-[#F5A623] hover:underline break-all">{lead.email}</a></div>
              {lead.phone && <div className="flex gap-2"><Phone size={14} className="text-gray-400 mt-0.5 flex-shrink-0"/><span className="text-gray-600">{lead.phone}</span></div>}
              {lead.city && <div className="flex gap-2"><MapPin size={14} className="text-gray-400 mt-0.5 flex-shrink-0"/><span className="text-gray-600">{lead.city}{lead.state ? `, ${lead.state}` : ""}</span></div>}
              {lead.gmb_url && <div className="flex gap-2"><ExternalLink size={14} className="text-gray-400 mt-0.5 flex-shrink-0"/><a href={lead.gmb_url} target="_blank" rel="noreferrer" className="text-[#F5A623] hover:underline text-xs">Google Business</a></div>}
            </div>

            {lead.notes && <div className="mt-4 p-3 bg-gray-50 rounded-lg text-xs text-gray-500">{lead.notes}</div>}
          </div>

          {/* Timestamps */}
          <div className="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <h3 className="font-semibold text-sm text-gray-700 mb-3">Timeline</h3>
            <div className="space-y-2 text-xs text-gray-500">
              <div className="flex justify-between"><span>Added</span><span className="font-medium">{fmtDate(lead.created_at)}</span></div>
              <div className="flex justify-between"><span>Email sent</span><span className="font-medium">{fmtDate(lead.email_sent_at)}</span></div>
              <div className="flex justify-between"><span>Replied</span><span className="font-medium">{fmtDate(lead.replied_at)}</span></div>
              <div className="flex justify-between"><span>Meeting</span><span className="font-medium">{fmtDate(lead.meeting_at)}</span></div>
              <div className="flex justify-between"><span>Closed</span><span className="font-medium">{fmtDate(lead.closed_at)}</span></div>
              {lead.status === "closed" && <div className="flex justify-between text-emerald-600 font-semibold"><span>Deal value</span><span>{fmtCurrency(lead.deal_value)}</span></div>}
            </div>
          </div>
        </div>

        {/* Right — Actions + Activity */}
        <div className="lg:col-span-2 space-y-4">
          {/* Move stage */}
          <div className="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <h3 className="font-semibold text-sm text-gray-700 mb-3">Move Stage</h3>
            <div className="flex flex-wrap gap-2">
              {STAGES.map(s => {
                const c = STATUS_CONFIG[s] ?? STATUS_CONFIG.pending
                return (
                  <button key={s} onClick={() => updateStatus(s)} disabled={saving || lead.status === s}
                    className={`px-3 py-1.5 rounded-full text-xs font-medium transition-all border ${
                      lead.status === s ? `${c.color} border-transparent` : "border-gray-200 text-gray-500 hover:border-gray-400"
                    }`}>
                    {c.label}
                  </button>
                )
              })}
            </div>
            {lead.status === "closed" || (
              <div className="mt-3 flex items-center gap-3">
                <input value={dealValue} onChange={e => setDealValue(e.target.value)} placeholder="Deal value (£)"
                  className="border border-gray-200 rounded-lg px-3 py-2 text-sm w-36 focus:outline-none focus:border-[#F5A623]" />
                <button onClick={() => supabase.from("leads").update({ deal_value: Number(dealValue) }).eq("id", id)}
                  className="bg-[#0D1B2A] text-white px-4 py-2 rounded-lg text-xs font-semibold">Save Value</button>
              </div>
            )}
          </div>

          {/* Email activity */}
          <div className="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <h3 className="font-semibold text-sm text-gray-700 mb-3">Email Activity ({emails.length})</h3>
            {emails.length === 0
              ? <p className="text-sm text-gray-400">No emails sent yet</p>
              : <div className="space-y-2">{emails.map(e => (
                  <div key={e.id} className="flex items-center gap-3 text-xs bg-gray-50 rounded-lg p-3">
                    <Mail size={13} className="text-[#F5A623] flex-shrink-0" />
                    <div className="flex-1"><div className="font-medium text-gray-700">{e.subject}</div><div className="text-gray-400">From: {e.from_account}</div></div>
                    <div className="text-gray-400">{fmtDate(e.created_at)}</div>
                  </div>
                ))}</div>
            }
          </div>

          {/* Replies */}
          <div className="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <h3 className="font-semibold text-sm text-gray-700 mb-3">Replies ({replies.length})</h3>
            {replies.length === 0
              ? <p className="text-sm text-gray-400">No replies yet</p>
              : <div className="space-y-2">{replies.map(r => (
                  <div key={r.id} className={`rounded-lg p-3 text-xs ${r.is_positive ? "bg-green-50 border border-green-200" : "bg-gray-50"}`}>
                    <div className="flex items-center justify-between mb-1">
                      <span className={`font-semibold ${r.is_positive ? "text-green-700" : "text-gray-600"}`}>{r.is_positive ? "✨ Positive reply" : "Reply received"}</span>
                      <span className="text-gray-400">{fmtDate(r.received_at)}</span>
                    </div>
                    {r.snippet && <p className="text-gray-600 italic">"{r.snippet}"</p>}
                  </div>
                ))}</div>
            }
          </div>
        </div>
      </div>
    </div>
  )
}
