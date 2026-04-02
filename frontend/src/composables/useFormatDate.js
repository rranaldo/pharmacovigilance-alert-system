const defaultOptions = {
  year: 'numeric',
  month: 'short',
  day: 'numeric',
}

export function formatDate(dateStr, options = defaultOptions) {
  if (!dateStr) return '—'
  return new Date(dateStr).toLocaleDateString('en-US', options)
}

export function formatDateTime(dateStr) {
  return formatDate(dateStr, {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}
