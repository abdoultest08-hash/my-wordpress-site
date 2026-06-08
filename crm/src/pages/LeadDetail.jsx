import { useEffect, useState } from "react"
import { useParams, useNavigate } from "react-router-dom"
import { supabase } from "../lib/supabase"
import { STATUS_CONFIG, fmtDate, fmtCurrency } from "../lib/utils"
import { ArrowLeft, Mail, Phone, MapPin, Building2, ExternalLink, Tag } from "lucide-react"

const STAGES = ["pending","site_generated","email_sent","replied","positive","meeting_booked","closed","not_interested"]

const REPLY_TAGS = [
  { label: "Hot",          color: "bg-red-100 text-red-700 border-red-300",     active: "bg-red-500 text-white border-red-500"    },
  { label: "Warm",         color: "bg-amber-100 text-amber-700 border-amber-300", active: "bg-amber-500 text-white border-amber-500"},
  { label: "Cold",         color: "bg-blue-100 text-blue-700 border-blue-300",  active: "bg-blue-500 text-white border-blue-500"  },
  { label: "Wrong person", color: "bg-gray-100 text-gray-500 border-gray-300",  active: "bg-gray-500 text-white border-gray-500"  },
]

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

  async function setReplyTag(replyId, tag) {
    await supabase.from("replies").update({ quality_tag: tag }).eq("id", replyId)
    setReplies(prev => prev.map(r => r.id === replyId ? { ...r, quality_tag: tag } : r))
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

            {/* Copy / Pitch tags */}
            <div className="flex items-center gap-2 mt-4 flex-wrap">
              {lead.copy_version && (
                <span className="px-2.5 py-1 rounded-full text-xs font-mono bg-gray-100 text-gray-600 border border-gray-200">
                  {lead.copy_version}
                </span>
              )}
              {lead.pitch_type && (
                <span className={`px-2.5 py-1 rounded-full text-xs font-medium border ${lead.pitch_type === "new" ? "bg-purple-100 text-purple-700 border-purple-200" : "bg-blue-100 text-blue-700 border-blue-200"}`}>
                  {lead.pitch_type === "new" ? "No website" : "Has website"}
                </span>
              )}
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
                  <div key={e.id} className="flex items-start gap-3 text-xs bg-gray-50 rounded-lg p-3">
                    <Mail size={13} className="text-[#F5A623] flex-shrink-0 mt-0.5" />
                    <div className="flex-1">
                      <div className="font-medium text-gray-700">{e.subject}</div>
                      <div className="text-gray-400">From: {e.from_account}</div>
                      <div className="flex gap-2 mt-1">
                        {e.copy_version && <span className="font-mono bg-white border border-gray-200 px-1.5 py-0.5 rounded text-gray-500">{e.copy_version}</span>}
                        {e.pitch_type   && <span className={`px-1.5 py-0.5 rounded font-medium ${e.pitch_type === "new" ? "bg-purple-50 text-purple-600" : "bg-blue-50 text-blue-600"}`}>{e.pitch_type}</span>}
                      </div>
                    </div>
                    <div className="text-gray-400">{fmtDate(e.created_at)}</div>
                  </div>
                ))}</div>
            }
          </div>

          {/* Replies with quality tags */}
          <div className="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <h3 className="font-semibold text-sm text-gray-700 mb-3">Replies ({replies.length})</h3>
            {replies.length === 0
              ? <p className="text-sm text-gray-400">No replies yet</p>
              : <div className="space-y-3">{replies.map(r => (
                  <div key={r.id} className={`rounded-lg p-3 text-xs ${r.is_positive ? "bg-green-50 border border-green-200" : "bg-gray-50"}`}>
                    <div className="flex items-center justify-between mb-1">
                      <span className={`font-semibold ${r.is_positive ? "text-green-700" : "text-gray-600"}`}>{r.is_positive ? "✨ Positive reply" : "Reply received"}</span>
                      <span className="text-gray-400">{fmtDate(r.received_at)}</span>
                    </div>
                    {r.snippet && <p className="text-gray-600 italic mb-2">"{r.snippet}"</p>}
                    {/* Quality tag picker */}
                    <div className="flex items-center gap-1.5 flex-wrap mt-2">
                      <Tag size={11} className="text-gray-400" />
                      {REPLY_TAGS.map(t => (
                        <button key={t.label} onClick={() => setReplyTag(r.id, t.label)}
                          className={`px-2 py-0.5 rounded-full text-xs font-medium border transition-all ${r.quality_tag === t.label ? t.active : t.color}`}>
                          {t.label}
                        </button>
                      ))}
                    </div>
                  </div>
                ))}</div>
            }
          </div>
        </div>
      </div>
    </div>
  )
}
