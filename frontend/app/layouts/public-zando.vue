<template>
  <div class="min-h-screen bg-[#0b0b0b] text-white font-sans flex flex-col justify-between">
    
    <header class="w-full flex justify-between items-center px-5 py-7 md:px-10 bg-black z-50">
      <div class="flex items-center">
        <NuxtLink to="/">
          <img 
            src="https://rtsolutz.com/raven/demo-zando/images/logo-white.png" 
            alt="logo Zando cuy!!" 
            class="h-8 md:h-10 w-auto object-contain cursor-pointer"
          />
        </NuxtLink>
      </div>

      <nav class="hidden md:flex items-center gap-8">
        <NuxtLink 
          to="/" 
          class="text-xs uppercase tracking-widest font-bold text-gray-400 hover:text-white transition duration-300"
          active-class="text-white"
        >
          Home
        </NuxtLink>
        <NuxtLink 
          to="/articles" 
          class="text-xs uppercase tracking-widest font-bold text-gray-400 hover:text-white transition duration-300"
          active-class="text-white"
        >
          Articles
        </NuxtLink>
        <NuxtLink 
          to="/contact" 
          class="text-xs uppercase tracking-widest font-bold text-gray-400 hover:text-white transition duration-300"
          active-class="text-white"
        >
          Contact
        </NuxtLink>
        <NuxtLink 
          to="/login" 
          class="text-xs uppercase tracking-widest font-bold text-gray-400 hover:text-white transition duration-300"
          active-class="text-white"
        >
          Sign In
        </NuxtLink>
      </nav>

      <div class="flex items-center gap-6 md:gap-10">
        <a
          href="https://themeforest.net/user/raventhemez" 
          target="_blank"
          class="text-[12px] md:text-[10px] uppercase tracking-widest font-bold hover:text-gray-400 transition duration-300"
        >
          Buy Template
        </a>
      </div>
    </header>

    <main class="flex-1 w-full flex flex-col">
      <slot />
    </main>

    <section class="flex justify-center items-center w-full py-9 bg-black border-t border-neutral-900">
      <img
        src="https://rtsolutz.com/raven/demo-zando/images/logo-white.png"
        alt="Logo Zando"
        class="max-w-[190px]"
      />
    </section>

    <el-backtop :right="10" :bottom="25">
      <div class="bg-black text-white w-10 h-10 rounded-full flex items-center justify-center shadow-2xl hover:bg-white hover:text-black transition duration-500">
        ↑
      </div>
    </el-backtop>

  </div>
</template>