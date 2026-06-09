/**
 * Strips HTML tags from a string and returns plain text.
 * Useful for short descriptions or previews.
 */
export const stripHtml = (html: string | null | undefined): string => {
  if (!html) return ''
  return html.replace(/<[^>]*>?/gm, ' ').replace(/\s+/g, ' ').trim()
}
