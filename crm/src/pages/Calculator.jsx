import { useState, useMemo } from "react"
import { DollarSign, TrendingUp, Mail, Users, Calendar } from "lucide-react"

function Slider({ label, value, min, max, step, onChange, format }) {
  return (
    <div className="space-y-2">
      <div className="flex justify-between items-center">
        <label className="text-sm font-medium text-gray-700">{label}</label>
        <span className="text-sm font-bold text-[#0D1B2A]">{format ? format(value) : value}</span>
      </div>
      <input type="range" min={min} max={max} step={step} value={value}
        onChange={e => onChange(Number(e.target.value))}
        className="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-[#F5A623]" />
      <div className="flex justify-between text-xs text-gray-400">
        <span>{format ? format(min) : min}</span>
        <span>{format ? format(max) : max}</span>
      </div>
    </div>
  )
}

function ResultCard({ icon: Icon, label, value, sub, highlight }) {
  return (
    <div className={`rounded-xl p-5 border ${highlight ? "bg-[#0D1B2A] border-[#0D1B2A]" : "bg-white border-gray-100 shadow-sm"}`}>
      <div className={`flex items-center gap-2 mb-2 ${highlight ? "text-[#F5A623]" : "text-gray-400"}`}>
        <Icon size={16} />
        <span className={`text-xs font-medium uppercase tracking-wide ${highlight ? "text-[#F5A623]/70" : "text-gray-400"}`}>{label}</span>
      </div>
      <div className={`text-3xl font-black font-heading ${highlight ? "text-white" : "text-gray-900"}`}>{value}</div>
      {sub && <div className={`text-xs mt-1 ${highlight ? "text-white/50" : "text-gray-400"}`}>{sub}</div>}
    </div>
  )
}

const fmtGBP = v => `£${v.toLocaleString()}`
const pct = v => `${v}%`

export default function Calculator() {
  const [target,       setTarget]       = useState(5000)
  const [setupFee,     setSetupFee]     = useState(800)
  const [retainer,     setRetainer]     = useState(150)
  const [retainerMos,  setRetainerMos]  = useState(6)
  const [emailToInterest, setEmailToInterest] = useState(5)
  const [interestToClose, setInterestToClose] = useState(30)
  const [months,       setMonths]       = useState(3)

  const calc = useMemo(() => {
    const clientValue  = setupFee + retainer * retainerMos
    const overallRate  = (emailToInterest / 100) * (interestToClose / 100)
    const clientsNeeded = Math.ceil(target / clientValue)
    const emailsNeeded  = overallRate > 0 ? Math.ceil(clientsNeeded / overallRate) : 0
    const emailsPerMonth = Math.ceil(emailsNeeded / months)
    const emailsPerDay   = Math.ceil(emailsPerMonth / 22) // ~22 working days
    const interestedNeeded = Math.ceil(clientsNeeded / (interestToClose / 100))
    return { clientValue, overallRate, clientsNeeded, emailsNeeded, emailsPerMonth, emailsPerDay, interestedNeeded }
  }, [target, setupFee, retainer, retainerMos, emailToInterest, interestToClose, months])

  return (
    <div>
      <div className="mb-6">
        <h1 className="text-2xl font-bold font-heading text-gray-900">Revenue Calculator</h1>
        <p className="text-sm text-gray-500 mt-1">Reverse-engineer your outreach targets from revenue goals</p>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {/* Inputs */}
        <div className="space-y-5">
          {/* Goal */}
          <div className="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
            <h3 className="font-semibold font-heading text-gray-800 mb-5">Revenue Goal</h3>
            <div className="space-y-6">
              <Slider label="Target Revenue" value={target} min={1000} max={50000} step={500} onChange={setTarget} format={fmtGBP} />
              <Slider label="Time to Hit Target (months)" value={months} min={1} max={12} step={1} onChange={setMonths} />
            </div>
          </div>

          {/* Pricing */}
          <div className="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
            <h3 className="font-semibold font-heading text-gray-800 mb-5">Deal Structure</h3>
            <div className="space-y-6">
              <Slider label="Setup / Build Fee" value={setupFee} min={200} max={5000} step={50} onChange={setSetupFee} format={fmtGBP} />
              <Slider label="Monthly Retainer" value={retainer} min={0} max={500} step={25} onChange={setRetainer} format={fmtGBP} />
              <Slider label="Retainer Months (avg contract)" value={retainerMos} min={1} max={24} step={1} onChange={setRetainerMos} />
            </div>
          </div>

          {/* Conversion */}
          <div className="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
            <h3 className="font-semibold font-heading text-gray-800 mb-5">Conversion Rates</h3>
            <div className="space-y-6">
              <Slider label="Email → Interested" value={emailToInterest} min={1} max={30} step={1} onChange={setEmailToInterest} format={pct} />
              <Slider label="Interested → Closed" value={interestToClose} min={5} max={80} step={5} onChange={setInterestToClose} format={pct} />
            </div>
          </div>
        </div>

        {/* Results */}
        <div className="space-y-4">
          {/* Summary */}
          <div className="bg-[#F5A623]/10 rounded-xl border border-[#F5A623]/30 p-5">
            <div className="flex items-center gap-2 mb-1">
              <TrendingUp size={16} className="text-[#F5A623]" />
              <span className="text-sm font-semibold text-gray-700">Deal value per client</span>
            </div>
            <div className="text-4xl font-black font-heading text-[#0D1B2A]">{fmtGBP(calc.clientValue)}</div>
            <div className="text-xs text-gray-500 mt-1">{fmtGBP(setupFee)} setup + {fmtGBP(retainer)}/mo × {retainerMos} months</div>
          </div>

          <div className="grid grid-cols-2 gap-4">
            <ResultCard icon={Users}    label="Clients Needed"     value={calc.clientsNeeded}   sub={`to hit ${fmtGBP(target)}`} />
            <ResultCard icon={TrendingUp} label="Overall Close Rate" value={`${(calc.overallRate*100).toFixed(1)}%`} sub="email → client" />
          </div>

          <ResultCard icon={Mail}     label="Total Emails to Send"    value={calc.emailsNeeded.toLocaleString()}   sub={`across ${months} months`} highlight />

          <div className="grid grid-cols-2 gap-4">
            <ResultCard icon={Mail}     label="Emails / Month"   value={calc.emailsPerMonth.toLocaleString()} sub="to stay on track" />
            <ResultCard icon={Calendar} label="Emails / Day"     value={calc.emailsPerDay} sub="on working days" />
          </div>

          <ResultCard icon={Users} label="Interested Leads Needed" value={calc.interestedNeeded} sub={`before closing ${calc.clientsNeeded} clients`} />

          {/* Warm-up note */}
          {calc.emailsPerDay > 20 && (
            <div className="bg-amber-50 border border-amber-200 rounded-xl p-4 text-sm text-amber-800">
              <strong>Note:</strong> {calc.emailsPerDay} emails/day requires warm accounts (40/day tier). You're currently limited to 10–40/day per account while warming up.
            </div>
          )}
          {calc.emailsPerDay <= 20 && calc.emailsPerDay > 0 && (
            <div className="bg-emerald-50 border border-emerald-200 rounded-xl p-4 text-sm text-emerald-800">
              <strong>On track:</strong> {calc.emailsPerDay} emails/day is achievable with your current warm-up tier.
            </div>
          )}
        </div>
      </div>
    </div>
  )
}
