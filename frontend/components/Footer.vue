<template>
  <div class="py-14 sm:py-20 bg-slate-900 ">
    <div class="mx-auto px-4 sm:px-6 lg:px-20 xl:px-34 w-full grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-x-10 gap-y-10">
      <div class="flex flex-col gap-4">
        <div>
          <img src="/images/logo-white.png" alt="Company Logo" class="h-16" width="188" height="64"/>
        </div>

        <p class="text-sm text-slate-400 tracking-wider">Kami berkomitmen untuk memberikan solusi terbaik bagi klien kami dengan layanan yang andal dan inovatif.</p>

        <div class="flex items-center gap-2">
          <a
            href="#"
            aria-label="LinkedIn"
            class="flex text-slate-300 transition hover:text-white"
          >
            <Icon icon="tabler:brand-linkedin" class="h-6 w-6" />
          </a>
          <a
            href="#"
            aria-label="Instagram"
            class="flex text-slate-300 transition hover:text-white"
          >
            <Icon icon="tabler:brand-instagram" class="h-6 w-6" />
          </a>
        </div>
      </div>

      <div class="flex flex-col gap-4">
        <p class="text-white text-lg font-semibold tracking-wide">Quick Links</p>

        <ul class="text-slate-300 text-sm flex flex-col gap-2">
          <li class="hover:text-white transition">
            <NuxtLink to="/">Beranda</NuxtLink>
          </li>
          <li class="hover:text-white transition">
            <NuxtLink to="/about">Tentang Kami</NuxtLink>
          </li>
          <li class="hover:text-white transition">
            <NuxtLink to="/service">Layanan</NuxtLink>
          </li>
          <li class="hover:text-white transition">
            <NuxtLink to="/product">Produk</NuxtLink>
          </li>
          <!-- <li class="hover:text-white transition">
            <NuxtLink to="/portfolio">Portofolio</NuxtLink>
          </li> -->
          <li class="hover:text-white transition">
            <NuxtLink to="/articles">Artikel</NuxtLink>
          </li>
          <li class="hover:text-white transition">
            <NuxtLink to="/contact">Kontak</NuxtLink>
          </li>
        </ul>
      </div>

      <div class="flex flex-col gap-4">
        <p class="text-white text-lg font-semibold tracking-wide">Layanan Utama</p>

        <ul v-for="service in services" :key="service.id" class="text-slate-300 text-sm flex flex-col gap-2">
          <li class="hover:text-white transition">
            <NuxtLink to="/service">{{ service.name }}</NuxtLink>
          </li>
        </ul>
      </div>

      <div class="flex flex-col gap-4">
        <p class="text-white text-lg font-semibold tracking-wide">Kontak</p>

        <ul class="text-slate-300 flex flex-col gap-2">
          <li class="flex items-start gap-3">
            <Icon icon="tabler:map-pin" class="shrink-0 h-5 w-5 mt-0.5"/>
            <a class="hover:text-white transition" target="_blank" href="https://maps.app.goo.gl/tpzLKUeuJbu5gYMo6">Graha Mustika Ratu 4th Floor, Jalan Gatot Subroto Kav 74-75, DKI Jakarta 12870 - Indonesia</a>
          </li>
          <li class="flex items-start gap-3">
            <Icon icon="tabler:mail" class="shrink-0 h-5 w-5 mt-0.5"/>
            <a class="hover:text-white transition" href="mailto:info@lamsolusi.com">info@lamsolusi.com</a>
          </li>
          <li class="flex items-start gap-3">
            <Icon icon="tabler:phone" class="shrink-0 h-5 w-5 mt-0.5"/>
            <a class="hover:text-white transition" target="_blank" href="https://wa.me/62881082203778">+62 881 0822 03778</a>
          </li>
        </ul>
      </div>

      <div class="sm:col-span-2 md:col-span-4 text-center text-slate-500 text-sm mt-10 border-t border-slate-800">
        <div class="pt-10">&copy; {{ new Date().getFullYear() }} PT Lamjaya Global Solusi. All rights reserved.</div>
      </div>
    </div>
  </div>
</template>

<script lang="ts" setup>

import { storeToRefs } from 'pinia'
import { useAppStore } from '~/stores/app'

const appStore = useAppStore()
const { services, loadingServices } = storeToRefs(appStore)
</script>