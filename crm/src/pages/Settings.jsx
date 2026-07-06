import { useState } from "react"
import { getFollowUpSettings, saveFollowUpSettings } from "../lib/utils"
import { Save, CheckCircle, Clock, Zap } from "lucide-react"

const ACCOUNTS = [
  { email: "sitesbyabs@gmail.com",     start: "2026-06-03" },
  { email: "pagesforlocals@gmail.com", start: "2026-06-03" },
]

export default function Settings() {
  const [settings, setSettings] = useState(getFollowUpSettings())
  const [saved, setSaved]       = useState(false)

  function handleSave() {
    saveFollowUpSettings(settings)
    setSaved(true)
    setTimeout(() => setSaved(false), 2500)
  }

  return (
    <div className="max-w-2xl">
      <div className="mb-6">
        <h1 className="text-2xl font-bold font-heading text-gray-900">Settings</h1>
        <p className="text-sm text-gray-500 mt-1">Configure follow-up timing and account details</p>
      </div>

      {/* Follow-up schedule */}
      <div className="bg-white rounded-xl border border-gray-100 shadow-sm p-6 mb-5">
        <div className="flex items-center gap-2 mb-1">
          <Clock size={16} className="text-[#F5A623]" />
          <h2 className="font-semibold font-heading text-gray-900">Follow-up Schedule</h2>
        </div>
        <p className="text-sm text-gray-500 mb-5">
          Based on when an email was sent, the system will show reminders in the Follow-Up tracker and dashboard badge.
        </p>

        <div className="space-y-4">
          <div className="flex items-center gap-4">
            <div className="flex-1">
              <label className="block text-sm font-medium text-gray-700 mb-1">Follow-up 1 — after how many days?</label>
              <p className="text-xs text-gray-400 mb-2">First nudge if no reply received</p>
              <div className="flex items-center gap-2">
                <input
                  type="number" min="1" max="30"
                  value={settings.followup1Days}
                  onChange={e => setSettings(p => ({ ...p, followup1Days: Number(e.target.value) }))}
                  className="w-24 border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-[#F5A623]"
                />
                <span className="text-sm text-gray-500">days after sending</span>
              </div>
            </div>
          </div>

          <div className="border-t border-gray-100 pt-4">
            <label className="block text-sm font-medium text-gray-700 mb-1">Follow-up 2 — after how many days?</label>
            <p className="text-xs text-gray-400 mb-2">Second nudge if still no reply</p>
            <div className="flex items-center gap-2">
              <input
                type="number" min="1" max="60"
                value={settings.followup2Days}
                onChange={e => setSettings(p => ({ ...p, followup2Days: Number(e.target.value) }))}
                className="w-24 border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-[#F5A623]"
              />
              <span className="text-sm text-gray-500">days after sending</span>
            </div>
          </div>

          <div className="bg-blue-50 border border-blue-100 rounded-lg p-3 text-xs text-blue-700 mt-2">
            With these settings: Follow-up 1 at day {settings.followup1Days}, Follow-up 2 at day {settings.followup2Days}.
            The dashboard badge and Follow-Up page will highlight leads that have passed day {settings.followup1Days} with no reply.
          </div>
        </div>

        <div className="mt-5 flex items-center gap-3">
          <button onClick={handleSave}
            className="flex items-center gap-2 bg-[#0D1B2A] text-white px-5 py-2.5 rounded-lg text-sm font-semibold hover:bg-[#162336] transition-colors">
            {saved ? <CheckCircle size={14} /> : <Save size={14} />}
            {saved ? "Saved!" : "Save Settings"}
          </button>
          {saved && <span className="text-sm text-green-600 font-medium">Settings saved locally</span>}
        </div>
      </div>

      {/* Accounts info */}
      <div className="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
        <div className="flex items-center gap-2 mb-4">
          <Zap size={16} className="text-[#F5A623]" />
          <h2 className="font-semibold font-heading text-gray-900">Sending Accounts</h2>
        </div>
        <div className="space-y-3">
          {ACCOUNTS.map(acc => (
            <div key={acc.email} className="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
              <div>
                <div className="text-sm font-medium text-gray-900">{acc.email}</div>
                <div className="text-xs text-gray-400">Started {new Date(acc.start).toLocaleDateString("en-GB", { day:"numeric", month:"short", year:"numeric" })}</div>
              </div>
              <div className="w-2.5 h-2.5 rounded-full bg-green-400"></div>
            </div>
          ))}
        </div>
        <p className="text-xs text-gray-400 mt-3">To add or modify accounts, update the ACCOUNTS array in <code className="bg-gray-100 px-1 py-0.5 rounded">outreach/accounts.py</code></p>
      </div>
    </div>
  )
}
