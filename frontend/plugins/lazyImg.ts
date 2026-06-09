import lazyImg from '~/directives/lazyImg'

export default defineNuxtPlugin((nuxtApp) => {
  nuxtApp.vueApp.directive('lazy-src', lazyImg)
})
