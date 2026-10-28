import { useEffect, useRef, useState, type KeyboardEvent, type PointerEvent } from 'react'
import type { Granularity, VisitorBucket } from './api'
import { formatAxisLabel, formatBucketTitle } from './format'

const SERIES_COLOR = '#2a78d6'
const SERIES_COLOR_ACTIVE = '#1c5cab'
const GRID_COLOR = '#e1e0d9'
const AXIS_COLOR = '#c3c2b7'
const MUTED_INK = '#898781'

const HEIGHT = 280
const MARGIN = { top: 12, right: 8, bottom: 28, left: 40 }
const MAX_BAR_WIDTH = 24
const MIN_LABEL_SPACING = 64

const compact = new Intl.NumberFormat('fr-FR', { notation: 'compact', maximumFractionDigits: 1 })
const full = new Intl.NumberFormat('fr-FR')

// Round the top of the axis to a clean whole number (1, 2, 5 x 10^n) and give ~4
// ticks. Visitors are people, so the step never drops below 1.
function niceScale(max: number): { top: number; ticks: number[] } {
  const rough = Math.max(max, 1) / 4
  const magnitude = 10 ** Math.floor(Math.log10(rough))
  const step = Math.max(1, ([1, 2, 5, 10].find((m) => m * magnitude >= rough) ?? 10) * magnitude)
  const count = Math.ceil(Math.max(max, 1) / step)
  return { top: step * count, ticks: Array.from({ length: count + 1 }, (_, i) => i * step) }
}

// Rounded 4px at the data end (top), square at the baseline.
function barPath(x: number, y: number, width: number, height: number): string {
  const r = Math.min(4, width / 2, height)
  return `M${x},${y + height} V${y + r} Q${x},${y} ${x + r},${y} H${x + width - r} Q${x + width},${y} ${x + width},${y + r} V${y + height} Z`
}

function useWidth<T extends HTMLElement>() {
  const ref = useRef<T>(null)
  const [width, setWidth] = useState(0)

  useEffect(() => {
    const element = ref.current
    if (!element) {
      return
    }
    const observer = new ResizeObserver(([entry]) => setWidth(entry.contentRect.width))
    observer.observe(element)
    return () => observer.disconnect()
  }, [])

  return { ref, width }
}

interface VisitorsChartProps {
  series: VisitorBucket[]
  granularity: Granularity
}

