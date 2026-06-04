import { useEffect, useState } from "react"
import { supabase } from "../lib/supabase"
import { BarChart, Bar, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer, LineChart, Line, PieChart, Pie, Cell } from "recharts"
import { fmtCurrency } from "../lib/utils"

export default function KPIs() {
  const [data, setData] = useState({ funnel: [], daily: [], byIndustry: [] })

  useEffect(() => {
    async function load() {
      const { data: leads } = await supabase.from("leads").select("status, industry, deal_value, email_sent_at, created_at")
      const { data: counts } = await supabase.from("send_counts").select("account, send_date, count").order("send_date")

      // Funnel
      const statuses = ["email_sent","replied","positive","meeting_booked","closed"]
      const labels   = ["Sent","Replied","Positive","Meeting","Closed"]
      const funnel = statuses.map((s, i) => ({
        name:  labels[i],
        value: leads?.filter(l => [s, ...statuses.slice(i+1)].includes(l.status)).length ?? 0,
      }))

      // Daily sends (last 14 days)
      const dailyMap = {}
      counts?.forEach(c => {
        dailyMap[c.send_date] = (dailyMap[c.send_date] || 0) + c.count
      })
      const daily = Object.entries(dailyMap).slice(-14).map(([date, count]) => ({
        date: new Date(date).toLocaleDateString("en-GB", { day:"numeric", month:"short" }),
        count,
      }))

      // By industry
      const indMap = {}
      leads?.forEach(l => { if (l.industry) indMap[l.industry] = (indMap[l.industry] || 0) + 1 })
      const byIndustry = Object.entries(indMap).sort((a,b) => b[1]-a[1]).slice(0,8).map(([name, value]) => ({ name, value }))

      const revenue = leads?.filter(l => l.status === "closed").reduce((a, l) => a + (l.deal_value || 0), 0) ?? 0

      setData({ funnel, daily, byIndustry, revenue })
    }
    load()
  }, [])

  const COLORS = ["#F5A623","#0D1B2A","#2B78BF","#27AE60","#8E44AD"]

  return (
    <div>
      <div className="mb-6">
        <h1 className="text-2xl font-bold font-heading text-gray-900">KPI Tracker</h1>
        <p className="text-sm text-gray-500 mt-1">Performance metrics across all accounts</p>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-2 gap-5">
        {/* Funnel */}
        <div className="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
          <h3 className="font-semibold font-heading text-gray-800 mb-4">Sales Funnel</h3>
          <ResponsiveContainer width="100%" height={240}>
            <BarChart data={data.funnel} layout="vertical" margin={{ left: 10 }}>
              <CartesianGrid strokeDasharray="3 3" horizontal={false} />
              <XAxis type="number" tick={{ fontSize: 11 }} />
              <YAxis type="category" dataKey="name" tick={{ fontSize: 12 }} width={60} />
              <Tooltip />
              <Bar dataKey="value" fill="#F5A623" radius={[0,4,4,0]} />
            </BarChart>
          </ResponsiveContainer>
        </div>

        {/* Daily Sends */}
        <div className="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
          <h3 className="font-semibold font-heading text-gray-800 mb-4">Daily Sends (last 14 days)</h3>
          <ResponsiveContainer width="100%" height={240}>
            <LineChart data={data.daily}>
              <CartesianGrid strokeDasharray="3 3" />
              <XAxis dataKey="date" tick={{ fontSize: 10 }} />
              <YAxis tick={{ fontSize: 11 }} />
              <Tooltip />
              <Line type="monotone" dataKey="count" stroke="#0D1B2A" strokeWidth={2} dot={{ fill: "#F5A623", r: 4 }} />
            </LineChart>
          </ResponsiveContainer>
        </div>

        {/* By Industry */}
        <div className="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
          <h3 className="font-semibold font-heading text-gray-800 mb-4">Leads by Industry</h3>
          <ResponsiveContainer width="100%" height={240}>
            <PieChart>
              <Pie data={data.byIndustry} dataKey="value" nameKey="name" cx="50%" cy="50%" outerRadius={90} label={({ name, percent }) => `${name} ${(percent*100).toFixed(0)}%`} labelLine={false}>
                {data.byIndustry?.map((_, i) => <Cell key={i} fill={COLORS[i % COLORS.length]} />)}
              </Pie>
              <Tooltip />
            </PieChart>
          </ResponsiveContainer>
        </div>

        {/* Revenue summary */}
        <div className="bg-white rounded-xl border border-gray-100 shadow-sm p-5 flex flex-col justify-center items-center">
          <div className="text-gray-500 text-sm mb-2">Total Revenue Closed</div>
          <div className="text-5xl font-black font-heading text-[#0D1B2A]">{fmtCurrency(data.revenue)}</div>
          <div className="text-gray-400 text-sm mt-2">from all closed deals</div>
        </div>
      </div>
    </div>
  )
}
