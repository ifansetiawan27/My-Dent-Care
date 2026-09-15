/**
 * RFC 4122 v4 UUID with a safe fallback.
 *
 * `crypto.randomUUID()` is only exposed in secure contexts (HTTPS or
 * localhost). On a plain-HTTP origin it is `undefined` and calling it throws a
 * TypeError, which previously crashed the login flow. Fall back to a
 * Math.random-based generator so device ids keep working everywhere.
 */
export function uuid(): string {
  const c = typeof crypto !== 'undefined' ? crypto : undefined
  if (c && typeof c.randomUUID === 'function') {
    return c.randomUUID()
  }

  return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, (char) => {
    const random = (Math.random() * 16) | 0
    const value = char === 'x' ? random : (random & 0x3) | 0x8
    return value.toString(16)
  })
}