export function VisitorsChart({ series, granularity }: VisitorsChartProps) {
  const { ref, width } = useWidth<HTMLDivElement>()
  const [active, setActive] = useState<number | null>(null)

  const plotWidth = Math.max(width - MARGIN.left - MARGIN.right, 0)
  const plotHeight = HEIGHT - MARGIN.top - MARGIN.bottom
  const slot = series.length > 0 ? plotWidth / series.length : 0
  const gap = Math.min(2, slot * 0.3)
  const barWidth = Math.max(Math.min(MAX_BAR_WIDTH, slot - gap), 1)

  const { top, ticks } = niceScale(Math.max(0, ...series.map((b) => b.visitors)))
  const yFor = (value: number) => MARGIN.top + plotHeight - (value / top) * plotHeight

  const labelEvery = slot > 0 ? Math.max(1, Math.ceil(MIN_LABEL_SPACING / slot)) : 1
  const activeBucket = active !== null ? series[active] : null

  function indexAt(event: PointerEvent<SVGRectElement>): number | null {
    if (slot === 0) {
      return null
    }
    const rect = event.currentTarget.getBoundingClientRect()
    return Math.min(series.length - 1, Math.max(0, Math.floor((event.clientX - rect.left) / slot)))
  }

  function handleKeyDown(event: KeyboardEvent<HTMLDivElement>) {
    if (series.length === 0) {
      return
    }
    if (event.key === 'ArrowRight') {
      setActive((current) => (current === null ? 0 : Math.min(series.length - 1, current + 1)))
    } else if (event.key === 'ArrowLeft') {
      setActive((current) => (current === null ? series.length - 1 : Math.max(0, current - 1)))
    } else if (event.key === 'Escape') {
      setActive(null)
    } else {
      return
    }
    event.preventDefault()
  }

  const tooltipX = active !== null ? MARGIN.left + slot * active + slot / 2 : 0
  const flipTooltip = tooltipX > width / 2

  return (
    <div
      ref={ref}
      className="relative outline-none focus-visible:ring-2 focus-visible:ring-indigo-300"
      tabIndex={0}
      onKeyDown={handleKeyDown}
      onBlur={() => setActive(null)}
      aria-label="Visiteurs uniques par période. Utilisez les flèches gauche et droite pour parcourir les valeurs, ou affichez la vue tableau."
    >
      {width > 0 && (
        <svg width={width} height={HEIGHT} role="img" aria-hidden="true">
          {ticks.map((tick) => (
            <g key={tick}>
              <line
                x1={MARGIN.left}
                x2={width - MARGIN.right}
                y1={yFor(tick)}
                y2={yFor(tick)}
                stroke={tick === 0 ? AXIS_COLOR : GRID_COLOR}
                strokeWidth={1}
              />
              <text x={MARGIN.left - 8} y={yFor(tick)} dy="0.32em" textAnchor="end" fontSize={11} fill={MUTED_INK}>
                {compact.format(tick)}
              </text>
            </g>
          ))}

          {series.map((bucket, index) => {
            const x = MARGIN.left + slot * index + (slot - barWidth) / 2
            const barHeight = plotHeight - (yFor(bucket.visitors) - MARGIN.top)
            return (
              <g key={bucket.start}>
                {bucket.visitors > 0 && (
                  <path
                    d={barPath(x, yFor(bucket.visitors), barWidth, barHeight)}
                    fill={index === active ? SERIES_COLOR_ACTIVE : SERIES_COLOR}
                  />
                )}
                {index % labelEvery === 0 && (
                  <text
                    x={MARGIN.left + slot * index + slot / 2}
                    y={HEIGHT - MARGIN.bottom + 18}
                    textAnchor="middle"
                    fontSize={11}
                    fill={MUTED_INK}
                  >
                    {formatAxisLabel(bucket.start, granularity)}
                  </text>
                )}
              </g>
            )
          })}

          {/* One hit area spanning the whole plot: the pointer only has to be on the right column, never on the bar itself. */}
          <rect
            x={MARGIN.left}
            y={MARGIN.top}
            width={plotWidth}
            height={plotHeight}
            fill="transparent"
            onPointerMove={(event) => setActive(indexAt(event))}
            onPointerLeave={() => setActive(null)}
          />
        </svg>
      )}

      {activeBucket && (
        <div
          role="status"
          className="pointer-events-none absolute z-10 w-max max-w-56 rounded-md border border-gray-200 bg-white px-3 py-2 text-xs shadow-md"
          style={{
            top: MARGIN.top,
            left: tooltipX,
            transform: `translateX(${flipTooltip ? 'calc(-100% - 12px)' : '12px'})`,
          }}
        >
          <p className="text-gray-500">{formatBucketTitle(activeBucket.start, granularity)}</p>
          <p className="mt-1 flex items-center gap-2 text-gray-900">
            <span className="inline-block h-0.5 w-3 rounded" style={{ background: SERIES_COLOR }} />
            <span className="text-sm font-semibold">{full.format(activeBucket.visitors)}</span>
            <span className="text-gray-500">visiteurs uniques</span>
          </p>
          <p className="mt-0.5 pl-5 text-gray-500">
            dont <span className="font-medium text-gray-700">{full.format(activeBucket.users)}</span>{' '}
            {activeBucket.users > 1 ? 'connectés' : 'connecté'}
          </p>
        </div>
      )}
    </div>
  )
}

export function VisitorsTable({ series, granularity }: VisitorsChartProps) {
  return (
    <div className="max-h-80 overflow-auto rounded-md border border-gray-200">
      <table className="w-full text-left text-sm">
        <thead className="sticky top-0 bg-gray-50 text-xs text-gray-500">
          <tr>
            <th className="px-4 py-2 font-medium">Période</th>
            <th className="px-4 py-2 text-right font-medium">Visiteurs uniques</th>
            <th className="px-4 py-2 text-right font-medium">Dont connectés</th>
          </tr>
        </thead>
        <tbody className="divide-y divide-gray-100">
          {series.map((bucket) => (
            <tr key={bucket.start}>
              <td className="px-4 py-2 text-gray-700">{formatBucketTitle(bucket.start, granularity)}</td>
              <td className="px-4 py-2 text-right tabular-nums text-gray-900">{full.format(bucket.visitors)}</td>
              <td className="px-4 py-2 text-right tabular-nums text-gray-600">{full.format(bucket.users)}</td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  )
}
