/**
 * Format a Date as a `datetime-local` input value in the user's LOCAL
 * timezone ("YYYY-MM-DDTHH:mm").
 *
 * Do NOT use `toISOString()` for this. `toISOString()` returns UTC while a
 * `<input type="datetime-local">` interprets its value as local time, so the
 * field ends up shifted by the UTC offset (e.g. 7 hours behind in WIB/UTC+7).
 */
export function localDatetimeInput(date: Date = new Date()): string {
  const pad = (n: number): string => String(n).padStart(2, '0')

  return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}T${pad(date.getHours())}:${pad(date.getMinutes())}`
}

/**
 * The "YYYY-MM-DD" key for the current day in the user's LOCAL timezone.
 *
 * Appointment `scheduled_at` values are ISO strings; slicing one to 10 chars
 * gives its date key. Comparing those keys keeps calendar/queue grouping in
 * the local day instead of drifting across the UTC boundary.
 */
export function todayKey(date: Date = new Date()): string {
  const pad = (n: number): string => String(n).padStart(2, '0')
  return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}`
}

/** Date key ("YYYY-MM-DD") of an ISO datetime string, or '' when absent. */
export function dateKey(iso: string | undefined | null): string {
  return String(iso ?? '').slice(0, 10)
}

/** Format an ISO datetime as a short local time ("HH:mm"), "--:--" if invalid. */
export function fmtTime(iso: string | undefined | null): string {
  if (!iso) return '--:--'
  const d = new Date(iso)
  return Number.isNaN(d.getTime()) ? '--:--' : d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })
}
