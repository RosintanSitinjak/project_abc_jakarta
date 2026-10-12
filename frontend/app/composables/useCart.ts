export interface CartItem {
  id: string | number
  slug: string
  title: string
  author?: string
  coverUrl?: string | null
  price: number          // Harga umum
  member_price: number   // Harga PL
  stock: number          // Sisa stok di gudang
  qty: number
}

export const useCart = () => {
  // State global agar isi keranjang tersinkron di semua halaman
  const cart = useState<CartItem[]>('shopping-cart', () => [])
  const { isApprovedPL } = useAuth()

  // Muat data dari localStorage saat pertama kali browser dibuka
  if (import.meta.client) {
    onMounted(() => {
      const saved = localStorage.getItem('abc_cart')
      if (saved && cart.value.length === 0) {
        try {
          cart.value = JSON.parse(saved)
        } catch {
          cart.value = []
        }
      }
    })

    // Simpan otomatis ke localStorage setiap ada perubahan
    watch(cart, (newCart) => {
      localStorage.setItem('abc_cart', JSON.stringify(newCart))
    }, { deep: true })
  }

  // 1. Tambah Buku ke Keranjang
  const addToCart = (book: any, quantity: number = 1) => {
    const existing = cart.value.find((item) => item.id === book.id)
    const availableStock = book.stock ?? 999

    if (availableStock <= 0) {
      ElMessage.warning('Maaf, stok buku ini sedang habis.')
      return false
    }

    if (existing) {
      if (existing.qty + quantity > availableStock) {
        ElMessage.warning(`Jumlah melebihi stok yang tersedia (Maksimal: ${availableStock} eks).`)
        return false
      }
      existing.qty += quantity
    } else {
      if (quantity > availableStock) {
        ElMessage.warning(`Jumlah melebihi stok yang tersedia (Maksimal: ${availableStock} eks).`)
        return false
      }
      cart.value.push({
        id: book.id,
        slug: book.slug,
        title: book.title,
        author: book.author,
        coverUrl: book.coverUrl,
        price: Number(book.price) || 0,
        member_price: Number(book.member_price) || Number(book.price) || 0,
        stock: availableStock,
        qty: quantity
      })
    }

    ElMessage.success(`"${book.title}" berhasil ditambahkan ke keranjang.`)
    return true
  }

  // 2. Ubah Jumlah (Qty)
  const updateQty = (id: string | number, newQty: number) => {
    const item = cart.value.find((i) => i.id === id)
    if (!item) return

    if (newQty <= 0) {
      removeFromCart(id)
      return
    }

    if (newQty > item.stock) {
      ElMessage.warning(`Maksimal pembelian sesuai sisa stok: ${item.stock} eks.`)
      item.qty = item.stock
      return
    }

    item.qty = newQty
  }

  // 3. Hapus Buku dari Keranjang
  const removeFromCart = (id: string | number) => {
    cart.value = cart.value.filter((i) => i.id !== id)
    ElMessage.info('Buku dihapus dari keranjang.')
  }

  // 4. Kosongkan Keranjang
  const clearCart = () => {
    cart.value = []
    if (import.meta.client) {
      localStorage.removeItem('abc_cart')
    }
  }

  // 5. Total Jumlah Item
  const totalItems = computed(() => {
    return cart.value.reduce((acc, item) => acc + item.qty, 0)
  })

  // 6. Subtotal Belanja (Otomatis deteksi harga PL vs Harga Umum)
  const subtotal = computed(() => {
    return cart.value.reduce((acc, item) => {
      const activePrice = isApprovedPL.value ? item.member_price : item.price
      return acc + (activePrice * item.qty)
    }, 0)
  })

  // Format IDR Helper
  const formatRupiah = (num: number) => {
    return new Intl.NumberFormat('id-ID', {
      style: 'currency',
      currency: 'IDR',
      maximumFractionDigits: 0
    }).format(num)
  }

  return {
    cart,
    addToCart,
    updateQty,
    removeFromCart,
    clearCart,
    totalItems,
    subtotal,
    formatRupiah
  }
}