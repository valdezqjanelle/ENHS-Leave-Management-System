/*
|--------------------------------------------------------------------------
| Report export helpers
|
| Pure functions (no Vue, no API calls) that turn the report currently on
| screen into administrative output. The same ReportDocument feeds the CSV
| export and the printed header, so both always describe the same data,
| filters and totals.
|--------------------------------------------------------------------------
*/

export const SCHOOL_NAME = 'Echague National High School'
export const REPORT_TIME_ZONE = 'Asia/Manila'

export type Cell = string | number | null | undefined

export interface ReportTable {
  title: string
  headers: string[]
  rows: Cell[][]
  /** Grand total row, shown after the data rows. */
  footer?: Cell[]
  /** Explicit line shown instead of rows when nothing matches. */
  emptyMessage: string
}

export interface ReportDocument {
  title: string
  /** Label / value pairs describing period, scope, filters and generation info. */
  meta: [string, string][]
  /** Short explanatory notes (what the figures mean). */
  notes: string[]
  /** Headline figures shown before the tables. */
  totals: [string, Cell][]
  tables: ReportTable[]
}

const FORMULA_START = /^[=+\-@\t\r]/
const PLAIN_NUMBER = /^-?\d+(\.\d+)?$/

/**
 * Quotes one CSV cell. Text that a spreadsheet could read as a formula
 * (starts with = + - @) is prefixed with an apostrophe so employee names or
 * filter text can never run as a formula; real numbers are left untouched.
 */
export const csvCell = (value: Cell): string => {
  let text = value === null || value === undefined ? '' : String(value)
  if (typeof value !== 'number' && FORMULA_START.test(text) && !PLAIN_NUMBER.test(text)) text = `'${text}`
  return `"${text.replace(/"/g, '""')}"`
}

/** UTF-8 BOM (so Excel reads accents correctly) + CRLF rows, every cell quoted. */
export const toCsv = (rows: Cell[][]): string =>
  '﻿' + rows.map((row) => row.map(csvCell).join(',')).join('\r\n')

/** Rows of the CSV: identification, context, headline totals, then each table. */
export const buildCsvRows = (report: ReportDocument): Cell[][] => {
  const rows: Cell[][] = [[SCHOOL_NAME], [report.title]]
  report.meta.forEach(([label, value]) => rows.push([label, value]))
  report.notes.forEach((note) => rows.push(['Note', note]))
  if (report.totals.length) {
    rows.push([], ['Overall Totals'], ['Measure', 'Value'])
    report.totals.forEach(([label, value]) => rows.push([label, value]))
  }
  report.tables.forEach((table) => {
    rows.push([], [table.title], table.headers)
    if (table.rows.length === 0) rows.push([table.emptyMessage])
    else table.rows.forEach((row) => rows.push(row))
    if (table.footer) rows.push(table.footer)
  })
  return rows
}

const formatter = (options: Intl.DateTimeFormatOptions, locale = 'en-PH') =>
  new Intl.DateTimeFormat(locale, { timeZone: REPORT_TIME_ZONE, ...options })

/** e.g. "Oct 12, 2026, 4:51 PM (Philippine Time)". */
export const formatGeneratedAt = (date: Date = new Date()): string =>
  `${formatter({ dateStyle: 'medium', timeStyle: 'short' }).format(date)} (Philippine Time)`

/** YYYY-MM-DD in Philippine time, for file names. */
export const fileStamp = (date: Date = new Date()): string =>
  formatter({ year: 'numeric', month: '2-digit', day: '2-digit' }, 'en-CA').format(date)

export const reportFilename = (slug: string, date: Date = new Date()): string =>
  `${slug}_${fileStamp(date)}.csv`

/** Downloads text as a file through a temporary link (browser only). */
export const downloadCsv = (filename: string, rows: Cell[][]): void => {
  const blob = new Blob([toCsv(rows)], { type: 'text/csv;charset=utf-8;' })
  const url = URL.createObjectURL(blob)
  const link = document.createElement('a')
  link.href = url
  link.download = filename
  document.body.appendChild(link)
  link.click()
  document.body.removeChild(link)
  URL.revokeObjectURL(url)
}
