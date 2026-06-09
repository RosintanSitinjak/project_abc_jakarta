type LoginPayload = {
  email: string
  password: string
  remember?: boolean
}

export const useSanctum = () => {
  const { $api } = useNuxtApp()

  const csrf = () => $api('/sanctum/csrf-cookie')

  const login = (payload: LoginPayload) =>
    $api('/login', {
      method: 'POST',
      body: payload,
    })

  const logout = () =>
    $api('/logout', {
      method: 'POST',
    })

  const me = () => $api('/api/user')

  return { csrf, login, logout, me }
}
