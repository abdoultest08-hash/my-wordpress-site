import { useEffect, useState, useRef } from "react"
import { supabase } from "../lib/supabase"
import { STATUS_CONFIG, effectiveStatus, fmtDate, ALL_STAGES } from "../lib/utils"
import { useNavigate } from "react-router-dom"
import { Upload, Download, Search, Filter, Plus, CheckSquare, Square, ChevronDown } from "lucide-react"
import * as XLSX from "xlsx"

const STATUS_OPTS = ["all", ...ALL_STAGES]

export default function Leads() {
  const [leads, setLeads]       = useState([])
  const [search, setSearch]     = useState("")
  const [statusFilter, setStatusFilter] = useState("all")
  const [industryFilter, setIndustryFilter] = useState("all")
  const [accountFilter, setAccountFilter]   = useState("all")
  const [selected, setSelected] = useState(new Set())
  const [bulkStatus, setBulkStatus] = useState("")
  const [importing, setImporting] = useState(false)
  const [importMsg, setImportMsg] = useState("")
  const [showBulk, setShowBulk] = useState(false)
  const fileRef = useRef()
  const navigate = useNavigate()

  useEffect(() => { loadLeads() }, [])

  async function loadLeads() {
    const { data } = await supabase.from("leads").select("*").order("created_at", { ascending: false })
    setLeads(data ?? [])
  }

  const industries = ["all", ...Array.from(new Set(leads.map(l => l.industry).filter(Boolean))).sort()]
  const accounts   = ["all", ...Array.from(new Set(leads.map(l => l.email_sent_from).filter(Boolean))).sort()]

  const filtered = leads.filter(l => {
    const eff = effectiveStatus(l)
    const matchSearch  = !search || [l.business_name, l.owner_name, l.city, l.email, l.industry]
      .some(f => f?.toLowerCase().includes(search.toLowerCase()))
    const matchStatus  = statusFilter === "all" || eff === statusFilter
    const matchIndustry = industryFilter === "all" || l.industry === industryFilter
    const matchAccount  = accountFilter === "all" || l.email_sent_from === accountFilter
    return matchSearch && matchStatus && matchIndustry && matchAccount
  })

  function toggleSelect(id) {
    setSelected(prev => {
      const next = new Set(prev)
      next.has(id) ? next.delete(id) : next.add(id)
      return next
    })
  }
  function toggleAll() {
    if (selected.size === filtered.length) setSelected(new Set())
    else setSelected(new Set(filtered.map(l => l.id)))
  }

  async function applyBulkStatus() {
    if (!bulkStatus || selected.size === 0) return
    const ids = Array.from(selected)
    const updates = { status: bulkStatus }
    if (bulkStatus === "email_sent") updates.email_sent_at = new Date().toISOString()
    await supabase.from("leads").update(updates).in("id", ids)
    setSelected(new Set())
    setBulkStatus("")
    setShowBulk(false)
    loadLeads()
  }

  async function exportExcel() {
    const src = selected.size > 0 ? filtered.filter(l => selected.has(l.id)) : filtered
    if (!src.length) return alert("Nothing to export.")
    const rows = src.map(l => ({
      "Business Name":   l.business_name,
      "Owner":           l.owner_name,
      "Industry":        l.industry,
      "City":            l.city,
      "State":           l.state,
      "Email":           l.email,
      "Phone":           l.phone,
      "Status":          effectiveStatus(l),
      "Draft Account":   l.email_sent_from || "",
      "Copy Version":    l.copy_version || "",
      "Pitch Type":      l.pitch_type || "",
      "Deal Value (£)":  l.deal_value || 0,
      "Notes":           l.notes || "",
      "Email Sent At":   l.email_sent_at ? new Date(l.email_sent_at).toLocaleString() : "",
      "Replied At":      l.replied_at    ? new Date(l.replied_at).toLocaleString()    : "",
      "Added":           l.created_at    ? new Date(l.created_at).toLocaleString()    : "",
    }))
    const ws = XLSX.utils.json_to_sheet(rows)
    const wb = XLSX.utils.book_new()
    XLSX.utils.book_append_sheet(wb, ws, "Leads")
    XLSX.writeFile(wb, `leads-export-${new Date().toISOString().split("T")[0]}.xlsx`)
  }

  async function importExcel(e) {
    const file = e.target.files[0]
    if (!file) return
    setImporting(true); setImportMsg("")
    try {
      const buf  = await file.arrayBuffer()
      const wb   = XLSX.read(buf)
      const rows = XLSX.utils.sheet_to_json(wb.Sheets[wb.SheetNames[0]])
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
        copy_version:  r.copy_version  || r["Copy Version"]  || "v1",
        pitch_type:    r.pitch_type    || r["Pitch Type"]    || "new",
        status: "pending",
      })).filter(r => r.email && r.business_name)
      const { error } = await supabase.from("leads").upsert(mapped, { onConflict: "email" })
      if (error) throw error
      setImportMsg(`✅ ${mapped.length} leads imported`)
      loadLeads()
    } catch (err) {
      setImportMsg(`❌ ${err.message}`)
    }
    setImporting(false)
  }

  const allSelected = filtered.length > 0 && selected.size === filtered.length

  return (
    <div>
      <div className="flex items-center justify-between mb-6">
        <div>
          <h1 className="text-2xl font-bold font-heading text-gray-900">Leads</h1>
          <p className="text-sm text-gray-500 mt-1">{leads.length} total · {filtered.length} shown{selected.size > 0 ? ` · ${selected.size} selected` : ""}</p>
        </div>
        <div className="flex items-center gap-2 flex-wrap justify-end">
          {importMsg && <span className="text-sm">{importMsg}</span>}
          {selected.size > 0 && (
            <div className="flex items-center gap-2">
              <select value={bulkStatus} onChange={e => setBulkStatus(e.target.value)}
                className="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-[#F5A623]">
                <option value="">Change status…</option>
                {ALL_STAGES.filter(s => s !== "draft_ready").map(s => (
                  <option key={s} value={s}>{STATUS_CONFIG[s]?.label ?? s}</option>
                ))}
              </select>
              <button onClick={applyBulkStatus} disabled={!bulkStatus}
                className="bg-[#F5A623] text-[#0D1B2A] px-4 py-2 rounded-lg text-sm font-semibold disabled:opacity-40">
                Apply
              </button>
            </div>
          )}
          <button onClick={exportExcel}
            className="flex items-center gap-2 bg-white border border-gray-200 text-gray-700 px-3 py-2 rounded-lg text-sm font-semibold hover:bg-gray-50">
            <Download size={14} /> {selected.size > 0 ? `Export (${selected.size})` : "Export"}
          </button>
          <button onClick={() => fileRef.current?.click()} disabled={importing}
            className="flex items-center gap-2 bg-white border border-gray-200 text-gray-700 px-3 py-2 rounded-lg text-sm font-semibold hover:bg-gray-50">
            <Upload size={14} /> {importing ? "Importing…" : "Import"}
          </button>
          <button onClick={() => navigate("/leads/new")}
            className="flex items-center gap-2 bg-[#0D1B2A] text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-[#162336]">
            <Plus size={14} /> Add Lead
          </button>
          <input ref={fileRef} type="file" accept=".xlsx,.xls" className="hidden" onChange={importExcel} />
        </div>
      </div>

      {/* Filters */}
      <div className="bg-white rounded-xl border border-gray-100 shadow-sm mb-4 p-4 flex items-center gap-3 flex-wrap">
        <div className="relative flex-1 min-w-48">
          <Search size={14} className="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" />
          <input value={search} onChange={e => setSearch(e.target.value)}
            placeholder="Search name, email, city, industry…"
            className="w-full pl-9 pr-4 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:border-[#F5A623]" />
        </div>
        <select value={statusFilter} onChange={e => setStatusFilter(e.target.value)}
          className="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-[#F5A623]">
          {STATUS_OPTS.map(s => <option key={s} value={s}>{s === "all" ? "All statuses" : (STATUS_CONFIG[s]?.label ?? s)}</option>)}
        </select>
        <select value={industryFilter} onChange={e => setIndustryFilter(e.target.value)}
          className="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-[#F5A623]">
          {industries.map(i => <option key={i} value={i}>{i === "all" ? "All industries" : i}</option>)}
        </select>
        <select value={accountFilter} onChange={e => setAccountFilter(e.target.value)}
          className="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-[#F5A623]">
          {accounts.map(a => <option key={a} value={a}>{a === "all" ? "All accounts" : a.split("@")[0]}</option>)}
        </select>
      </div>

      {/* Table */}
      <div className="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <table className="w-full text-sm">
          <thead>
            <tr className="border-b border-gray-100 bg-gray-50">
              <th className="px-4 py-3 w-10">
                <button onClick={toggleAll} className="text-gray-400 hover:text-gray-700">
                  {allSelected ? <CheckSquare size={16} /> : <Square size={16} />}
                </button>
              </th>
              <th className="text-left px-4 py-3 font-semibold text-gray-500 text-xs uppercase tracking-wide">Business</th>
              <th className="text-left px-4 py-3 font-semibold text-gray-500 text-xs uppercase tracking-wide hidden md:table-cell">Industry</th>
              <th className="text-left px-4 py-3 font-semibold text-gray-500 text-xs uppercase tracking-wide hidden lg:table-cell">City</th>
              <th className="text-left px-4 py-3 font-semibold text-gray-500 text-xs uppercase tracking-wide">Status</th>
              <th className="text-left px-4 py-3 font-semibold text-gray-500 text-xs uppercase tracking-wide hidden xl:table-cell">Account</th>
              <th className="text-left px-4 py-3 font-semibold text-gray-500 text-xs uppercase tracking-wide hidden xl:table-cell">Copy</th>
              <th className="text-left px-4 py-3 font-semibold text-gray-500 text-xs uppercase tracking-wide hidden lg:table-cell">Sent</th>
            </tr>
          </thead>
          <tbody className="divide-y divide-gray-50">
            {filtered.length === 0 && (
              <tr><td colSpan={8} className="text-center py-12 text-gray-400">No leads match your filters</td></tr>
            )}
            {filtered.map(lead => {
              const eff = effectiveStatus(lead)
              const cfg = STATUS_CONFIG[eff] ?? STATUS_CONFIG.pending
              const isSelected = selected.has(lead.id)
              return (
                <tr key={lead.id}
                  className={`hover:bg-gray-50 cursor-pointer transition-colors ${isSelected ? "bg-blue-50" : ""}`}>
                  <td className="px-4 py-3" onClick={e => { e.stopPropagation(); toggleSelect(lead.id) }}>
                    {isSelected ? <CheckSquare size={16} className="text-[#F5A623]" /> : <Square size={16} className="text-gray-300" />}
                  </td>
                  <td className="px-4 py-3" onClick={() => navigate(`/leads/${lead.id}`)}>
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
                  <td className="px-4 py-3 text-gray-500 hidden md:table-cell" onClick={() => navigate(`/leads/${lead.id}`)}>{lead.industry}</td>
                  <td className="px-4 py-3 text-gray-500 hidden lg:table-cell" onClick={() => navigate(`/leads/${lead.id}`)}>{lead.city}</td>
                  <td className="px-4 py-3" onClick={() => navigate(`/leads/${lead.id}`)}>
                    <span className={`inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium ${cfg.color}`}>
                      <span className={`w-1.5 h-1.5 rounded-full ${cfg.dot}`}></span>
                      {cfg.label}
                    </span>
                  </td>
                  <td className="px-4 py-3 hidden xl:table-cell text-xs text-gray-500" onClick={() => navigate(`/leads/${lead.id}`)}>
                    {lead.email_sent_from ? lead.email_sent_from.split("@")[0] : "—"}
                  </td>
                  <td className="px-4 py-3 hidden xl:table-cell" onClick={() => navigate(`/leads/${lead.id}`)}>
                    {lead.copy_version && <span className="px-2 py-0.5 rounded text-xs font-mono bg-gray-100 text-gray-600">{lead.copy_version}</span>}
                    {lead.pitch_type && <span className={`ml-1 px-2 py-0.5 rounded text-xs font-medium ${lead.pitch_type === "new" ? "bg-purple-100 text-purple-600" : "bg-blue-100 text-blue-600"}`}>{lead.pitch_type}</span>}
                  </td>
                  <td className="px-4 py-3 text-gray-400 text-xs hidden lg:table-cell" onClick={() => navigate(`/leads/${lead.id}`)}>{fmtDate(lead.email_sent_at)}</td>
                </tr>
              )
            })}
          </tbody>
        </table>
      </div>
    </div>
  )
}
