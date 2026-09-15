import { BrowserRouter, Route, Routes } from 'react-router'
import { LoginPage } from './features/auth/LoginPage'
import { RegisterPage } from './features/auth/RegisterPage'
import { RequireAuth } from './features/auth/RequireAuth'
import { AppLayout } from './features/dashboard/AppLayout'
import { ProjectPage } from './features/projects/ProjectPage'
import { TeamPage } from './features/teams/TeamPage'
import { TeamsPage } from './features/teams/TeamsPage'
import { TicketPage } from './features/tickets/TicketPage'

function App() {
  return (
    <BrowserRouter>
      <Routes>
        <Route path="/login" element={<LoginPage />} />
        <Route path="/register" element={<RegisterPage />} />
        <Route element={<RequireAuth />}>
          <Route element={<AppLayout />}>
            <Route path="/" element={<TeamsPage />} />
            <Route path="/teams/:teamId" element={<TeamPage />} />
            <Route path="/projects/:projectId" element={<ProjectPage />} />
            <Route path="/tickets/:ticketId" element={<TicketPage />} />
          </Route>
        </Route>
      </Routes>
    </BrowserRouter>
  )
}

export default App
