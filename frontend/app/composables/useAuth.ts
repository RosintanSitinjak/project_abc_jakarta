export const useAuth = () => {
  // Global state untuk data user
  const authUser = useState<any>('auth-user', () => null)
  const { apiFetch } = useApi()

  // Fungsi untuk mengecek profil (Poin 3 & 5)
  const checkProfile = async () => {
    try {
      const user = await apiFetch('/user')
      authUser.value = user
    } catch (e) {
      authUser.value = null
    }
  }

  // Helper untuk pengecekan harga khusus (Poin 4)
  const isApprovedPL = computed(() => {
    return authUser.value?.customer?.type === 'penginjil' && 
           authUser.value?.customer?.status === 'approved'
  })

  const isPendingPL = computed(() => {
  return authUser.value?.customer?.type === 'penginjil' && 
         authUser.value?.customer?.status === 'pending'
})

  // Helper untuk pengecekan metode pembayaran (Poin 4)
  const canUseCredit = computed(() => {
    return authUser.value?.customer?.type !== 'jemaat'
  })

 const logout = async () => {
    try {
      await apiFetch('/auth/logout', { method: 'POST' })
      authUser.value = null
      navigateTo('/login')
    } catch (e) {
      authUser.value = null
      navigateTo('/login')
    }
  }

  return { 
    authUser, 
    checkProfile, 
    isApprovedPL, 
    isPendingPL, 
    canUseCredit, 
    logout 
  }
}