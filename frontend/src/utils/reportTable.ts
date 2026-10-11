/*
|--------------------------------------------------------------------------
| Report table helpers
|
| Pure functions (no Vue, no API calls) for the Reports page: employee
| search, balance filters, sorting, pagination and quick date ranges.
| Keeping them separate lets every combination be tested on its own.
|--------------------------------------------------------------------------
*/

export type SortDirection = 'asc' | 'desc'
export interface SortState { key: string; dir: SortDirection }
export type SortKind = 'text' | 'number'

export interface CreditFilters {
  search: string
  department: string // '' = all, 'unassigned', or a department id
  status: string // '' = all, 'active', 'inactive'
  balance: string // '' = all, 'with-balance', 'zero', 'no-record'
}

export const emptyCreditFilters = (): CreditFilters => ({
  search: '',
  department: '',
  status: '',
  balance: ''
})

export const hasActiveCreditFilters = (filters: CreditFilters) =>
  Boolean(filters.search.trim() || filters.department || filters.status || filters.balance)

/** Lower-case, comma/space separated words; every word must match a name part. */
export const searchTokens = (text: string): string[] =>
  text.toLowerCase().split(/[\s,]+/).filter(Boolean)

/** Searchable text for an employee: all name parts, in any order. */
export const buildSearchText = (...parts: unknown[]): string =>
  parts.filter((part) => part !== null && part !== undefined && String(part).trim() !== '')
    .map((part) => String(part).toLowerCase()).join(' ')

const thousandths = (value: unknown): number => Math.round(Number(value ?? 0) * 1000)

/**
 * Filters Leave Credits rows. "Remaining days" means the existing rule:
 * vacation + sick + local credits (row.total_available).
 */
export function filterCreditRows<T extends Record<string, any>>(rows: readonly T[], filters: CreditFilters): T[] {
  const tokens = searchTokens(filters.search)
  return rows.filter((row) => {
    if (tokens.length && !tokens.every((token) => String(row.search_text ?? '').includes(token))) return false

    if (filters.department === 'unassigned') {
      if (row.department_id !== null && row.department_id !== undefined) return false
    } else if (filters.department && String(row.department_id ?? '') !== filters.department) {
      return false
    }

    if (filters.status && String(row.employment_status ?? '').trim().toLowerCase() !== filters.status) return false

    const hasRecord = row.has_balance_record !== false
    switch (filters.balance) {
      case 'with-balance':
        return hasRecord && thousandths(row.total_available) > 0
      case 'zero':
        return hasRecord && thousandths(row.total_available) === 0
      case 'no-record':
        return !hasRecord
      default:
        return true
    }
  })
}

const collator = new Intl.Collator('en', { sensitivity: 'base', numeric: true })

/**
 * Returns a sorted copy. Equal values fall back to the tie-break key so ordering is stable
 * and predictable; numeric cells with no value (no balance record) always sort last.
 */
export function sortRows<T extends Record<string, any>>(
  rows: readonly T[],
  sort: SortState | null,
  kinds: Record<string, SortKind>,
  options: { tieBreak?: string; isMissing?: (row: T, key: string) => boolean } = {}
): T[] {
  if (!sort || !kinds[sort.key]) return [...rows]
  const { key, dir } = sort
  const sign = dir === 'asc' ? 1 : -1
  const kind = kinds[key]
  const tieBreak = options.tieBreak
  const missing = (row: T) =>
    options.isMissing?.(row, key) === true || (kind === 'number' && !Number.isFinite(Number(row[key])))

  return [...rows].sort((a, b) => {
    const aMissing = kind === 'number' && missing(a)
    const bMissing = kind === 'number' && missing(b)
    if (aMissing !== bMissing) return aMissing ? 1 : -1 // missing last, whatever the direction
    let result = 0
    if (!aMissing) {
      result = kind === 'number'
        ? thousandths(a[key]) - thousandths(b[key])
        : collator.compare(String(a[key] ?? ''), String(b[key] ?? ''))
    }
    if (result !== 0) return result * sign
    return tieBreak ? collator.compare(String(a[tieBreak] ?? ''), String(b[tieBreak] ?? '')) : 0
  })
}

/** Click cycle for a column header: ascending, then descending, then back to the default order. */
export function nextSort(current: SortState | null, key: string): SortState | null {
  if (!current || current.key !== key) return { key, dir: 'asc' }
  return current.dir === 'asc' ? { key, dir: 'desc' } : null
}

export interface PageResult<T> {
  items: T[]
  page: number
  pageCount: number
  total: number
  from: number // 1-based index of the first visible record, 0 when empty
  to: number
}

/** pageSize 0 means "all records". The requested page is clamped to what exists. */
export function paginate<T>(rows: readonly T[], page: number, pageSize: number): PageResult<T> {
  const total = rows.length
  if (!pageSize || pageSize < 0) {
    return { items: [...rows], page: 1, pageCount: 1, total, from: total ? 1 : 0, to: total }
  }
  const pageCount = Math.max(1, Math.ceil(total / pageSize))
  const current = Math.min(Math.max(1, Math.floor(page) || 1), pageCount)
  const start = (current - 1) * pageSize
  const items = rows.slice(start, start + pageSize)
  return { items, page: current, pageCount, total, from: items.length ? start + 1 : 0, to: start + items.length }
}

const pad = (value: number) => String(value).padStart(2, '0')
export const toLocalDate = (date: Date) => `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}`

/**
 * Whole calendar periods. Leave Summary matches applications whose leave dates OVERLAP the range,
 * so "This Month" must run to the end of the month, otherwise leave scheduled later this month
 * would be missed.
 */
export function quickDateRange(preset: string, now: Date = new Date()): { start: string; end: string } | null {
  const year = now.getFullYear()
  const month = now.getMonth()
  switch (preset) {
    case 'today':
      return { start: toLocalDate(now), end: toLocalDate(now) }
    case 'week': {
      const monday = new Date(year, month, now.getDate() - ((now.getDay() + 6) % 7))
      const sunday = new Date(monday.getFullYear(), monday.getMonth(), monday.getDate() + 6)
      return { start: toLocalDate(monday), end: toLocalDate(sunday) }
    }
    case 'month':
      return { start: toLocalDate(new Date(year, month, 1)), end: toLocalDate(new Date(year, month + 1, 0)) }
    case 'quarter': {
      const first = Math.floor(month / 3) * 3
      return { start: toLocalDate(new Date(year, first, 1)), end: toLocalDate(new Date(year, first + 3, 0)) }
    }
    case 'year':
      return { start: `${year}-01-01`, end: `${year}-12-31` }
    default:
      return null
  }
}

/** The preset a start/end pair corresponds to ('' for all dates or a custom range). */
export function matchQuickRange(start: string, end: string, now: Date = new Date()): string {
  if (!start || !end) return ''
  for (const preset of ['today', 'week', 'month', 'quarter', 'year']) {
    const range = quickDateRange(preset, now)
    if (range && range.start === start && range.end === end) return preset
  }
  return ''
}
