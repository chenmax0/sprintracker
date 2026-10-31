import { BrowserRouter, Route, Routes } from 'react-router'
import { LoginPage } from './features/auth/LoginPage'
import { RegisterPage } from './features/auth/RegisterPage'
import { RequireAuth } from './features/auth/RequireAuth'
import { PageTracker } from './features/analytics/PageTracker'
import { AdminDashboardPage } from './features/admin/AdminDashboardPage'
import { RequireAdmin } from './features/admin/RequireAdmin'
import { AppLayout } from './features/dashboard/AppLayout'
import { BacklogPage } from './features/backlog/BacklogPage'
import { DemoPage } from './features/demo/DemoPage'
import { LandingPage } from './features/landing/LandingPage'
import { MyProjectsPage } from './features/projects/MyProjectsPage'
import { ProjectPage } from './features/projects/ProjectPage'
import { TicketPage } from './features/tickets/TicketPage'

function App() {
  return (
    <BrowserRouter>
      <PageTracker />
      <Routes>
        <Route path="/" element={<LandingPage />} />
        <Route path="/demo" element={<DemoPage />} />
        <Route path="/login" element={<LoginPage />} />
        <Route path="/register" element={<RegisterPage />} />
        <Route element={<RequireAuth />}>
          <Route element={<AppLayout />}>
            <Route path="/app" element={<MyProjectsPage />} />
            <Route path="/projects/:projectId" element={<ProjectPage />} />
            <Route path="/projects/:projectId/backlog" element={<BacklogPage />} />
            <Route path="/tickets/:ticketId" element={<TicketPage />} />
            <Route element={<RequireAdmin />}>
              <Route path="/admin" element={<AdminDashboardPage />} />
            </Route>
          </Route>
        </Route>
      </Routes>
    </BrowserRouter>
  )
}

export default App
