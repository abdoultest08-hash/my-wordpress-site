import { BrowserRouter, Routes, Route } from "react-router-dom"
import Sidebar     from "./components/Sidebar"
import Dashboard   from "./pages/Dashboard"
import Pipeline    from "./pages/Pipeline"
import Leads       from "./pages/Leads"
import LeadDetail  from "./pages/LeadDetail"
import KPIs        from "./pages/KPIs"
import Calculator  from "./pages/Calculator"
import FollowUp    from "./pages/FollowUp"

export default function App() {
  return (
    <BrowserRouter>
      <div className="flex min-h-screen bg-gray-50">
        <Sidebar />
        <main className="flex-1 ml-60 p-6 min-w-0">
          <Routes>
            <Route path="/"            element={<Dashboard />} />
            <Route path="/pipeline"    element={<Pipeline />} />
            <Route path="/leads"       element={<Leads />} />
            <Route path="/leads/:id"   element={<LeadDetail />} />
            <Route path="/kpis"        element={<KPIs />} />
            <Route path="/calculator"  element={<Calculator />} />
            <Route path="/followup"    element={<FollowUp />} />
          </Routes>
        </main>
      </div>
    </BrowserRouter>
  )
}
