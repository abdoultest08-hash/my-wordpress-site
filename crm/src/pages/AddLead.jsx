import { useState } from "react"
import { useNavigate } from "react-router-dom"
import { supabase } from "../lib/supabase"
import { ArrowLeft, Save } from "lucide-react"

const FIELDS = [
  { key: "business_name", label: "Business Name *", required: true },
  { key: "owner_name",    label: "Owner Name" },
  { key: "email",         label: "Email *", required: true, type: "email" },
  { key: "phone",         label: "Phone" },
  { key: "industry",      label: "Industry" },
  { key: "city",          label: "City" },
  { key: "state",         label: "State" },
  { key: "gmb_url",       label: "Google Business URL" },
  { key: "logo_url",      label: "Logo URL" },
]

export default function AddLead() {
  const navigate = useNavigate()
  const [form, setForm]     = useState({ pitch_type: "new", copy_version: "v1" })
  const [saving, setSaving] = useState(false)
  const [error, setError]   = useState("")

  async function handleSave() {
    if (!form.business_name || !form.email) { setError("Business name and email are required."); return }
    setSaving(true); setError("")
    const { data, error: err } = await supabase.from("leads").insert([{ ...form, status: "pending" }]).select().single()
    if (err) { setError(err.message); setSaving(false); return }
    navigate(`/leads/${data.id}`)
  }

  return (
    <div className="max-w-2xl">
      <button onClick={() => navigate(-1)} className="flex items-center gap-2 text-sm text-gray-500 hover:text-gray-900 mb-5 transition-colors">
        <ArrowLeft size={15} /> Back
      </button>

      <div className="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
        <h1 className="text-xl font-bold font-heading text-gray-900 mb-5">Add Lead Manually</h1>

        <div className="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
          {FIELDS.map(({ key, label, required, type }) => (
            <div key={key} className={key === "gmb_url" || key === "logo_url" ? "md:col-span-2" : ""}>
              <label className="block text-xs font-medium text-gray-500 mb-1">{label}</label>
              <input
                type={type || "text"}
                value={form[key] || ""}
                onChange={e => setForm(p => ({ ...p, [key]: e.target.value }))}
                className="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-[#F5A623]"
              />
            </div>
          ))}
        </div>

        <div className="grid grid-cols-2 gap-4 mb-4">
          <div>
            <label className="block text-xs font-medium text-gray-500 mb-1">Pitch Type</label>
            <select value={form.pitch_type} onChange={e => setForm(p => ({ ...p, pitch_type: e.target.value }))}
              className="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-[#F5A623]">
              <option value="new">New (no website)</option>
              <option value="upgrade">Upgrade (has website)</option>
            </select>
          </div>
          <div>
            <label className="block text-xs font-medium text-gray-500 mb-1">Copy Version</label>
            <select value={form.copy_version} onChange={e => setForm(p => ({ ...p, copy_version: e.target.value }))}
              className="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-[#F5A623]">
              <option value="v1">v1</option>
              <option value="v2">v2</option>
              <option value="v3">v3</option>
            </select>
          </div>
        </div>

        <div className="mb-5">
          <label className="block text-xs font-medium text-gray-500 mb-1">Notes</label>
          <textarea value={form.notes || ""} onChange={e => setForm(p => ({ ...p, notes: e.target.value }))}
            rows={3} placeholder="Any context about this lead…"
            className="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-[#F5A623] resize-none" />
        </div>

        {error && <div className="text-red-600 text-sm mb-4">{error}</div>}

        <div className="flex items-center gap-3">
          <button onClick={handleSave} disabled={saving}
            className="flex items-center gap-2 bg-[#0D1B2A] text-white px-5 py-2.5 rounded-lg text-sm font-semibold hover:bg-[#162336] disabled:opacity-50">
            <Save size={14} /> {saving ? "Saving…" : "Save Lead"}
          </button>
          <button onClick={() => navigate(-1)} className="px-5 py-2.5 rounded-lg text-sm border border-gray-200 text-gray-600 hover:bg-gray-50">
            Cancel
          </button>
        </div>
      </div>
    </div>
  )
}
