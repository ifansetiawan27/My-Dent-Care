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
