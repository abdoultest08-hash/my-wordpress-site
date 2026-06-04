import { NavLink } from "react-router-dom"
import { LayoutDashboard, Users, Kanban, BarChart3, Settings } from "lucide-react"

const links = [
  { to: "/",         icon: LayoutDashboard, label: "Dashboard"  },
  { to: "/pipeline", icon: Kanban,          label: "Pipeline"   },
  { to: "/leads",    icon: Users,           label: "Leads"      },
  { to: "/kpis",     icon: BarChart3,       label: "KPIs"       },
]

export default function Sidebar() {
  return (
    <aside className="w-60 min-h-screen bg-[#0D1B2A] flex flex-col fixed left-0 top-0 bottom-0 z-30">
      {/* Logo */}
      <div className="px-6 py-5 border-b border-white/10">
        <div className="flex items-center gap-3">
          <div className="w-9 h-9 bg-[#F5A623] rounded-lg flex items-center justify-center font-bold text-[#0D1B2A] text-sm font-heading">AB</div>
          <div>
            <div className="text-white font-semibold text-sm font-heading">Sites By Abs</div>
            <div className="text-white/40 text-xs">Outreach CRM</div>
          </div>
        </div>
      </div>

      {/* Nav */}
      <nav className="flex-1 px-3 py-4 space-y-1">
        {links.map(({ to, icon: Icon, label }) => (
          <NavLink key={to} to={to} end={to === "/"}
            className={({ isActive }) =>
              `flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all ${
                isActive
                  ? "bg-[#F5A623] text-[#0D1B2A]"
                  : "text-white/60 hover:text-white hover:bg-white/8"
              }`
            }>
            <Icon size={17} />
            {label}
          </NavLink>
        ))}
      </nav>

      {/* Footer */}
      <div className="px-4 py-4 border-t border-white/10">
        <div className="text-white/30 text-xs text-center">Sites By Abs © 2026</div>
      </div>
    </aside>
  )
}
