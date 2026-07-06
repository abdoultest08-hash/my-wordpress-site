import { NavLink } from "react-router-dom"
import { useEffect, useState } from "react"
import { supabase } from "../lib/supabase"
import { getFollowUpSettings } from "../lib/utils"
import {
  LayoutDashboard, Users, Kanban, BarChart3,
  Calculator, Clock, MessageSquare, Settings
} from "lucide-react"

export default function Sidebar() {
  const [followUpCount, setFollowUpCount] = useState(0)
  const [replyCount, setReplyCount]       = useState(0)

  useEffect(() => {
    async function loadBadges() {
      const { followup1Days, followup2Days } = getFollowUpSettings()
      const now = new Date()
      const cutoff = new Date(now - followup1Days * 24 * 60 * 60 * 1000).toISOString()
      const { data: fu } = await supabase
        .from("leads")
        .select("id", { count: "exact" })
        .eq("status", "email_sent")
        .lte("email_sent_at", cutoff)
      setFollowUpCount(fu?.length ?? 0)

      const { data: rp } = await supabase
        .from("replies")
        .select("id", { count: "exact" })
        .eq("seen", false)
      setReplyCount(rp?.length ?? 0)
    }
    loadBadges()
  }, [])

  const links = [
    { to: "/",           icon: LayoutDashboard, label: "Dashboard"  },
    { to: "/pipeline",   icon: Kanban,          label: "Pipeline"   },
    { to: "/leads",      icon: Users,           label: "Leads"      },
    { to: "/replies",    icon: MessageSquare,   label: "Replies",    badge: replyCount    },
    { to: "/kpis",       icon: BarChart3,       label: "KPIs"       },
    { to: "/followup",   icon: Clock,           label: "Follow-Up",  badge: followUpCount },
    { to: "/calculator", icon: Calculator,      label: "Calculator" },
    { to: "/settings",   icon: Settings,        label: "Settings"   },
  ]

  return (
    <aside className="w-60 min-h-screen bg-[#0D1B2A] flex flex-col fixed left-0 top-0 bottom-0 z-30">
      <div className="px-6 py-5 border-b border-white/10">
        <div className="flex items-center gap-3">
          <div className="w-9 h-9 bg-[#F5A623] rounded-lg flex items-center justify-center font-bold text-[#0D1B2A] text-sm font-heading">AB</div>
          <div>
            <div className="text-white font-semibold text-sm font-heading">Sites By Abs</div>
            <div className="text-white/40 text-xs">Outreach CRM</div>
          </div>
        </div>
      </div>

      <nav className="flex-1 px-3 py-4 space-y-1">
        {links.map(({ to, icon: Icon, label, badge }) => (
          <NavLink key={to} to={to} end={to === "/"}
            className={({ isActive }) =>
              `flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all ${
                isActive
                  ? "bg-[#F5A623] text-[#0D1B2A]"
                  : "text-white/60 hover:text-white hover:bg-white/8"
              }`
            }>
            <Icon size={17} />
            <span className="flex-1">{label}</span>
            {badge > 0 && (
              <span className="bg-red-500 text-white text-xs font-bold rounded-full w-5 h-5 flex items-center justify-center leading-none">
                {badge > 9 ? "9+" : badge}
              </span>
            )}
          </NavLink>
        ))}
      </nav>

      <div className="px-4 py-4 border-t border-white/10">
        <div className="text-white/30 text-xs text-center">Sites By Abs © 2026</div>
      </div>
    </aside>
  )
}
