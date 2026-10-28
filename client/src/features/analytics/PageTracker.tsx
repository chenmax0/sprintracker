import { useEffect } from 'react'
import { useLocation } from 'react-router'
import { startHeartbeat, trackVisit } from './tracking'

export function PageTracker() {
  const { pathname } = useLocation()

  useEffect(() => {
    trackVisit()
  }, [pathname])

  useEffect(() => startHeartbeat(), [])

  return null
}
