/**
 * Rupiah formatting helpers.
 *
 * Indonesian locale uses "." as the thousands separator ("1.500.000").
 * Input fields keep the raw digits and only render the grouping so the value
 * stays easy to type; the parsed number is what gets sent to the API.
 */

const DIGITS_ONLY = /[^0-9]/g

/**
 * Group a number into Indonesian thousands-separated digits
 * ("1500000" -> "1.500.000"). Returns "" for empty/invalid input so the input
 * field can show a placeholder instead of "0" or "NaN".
 */
export function formatRupiahInput(value: number | string | null | undefined): string {
  const digits = String(value ?? '').replace(DIGITS_ONLY, '')
  if (!digits) return ''
  const n = Number(digits)
  if (!Number.isFinite(n)) return ''
  return Math.trunc(n).toLocaleString('id-ID')
}

/**
 * Parse a rupiah-formatted input back to a number.
 * Returns null when the input holds no digits (so callers can send `null`
 * instead of `0` for an optional amount).
 */
export function parseRupiahInput(value: number | string | null | undefined): number | null {
  const digits = String(value ?? '').replace(DIGITS_ONLY, '')
  if (!digits) return null
  const n = Number(digits)
  return Number.isFinite(n) ? n : null
}

/**
 * Display a number as a full rupiah currency string ("Rp 1.500.000").
 * Used for table cells, receipts, and read-only summaries.
 */
export function formatRupiah(value: number | string | null | undefined): string {
  const n = typeof value === 'number' ? value : Number(String(value ?? '').replace(DIGITS_ONLY, ''))
  if (!Number.isFinite(n)) return 'Rp 0'
  return `Rp ${Math.trunc(n).toLocaleString('id-ID')}`
}

/**
 * Spell an amount out in Indonesian words ("1.500.000" ->
 * "Satu juta lima ratus ribu rupiah"). Used on printed invoices.
 */
export function terbilangRupiah(value: number | string | null | undefined): string {
  const n = typeof value === 'number' ? Math.trunc(value) : Math.trunc(Number(String(value ?? '').replace(DIGITS_ONLY, '')))
  if (!Number.isFinite(n)) return 'Nol rupiah'
  if (n === 0) return 'Nol rupiah'

  const satuan = ['', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan']

  function belowThousand(num: number): string {
    if (num < 10) return satuan[num]
    if (num < 20) return num === 10 ? 'sepuluh' : num === 11 ? 'sebelas' : `${satuan[num - 10]} belas`
    if (num < 100) {
      const r = num % 10
      return `${satuan[Math.floor(num / 10)]} puluh${r ? ' ' + satuan[r] : ''}`
    }
    const hundreds = Math.floor(num / 100)
    const rest = num % 100
    const head = hundreds === 1 ? 'seratus' : `${satuan[hundreds]} ratus`
    return rest ? `${head} ${belowThousand(rest)}` : head
  }

  function recurse(num: number): string {
    if (num < 1000) return belowThousand(num)
    if (num < 1_000_000) {
      const r = num % 1000
      const head = Math.floor(num / 1000)
      return `${head === 1 ? 'seribu' : belowThousand(head) + ' ribu'}${r ? ' ' + belowThousand(r) : ''}`
    }
    if (num < 1_000_000_000) {
      const r = num % 1_000_000
      const head = Math.floor(num / 1_000_000)
      return `${belowThousand(head)} juta${r ? ' ' + recurse(r) : ''}`
    }
    const r = num % 1_000_000_000
    const head = Math.floor(num / 1_000_000_000)
    return `${belowThousand(head)} miliar${r ? ' ' + recurse(r) : ''}`
  }

  const words = recurse(n).replace(/\s+/g, ' ').trim()
  return `${words.charAt(0).toUpperCase()}${words.slice(1)} rupiah`
}
