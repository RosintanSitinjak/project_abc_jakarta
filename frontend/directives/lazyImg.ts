import type { Directive } from 'vue'

/**
 * v-lazy-src directive
 *
 * Usage: <img v-lazy-src="imageUrl" />
 *
 * The image src will only be set when the element enters the viewport
 * (with a 200px root margin so it starts loading just before it's visible).
 */
const lazyImg: Directive<HTMLImageElement, string> = {
  mounted(el, binding) {
    const src = binding.value
    if (!src) return

    // Set a transparent placeholder
    el.src = 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7'

    const observer = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            el.src = src
            observer.unobserve(el)
          }
        })
      },
      {
        rootMargin: '200px',
      },
    )

    observer.observe(el)

    // Store observer for cleanup
    ;(el as any)._lazyObserver = observer
  },

  updated(el, binding) {
    // If the src value changes, update accordingly
    if (binding.value !== binding.oldValue && binding.value) {
      el.src = binding.value
    }
  },

  unmounted(el) {
    const observer = (el as any)._lazyObserver as IntersectionObserver | undefined
    if (observer) {
      observer.unobserve(el)
      ;(el as any)._lazyObserver = undefined
    }
  },
}

export default lazyImg
