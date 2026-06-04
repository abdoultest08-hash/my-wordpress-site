export const STATUS_CONFIG = {
  pending:         { label: "Pending",        color: "bg-gray-100 text-gray-600",    dot: "bg-gray-400"   },
  site_generated:  { label: "Site Generated", color: "bg-blue-100 text-blue-700",   dot: "bg-blue-500"   },
  email_sent:      { label: "Email Sent",     color: "bg-yellow-100 text-yellow-700",dot: "bg-yellow-500" },
  replied:         { label: "Replied",        color: "bg-purple-100 text-purple-700",dot: "bg-purple-500" },
  positive:        { label: "Positive ✨",    color: "bg-green-100 text-green-700",  dot: "bg-green-500"  },
  meeting_booked:  { label: "Meeting Booked", color: "bg-indigo-100 text-indigo-700",dot: "bg-indigo-500" },
  closed:          { label: "Closed 🎉",      color: "bg-emerald-100 text-emerald-700",dot:"bg-emerald-500"},
  not_interested:  { label: "Not Interested", color: "bg-red-100 text-red-600",     dot: "bg-red-400"    },
}

export const PIPELINE_STAGES = [
  "email_sent", "replied", "positive", "meeting_booked", "closed"
]

export const fmt = (n) => n?.toLocaleString() ?? 0

export const fmtDate = (d) => d
  ? new Date(d).toLocaleDateString("en-GB", { day:"numeric", month:"short", year:"numeric" })
  : "—"

export const fmtCurrency = (n) =>
  new Intl.NumberFormat("en-GB", { style:"currency", currency:"GBP", maximumFractionDigits:0 }).format(n || 0)
