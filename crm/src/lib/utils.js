export const STATUS_CONFIG = {
  pending:         { label: "Pending",        color: "bg-gray-100 text-gray-600",      dot: "bg-gray-400"    },
  site_generated:  { label: "Site Generated", color: "bg-blue-100 text-blue-700",      dot: "bg-blue-500"    },
  draft_ready:     { label: "Draft Ready",    color: "bg-orange-100 text-orange-700",  dot: "bg-orange-500"  },
  email_sent:      { label: "Email Sent",     color: "bg-yellow-100 text-yellow-700",  dot: "bg-yellow-500"  },
  replied:         { label: "Replied",        color: "bg-purple-100 text-purple-700",  dot: "bg-purple-500"  },
  positive:        { label: "Positive ✨",    color: "bg-green-100 text-green-700",    dot: "bg-green-500"   },
  meeting_booked:  { label: "Meeting Booked", color: "bg-indigo-100 text-indigo-700",  dot: "bg-indigo-500"  },
  closed:          { label: "Closed 🎉",      color: "bg-emerald-100 text-emerald-700",dot: "bg-emerald-500" },
  not_interested:  { label: "Not Interested", color: "bg-red-100 text-red-600",        dot: "bg-red-400"     },
}

// Returns effective status: site_generated + email_sent_from = "draft_ready"
export function effectiveStatus(lead) {
  if (lead.status === "site_generated" && lead.email_sent_from) return "draft_ready"
  return lead.status
}

export const PIPELINE_STAGES = [
  "email_sent", "replied", "positive", "meeting_booked", "closed"
]

export const ALL_STAGES = [
  "pending", "site_generated", "draft_ready", "email_sent",
  "replied", "positive", "meeting_booked", "closed", "not_interested"
]

export const fmt = (n) => (n ?? 0).toLocaleString()

export const fmtDate = (d) => d
  ? new Date(d).toLocaleDateString("en-GB", { day: "numeric", month: "short", year: "numeric" })
  : "—"

export const fmtCurrency = (n) =>
  new Intl.NumberFormat("en-GB", { style: "currency", currency: "GBP", maximumFractionDigits: 0 }).format(n || 0)

export function getFollowUpSettings() {
  try {
    const raw = localStorage.getItem("followup_settings")
    if (raw) return JSON.parse(raw)
  } catch {}
  return { followup1Days: 3, followup2Days: 7 }
}

export function saveFollowUpSettings(settings) {
  localStorage.setItem("followup_settings", JSON.stringify(settings))
}
