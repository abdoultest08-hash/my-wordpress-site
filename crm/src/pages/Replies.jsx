import { useEffect, useState } from "react"
import { supabase } from "../lib/supabase"
import { fmtDate } from "../lib/utils"
import { useNavigate } from "react-router-dom"
import { MessageSquare, ThumbsUp, ThumbsDown, Tag } from "lucide-react"

const REPLY_TAGS = [
  { label: "Hot",          color: "bg-red-100 text-red-700 border-red-300",       active: "bg-red-500 text-white border-red-500"    },
  { label: "Warm",         color: "bg-amber-100 text-amber-700 border-amber-300", active: "bg-amber-500 text-white border-amber-500" },
  { label: "Cold",         color: "bg-blue-100 text-blue-700 border-blue-300",    active: "bg-blue-500 text-white border-blue-500"   },
  { label: "Wrong person", color: "bg-gray-100 text-gray-500 border-gray-300",    active: "bg-gray-500 text-white border-gray-500"   },
]

export default function Replies() {
  const [replies, setReplies] = useState([])
  const [filter, setFilter]   = useState("all")
  const [loading, setLoading] = useState(true)
  const navigate = useNavigate()

  useEffect(() => { load() }, [])

  async function load() {
    const { data } = await supabase
      .from("replies")
      .select("*, leads(business_name, email, industry, city, email_sent_from, copy_version, pitch_type)")
      .order("received_at", { ascending: false })
    setReplies(data ?? [])
    setLoading(false)
  }

  async function setReplyTag(replyId, tag) {
    await supabase.from("replies").update({ quality_tag: tag }).eq("id", replyId)
    setReplies(prev => prev.map(r => r.id === replyId ? { ...r, quality_tag: tag } : r))
  }

  const filtered = replies.filter(r =>
    filter === "all" ? true :
    filter === "positive" ? r.is_positive :
    filter === "negative" ? !r.is_positive : true
  )

  const positiveCount = replies.filter(r => r.is_positive).length

  if (loading) return <div className="flex items-center justify-center h-64 text-gray-400">Loading…</div>

  return (
    <div>
      <div className="flex items-center justify-between mb-6">
        <div>
          <h1 className="text-2xl font-bold font-heading text-gray-900">Replies</h1>
          <p className="text-sm text-gray-500 mt-1">{replies.length} total · {positiveCount} positive</p>
        </div>
        <div className="flex items-center gap-2">
          {["all","positive","negative"].map(f => (
            <button key={f} onClick={() => setFilter(f)}
              className={`px-4 py-2 rounded-full text-sm font-medium transition-all ${
                filter === f ? "bg-[#0D1B2A] text-white" : "bg-white border border-gray-200 text-gray-600 hover:bg-gray-50"
              }`}>
              {f === "all" ? `All (${replies.length})` : f === "positive" ? `✨ Positive (${positiveCount})` : `Negative (${replies.length - positiveCount})`}
            </button>
          ))}
        </div>
      </div>

      {filtered.length === 0 ? (
        <div className="bg-white rounded-xl border border-gray-100 shadow-sm p-16 text-center">
          <MessageSquare size={40} className="text-gray-200 mx-auto mb-3" />
          <p className="text-gray-400 font-medium">No replies yet</p>
          <p className="text-gray-300 text-sm mt-1">Replies logged by the pipeline will appear here</p>
        </div>
      ) : (
        <div className="space-y-3">
          {filtered.map(r => {
            const lead = r.leads
            return (
              <div key={r.id} className={`bg-white rounded-xl border shadow-sm p-5 ${r.is_positive ? "border-green-200" : "border-gray-100"}`}>
                <div className="flex items-start justify-between gap-4">
                  <div className="flex items-start gap-4 flex-1 min-w-0">
                    <div className={`w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm flex-shrink-0 ${r.is_positive ? "bg-green-100 text-green-700" : "bg-gray-100 text-gray-600"}`}>
                      {lead?.business_name?.charAt(0) ?? "?"}
                    </div>
                    <div className="flex-1 min-w-0">
                      <div className="flex items-center gap-2 flex-wrap">
                        <button onClick={() => navigate(`/leads/${r.lead_id}`)}
                          className="font-semibold text-gray-900 hover:text-[#F5A623] text-sm transition-colors">
                          {lead?.business_name}
                        </button>
                        {r.is_positive
                          ? <span className="flex items-center gap-1 text-xs text-green-700 bg-green-100 px-2 py-0.5 rounded-full font-medium"><ThumbsUp size={10}/> Positive</span>
                          : <span className="flex items-center gap-1 text-xs text-gray-500 bg-gray-100 px-2 py-0.5 rounded-full font-medium"><ThumbsDown size={10}/> Not interested</span>
                        }
                      </div>
                      <div className="text-xs text-gray-400 mt-0.5">{lead?.email} · {lead?.industry} · {lead?.city}</div>
                      {r.snippet && (
                        <p className="mt-2 text-sm text-gray-700 italic bg-gray-50 rounded-lg p-3">"{r.snippet}"</p>
                      )}
                      {/* Outreach context */}
                      <div className="flex items-center gap-2 mt-2 flex-wrap">
                        {lead?.copy_version && <span className="text-xs font-mono bg-gray-100 text-gray-500 px-1.5 py-0.5 rounded">{lead.copy_version}</span>}
                        {lead?.pitch_type && <span className={`text-xs px-1.5 py-0.5 rounded font-medium ${lead.pitch_type === "new" ? "bg-purple-100 text-purple-600" : "bg-blue-100 text-blue-600"}`}>{lead.pitch_type}</span>}
                        {lead?.email_sent_from && <span className="text-xs text-gray-400">via {lead.email_sent_from.split("@")[0]}</span>}
                      </div>
                      {/* Quality tags */}
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
                  </div>
                  <div className="text-xs text-gray-400 flex-shrink-0">{fmtDate(r.received_at)}</div>
                </div>
              </div>
            )
          })}
        </div>
      )}
    </div>
  )
}
