import { useEffect, useState } from "react"
import { supabase } from "../lib/supabase"
import { fmtDate } from "../lib/utils"
import { useNavigate } from "react-router-dom"

const STAGES = [
  { key: "email_sent",     label: "Email Sent",     color: "border-yellow-400", dot: "bg-yellow-400" },
  { key: "replied",        label: "Replied",        color: "border-purple-400", dot: "bg-purple-400" },
  { key: "positive",       label: "Positive ✨",    color: "border-green-400",  dot: "bg-green-400"  },
  { key: "meeting_booked", label: "Meeting Booked", color: "border-indigo-400", dot: "bg-indigo-400" },
  { key: "closed",         label: "Closed 🎉",      color: "border-emerald-400",dot: "bg-emerald-400"},
]

export default function Pipeline() {
  const [leads, setLeads] = useState([])
  const [dragging, setDragging] = useState(null)
  const navigate = useNavigate()

  useEffect(() => { loadLeads() }, [])

  async function loadLeads() {
    const { data } = await supabase.from("leads")
      .select("id, business_name, industry, city, email, status, email_sent_at, replied_at, deal_value, owner_name")
      .in("status", STAGES.map(s => s.key))
      .order("email_sent_at", { ascending: false })
    setLeads(data ?? [])
  }

  async function moveStage(leadId, newStatus) {
    await supabase.from("leads").update({ status: newStatus }).eq("id", leadId)
    setLeads(prev => prev.map(l => l.id === leadId ? { ...l, status: newStatus } : l))
  }

  const byStage = (key) => leads.filter(l => l.status === key)

  return (
    <div>
      <div className="mb-6">
        <h1 className="text-2xl font-bold font-heading text-gray-900">Pipeline</h1>
        <p className="text-sm text-gray-500 mt-1">Drag cards to move leads through stages</p>
      </div>

      <div className="flex gap-4 overflow-x-auto pb-4">
        {STAGES.map(stage => (
          <div key={stage.key} className="flex-shrink-0 w-72"
            onDragOver={e => e.preventDefault()}
            onDrop={async () => { if (dragging) { await moveStage(dragging, stage.key); setDragging(null) } }}>

            {/* Column header */}
            <div className={`bg-white rounded-t-xl px-4 py-3 border-t-4 ${stage.color} border-x border-gray-200 flex items-center justify-between`}>
              <div className="flex items-center gap-2">
                <div className={`w-2 h-2 rounded-full ${stage.dot}`}></div>
                <span className="font-semibold text-sm font-heading text-gray-800">{stage.label}</span>
              </div>
              <span className="bg-gray-100 text-gray-500 text-xs font-bold px-2 py-0.5 rounded-full">
                {byStage(stage.key).length}
              </span>
            </div>

            {/* Cards */}
            <div className="bg-gray-50 border-x border-b border-gray-200 rounded-b-xl min-h-32 p-2 space-y-2">
              {byStage(stage.key).length === 0 && (
                <div className="text-center text-gray-300 text-xs py-6">Empty</div>
              )}
              {byStage(stage.key).map(lead => (
                <div key={lead.id}
                  draggable
                  onDragStart={() => setDragging(lead.id)}
                  onClick={() => navigate(`/leads/${lead.id}`)}
                  className="bg-white rounded-lg p-3 shadow-sm border border-gray-100 cursor-grab active:cursor-grabbing hover:shadow-md transition-shadow">
                  <div className="flex items-start justify-between gap-2 mb-2">
                    <div className="font-semibold text-sm text-gray-900 leading-tight">{lead.business_name}</div>
                    {lead.deal_value > 0 && (
                      <span className="text-xs font-bold text-emerald-600 bg-emerald-50 px-1.5 py-0.5 rounded whitespace-nowrap">£{lead.deal_value}</span>
                    )}
                  </div>
                  <div className="text-xs text-gray-400">{lead.industry} · {lead.city}</div>
                  {lead.owner_name && <div className="text-xs text-gray-500 mt-1">👤 {lead.owner_name}</div>}
                  <div className="text-xs text-gray-300 mt-2">{fmtDate(lead.email_sent_at)}</div>
                </div>
              ))}
            </div>
          </div>
        ))}
      </div>
    </div>
  )
}
