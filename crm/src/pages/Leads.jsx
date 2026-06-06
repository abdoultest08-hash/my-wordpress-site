import { useEffect, useState, useRef } from "react"
import { supabase } from "../lib/supabase"
import { STATUS_CONFIG, fmtDate } from "../lib/utils"
import { useNavigate } from "react-router-dom"
import { Upload, Download, Search, Filter } from "lucide-react"
import * as XLSX from "xlsx"

export default function Leads() {
  const [leads, setLeads] = useState([])
  const [search, setSearch] = useState("")
  const [filter, setFilter] = useState("all")
  const [importing, setImporting] = useState(false)
  const [importMsg, setImportMsg] = useState("")
  const fileRef = useRef()
  const navigate = useNavigate()

  useEffect(() => { loadLeads() }, [])

  async function loadLeads() {
    const { data } = await supabase.from("leads").select("*").order("created_at", { ascending: false })
    setLeads(data ?? [])
  }

  async function exportExcel() {
    const { data } = await supabase.from("leads").select("*").order("created_at", { ascending: false })
    if (!data?.length) return alert("No leads to export yet.")

    const rows = data.map(l => ({
      "Business Name":  l.business_name,
      "Owner Name":     l.owner_name,
      "Industry":       l.industry,
      "City":           l.city,
      "State":          l.state,
      "Email":          l.email,
      "Phone":          l.phone,
      "Status":         l.status,
      "Deal Value (£)": l.deal_value || 0,
      "Notes":          l.notes,
      "Logo URL":       l.logo_url,
      "GMB URL":        l.gmb_url,
      "Email Sent At":  l.email_sent_at ? new Date(l.email_sent_at).toLocaleString() : "",
      "Replied At":     l.replied_at   ? new Date(l.replied_at).toLocaleString()   : "",
      "Meeting At":     l.meeting_at   ? new Date(l.meeting_at).toLocaleString()   : "",
      "Closed At":      l.closed_at    ? new Date(l.closed_at).toLocaleString()    : "",
      "Added":          l.created_at   ? new Date(l.created_at).toLocaleString()   : "",
    }))

    const ws = XLSX.utils.json_to_sheet(rows)
    const wb = XLSX.utils.book_new()
    XLSX.utils.book_append_sheet(wb, ws, "Leads")
    const date = new Date().toISOString().split("T")[0]
    XLSX.writeFile(wb, `outreach-leads-${date}.xlsx`)
  }

  async function importExcel(e) {
    const file = e.target.files[0]
    if (!file) return
    setImporting(true)
    setImportMsg("")
    try {
      const buf  = await file.arrayBuffer()
      const wb   = XLSX.read(buf)
      const ws   = wb.Sheets[wb.SheetNames[0]]
      const rows = XLSX.utils.sheet_to_json(ws)

      const mapped = rows.map(r => ({
        business_name: r.business_name || r["Business Name"] || "",
        owner_name:    r.owner_name    || r["Owner Name"]    || "",
        industry:      r.industry      || r["Industry"]      || "",
        city:          r.city          || r["City"]          || "",
        state:         r.state         || r["State"]         || "",
        email:         r.email         || r["Email"]         || "",
        phone:         r.phone         || r["Phone"]         || "",
        notes:         r.notes         || r["Notes"]         || "",
        logo_url:      r.logo_url      || r["Logo URL"]      || "",
        gmb_url:       r.gmb_url       || r["GMB URL"]       || "",
        status:        "pending",
      })).filter(r => r.email && r.business_name)

      const { error } = await supabase.from("leads").upsert(mapped, { onConflict: "email" })
      if (error) throw error
      setImportMsg(`✅ ${mapped.length} leads imported`)
      loadLeads()
    } catch (err) {
      setImportMsg(`❌ Error: ${err.message}`)
    }
    setImporting(false)
  }

  const filtered = leads.filter(l => {
    const matchSearch = !search || [l.business_name, l.owner_name, l.city, l.email].some(f => f?.toLowerCase().includes(search.toLowerCase()))
    const matchFilter = filter === "all" || l.status === filter
    return matchSearch && matchFilter
  })

  const STATUS_OPTS = ["all", "pending", "email_sent", "replied", "positive", "meeting_booked", "closed", "not_interested"]

  return (
    <div>
      <div className="flex items-center justify-between mb-6">
        <div>
          <h1 className="text-2xl font-bold font-heading text-gray-900">Leads</h1>
          <p className="text-sm text-gray-500 mt-1">{leads.length} total leads</p>
        </div>
        <div className="flex items-center gap-3">
          {importMsg && <span className="text-sm">{importMsg}</span>}
          <button onClick={exportExcel}
            className="flex items-center gap-2 bg-white border border-gray-200 text-gray-700 px-4 py-2.5 rounded-lg text-sm font-semibold hover:bg-gray-50 transition-colors">
            <Download size={15} />
            Export Excel
          </button>
          <button onClick={() => fileRef.current?.click()}
            disabled={importing}
            className="flex items-center gap-2 bg-[#0D1B2A] text-white px-4 py-2.5 rounded-lg text-sm font-semibold hover:bg-[#162336] transition-colors">
            <Upload size={15} />
            {importing ? "Importing..." : "Import Excel"}
          </button>
          <input ref={fileRef} type="file" accept=".xlsx,.xls" className="hidden" onChange={importExcel} />
        </div>
      </div>

      {/* Filters */}
      <div className="bg-white rounded-xl border border-gray-100 shadow-sm mb-4">
        <div className="p-4 flex items-center gap-3 flex-wrap">
          <div className="relative flex-1 min-w-48">
            <Search size={14} className="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" />
            <input value={search} onChange={e => setSearch(e.target.value)}
              placeholder="Search business, owner, city..."
              className="w-full pl-9 pr-4 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:border-[#F5A623]" />
          </div>
          <div className="flex items-center gap-2 flex-wrap">
            <Filter size={14} className="text-gray-400" />
            {STATUS_OPTS.map(s => (
              <button key={s} onClick={() => setFilter(s)}
                className={`px-3 py-1.5 rounded-full text-xs font-medium transition-all ${
                  filter === s ? "bg-[#0D1B2A] text-white" : "bg-gray-100 text-gray-600 hover:bg-gray-200"
                }`}>
                {s === "all" ? "All" : s.replace(/_/g, " ")}
              </button>
            ))}
          </div>
        </div>
      </div>

      {/* Table */}
      <div className="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <table className="w-full text-sm">
          <thead>
            <tr className="border-b border-gray-100 bg-gray-50">
              <th className="text-left px-6 py-3 font-semibold text-gray-500 text-xs uppercase tracking-wide">Business</th>
              <th className="text-left px-4 py-3 font-semibold text-gray-500 text-xs uppercase tracking-wide hidden md:table-cell">Industry</th>
              <th className="text-left px-4 py-3 font-semibold text-gray-500 text-xs uppercase tracking-wide hidden lg:table-cell">City</th>
              <th className="text-left px-4 py-3 font-semibold text-gray-500 text-xs uppercase tracking-wide">Status</th>
              <th className="text-left px-4 py-3 font-semibold text-gray-500 text-xs uppercase tracking-wide hidden lg:table-cell">Email Sent</th>
              <th className="text-left px-4 py-3 font-semibold text-gray-500 text-xs uppercase tracking-wide hidden xl:table-cell">Added</th>
            </tr>
          </thead>
          <tbody className="divide-y divide-gray-50">
            {filtered.length === 0 && (
              <tr><td colSpan={6} className="text-center py-12 text-gray-400">No leads found</td></tr>
            )}
            {filtered.map(lead => {
              const cfg = STATUS_CONFIG[lead.status] ?? STATUS_CONFIG.pending
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
                  <td className="px-4 py-3">
                    <span className={`inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium ${cfg.color}`}>
                      <span className={`w-1.5 h-1.5 rounded-full ${cfg.dot}`}></span>
                      {cfg.label}
                    </span>
                  </td>
                  <td className="px-4 py-3 text-gray-400 text-xs hidden lg:table-cell">{fmtDate(lead.email_sent_at)}</td>
                  <td className="px-4 py-3 text-gray-400 text-xs hidden xl:table-cell">{fmtDate(lead.created_at)}</td>
                </tr>
              )
            })}
          </tbody>
        </table>
      </div>
    </div>
  )
}
