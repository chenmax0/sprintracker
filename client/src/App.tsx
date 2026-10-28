import { BrowserRouter, Route, Routes } from 'react-router'
import { LoginPage } from './features/auth/LoginPage'
import { RegisterPage } from './features/auth/RegisterPage'
import { RequireAuth } from './features/auth/RequireAuth'
import { AppLayout } from './features/dashboard/AppLayout'
import { DemoPage } from './features/demo/DemoPage'
import { LandingPage } from './features/landing/LandingPage'
import { MyProjectsPage } from './features/projects/MyProjectsPage'
import { ProjectPage } from './features/projects/ProjectPage'
import { TicketPage } from './features/tickets/TicketPage'

function App() {
  return (
    <BrowserRouter>
      <Routes>
        <Route path="/" element={<LandingPage />} />
        <Route path="/demo" element={<DemoPage />} />
        <Route path="/login" element={<LoginPage />} />
        <Route path="/register" element={<RegisterPage />} />
        <Route element={<RequireAuth />}>
          <Route element={<AppLayout />}>
            <Route path="/app" element={<MyProjectsPage />} />
            <Route path="/projects/:projectId" element={<ProjectPage />} />
            <Route path="/tickets/:ticketId" element={<TicketPage />} />
          </Route>
        </Route>
      </Routes>
    </BrowserRouter>
  )
}

export default App
