import { useMemo, useState } from 'react'
import { Card } from '../../lib/Card'
import type { Granularity } from './api'
import { browserTimezone, GRANULARITIES, maxDays, RANGE_PRESETS, rangeFor } from './dateRange'
import { useVisitorStats } from './hooks'
import { formatBucketTitle } from './format'
import { VisitorsChart, VisitorsTable } from './VisitorsChart'

const full = new Intl.NumberFormat('fr-FR')

const UNIT_LABEL: Record<Granularity, string> = {
  hour: 'heure',
  day: 'jour',
  week: 'semaine',
  month: 'mois',
}

function segmentClass(selected: boolean, disabled = false): string {
  const base = 'px-3 py-1.5 text-sm font-medium transition-colors first:rounded-l-md last:rounded-r-md'
  if (disabled) {
    return `${base} cursor-not-allowed text-gray-300`
  }
  return selected ? `${base} bg-indigo-600 text-white` : `${base} text-gray-600 hover:bg-gray-100`
}

export function AdminDashboardPage() {
  const [presetId, setPresetId] = useState('30d')
  const [granularity, setGranularity] = useState<Granularity>('day')
  const [view, setView] = useState<'chart' | 'table'>('chart')

  const preset = RANGE_PRESETS.find((p) => p.id === presetId) ?? RANGE_PRESETS[2]
  const timezone = useMemo(() => browserTimezone(), [])
  // Recomputed with the preset only: the window is "N days ending today" at the moment it was picked.
  const { from, to } = useMemo(() => rangeFor(preset.days), [preset.days])

  const { data, isLoading, isError, isFetching } = useVisitorStats({ granularity, from, to, timezone })

  function selectPreset(id: string, days: number) {
    setPresetId(id)
    // A window too wide for the current granularity would be rejected by the API: step up to days.
    if (days > maxDays(granularity)) {
      setGranularity('day')
    }
  }

  const peak = data?.series.reduce((best, bucket) => (bucket.visitors > best.visitors ? bucket : best), {
    start: '',
    visitors: 0,
    users: 0,
  })

  return (
    <div className="mx-auto flex max-w-6xl flex-col gap-6">
      <div>
        <h1 className="text-2xl font-semibold text-gray-900">Administration</h1>
        <p className="mt-1 text-sm text-gray-500">
          Fréquentation du site : chaque visiteur (connecté ou non) est compté une seule fois par période.
        </p>
      </div>

      <div className="flex flex-wrap items-center gap-x-6 gap-y-3">
        <div role="group" aria-label="Période" className="flex divide-x divide-gray-200 rounded-md border border-gray-200 bg-white">
          {RANGE_PRESETS.map((p) => (
            <button
              key={p.id}
              type="button"
              aria-pressed={p.id === presetId}
              onClick={() => selectPreset(p.id, p.days)}
              className={segmentClass(p.id === presetId)}
            >
              {p.label}
            </button>
          ))}
        </div>
        <div role="group" aria-label="Granularité" className="flex divide-x divide-gray-200 rounded-md border border-gray-200 bg-white">
          {GRANULARITIES.map((g) => {
            const disabled = preset.days > maxDays(g.id)
            return (
              <button
                key={g.id}
                type="button"
                disabled={disabled}
                aria-pressed={g.id === granularity}
                title={disabled ? 'Période trop large pour cette granularité' : undefined}
                onClick={() => setGranularity(g.id)}
                className={segmentClass(g.id === granularity, disabled)}
              >
                {g.label}
              </button>
            )
          })}
        </div>
      </div>

      {isError && (
        <Card className="border-red-200 text-sm text-red-700">Impossible de charger les statistiques.</Card>
      )}

      {isLoading && <p className="text-sm text-gray-500">Chargement…</p>}

      {data && (
        <div className={`flex flex-col gap-6 transition-opacity ${isFetching ? 'opacity-60' : ''}`}>
          <div className="grid grid-cols-1 gap-4 md:grid-cols-3">
            <Card className="md:col-span-1">
              <p className="text-sm text-gray-500">Visiteurs uniques</p>
              <p className="mt-2 text-5xl font-semibold text-gray-900">{full.format(data.totals.visitors)}</p>
              <p className="mt-2 text-xs text-gray-500">sur {preset.label === "Aujourd'hui" ? "aujourd'hui" : `les ${preset.label}`}</p>
            </Card>
            <Card>
              <p className="text-sm text-gray-500">Utilisateurs connectés</p>
              <p className="mt-2 text-3xl font-semibold text-gray-900">{full.format(data.totals.users)}</p>
              <p className="mt-2 text-xs text-gray-500">comptes distincts, tous appareils confondus</p>
            </Card>
            <Card>
              <p className="text-sm text-gray-500">Pic de fréquentation</p>
              <p className="mt-2 text-3xl font-semibold text-gray-900">{full.format(peak?.visitors ?? 0)}</p>
              <p className="mt-2 text-xs text-gray-500">
                {peak && peak.visitors > 0 ? formatBucketTitle(peak.start, data.granularity) : 'Aucune visite sur la période'}
              </p>
            </Card>
          </div>

          <Card>
            <div className="mb-4 flex flex-wrap items-center justify-between gap-3">
              <h2 className="text-sm font-semibold text-gray-700">Visiteurs uniques par {UNIT_LABEL[data.granularity]}</h2>
              <div role="group" aria-label="Affichage" className="flex divide-x divide-gray-200 rounded-md border border-gray-200">
                <button type="button" aria-pressed={view === 'chart'} onClick={() => setView('chart')} className={segmentClass(view === 'chart')}>
                  Graphique
                </button>
                <button type="button" aria-pressed={view === 'table'} onClick={() => setView('table')} className={segmentClass(view === 'table')}>
                  Tableau
                </button>
              </div>
            </div>
            {view === 'chart' ? (
              <VisitorsChart series={data.series} granularity={data.granularity} />
            ) : (
              <VisitorsTable series={data.series} granularity={data.granularity} />
            )}
            <p className="mt-3 text-xs text-gray-500">
              Heures et jours calculés dans votre fuseau horaire ({data.timezone}). Le total est dédoublonné sur toute la période : il peut être inférieur à la somme des barres.
            </p>
          </Card>
        </div>
      )}
    </div>
  )
}
