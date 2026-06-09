<template>
  <div class="min-h-screen bg-[#0b0b0b] text-white font-sans flex flex-col justify-between">
  <!-- <div class="min-h-screen text-white font-sans flex flex-col justify-between"> -->
    
    <header class="w-full flex justify-between items-center px-5 py-7 md:px-10 bg-black z-50">
      <div class="flex items-center">
        <img 
          src="https://rtsolutz.com/raven/demo-zando/images/logo-white.png" 
          alt="logo Zando cuy!!" 
          class="h-8 md:h-10 w-auto object-contain"
        />
      </div>

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


    
    <!-- <footer class="bg-white border-t border-slate-200 pt-16 pb-12 px-6 md:px-10 lg:px-20 text-slate-600">
    <div class="max-w-7xl mx-auto"> -->
      
      <!-- Grid Kolom -->
      <!-- <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-10 text-left"> -->
        
        <!-- Kolom Brand/Logo -->
        <!-- <div class="space-y-4 lg:col-span-1">
          <div class="flex items-center gap-2"> -->
            <!-- Icon/Logo Zando berbentuk bulat biru -->
            <!-- <span class="w-8 h-8 rounded-full bg-blue-600 flex items-center justify-center text-white font-bold text-lg">Z</span>
            <span class="text-xl font-bold text-slate-900">Zando</span>
          </div> -->
          <!-- <p class="text-sm text-slate-500 leading-relaxed">
            Our beautiful designs open the door to a realm of limitless possibilities, where imagination knows no bounds.
          </p> -->
        <!-- </div> -->
        <!-- Kolom Features -->
        <!-- <div>
          <h4 class="text-slate-900 font-semibold mb-4 text-sm tracking-wide">Features</h4>
          <ul class="space-y-3 text-sm">
            <li><a href="#" class="hover:text-slate-900 transition">Page builder</a></li>
            <li><a href="#" class="hover:text-slate-900 transition">Theme options</a></li>
            <li><a href="#" class="hover:text-slate-900 transition">Theme builder</a></li>
            <li><a href="#" class="hover:text-slate-900 transition">Template library</a></li>
          </ul>
        </div> -->
        <!-- Kolom Resources -->
        <!-- <div>
          <h4 class="text-slate-900 font-semibold mb-4 text-sm tracking-wide">Resources</h4>
          <ul class="space-y-3 text-sm">
            <li><a href="#" class="hover:text-slate-900 transition">Support center</a></li>
            <li><a href="#" class="hover:text-slate-900 transition">Documentation</a></li>
            <li><a href="#" class="hover:text-slate-900 transition">Community</a></li>
            <li><a href="#" class="hover:text-slate-900 transition">Hosting</a></li>
          </ul>
        </div> -->
        <!-- Kolom Follow Us -->
        <!-- <div>
          <h4 class="text-slate-900 font-semibold mb-4 text-sm tracking-wide">Follow us</h4>
          <ul class="space-y-3 text-sm">
            <li><a href="#" class="hover:text-slate-900 transition">Facebook</a></li>
            <li><a href="#" class="hover:text-slate-900 transition">ThemeForest</a></li>
            <li><a href="#" class="hover:text-slate-900 transition">Dribbble</a></li>
          </ul>
        </div> -->
        <!-- Kolom We're Social -->
        <!-- <div>
          <h4 class="text-slate-900 font-semibold mb-4 text-sm tracking-wide">We're social</h4>
          <div class="flex gap-3">
 -->
            <!-- Icon Facebook -->
            <!-- <a href="https://sierra.keydesign.xyz/#" target="_blank" class="w-10 h-10 rounded-xl bg-slate-50 hover:bg-slate-100 flex items-center justify-center text-blue-600 transition">
              <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24">
                <path d="M0 0h24v24H0z" fill="none" />
                <path fill="currentColor" d="M15.12 5.32H17V2.14A26 26 0 0 0 14.26 2c-2.72 0-4.58 1.66-4.58 4.7v2.62H6.61v3.56h3.07V22h3.68v-9.12h3.06l.46-3.56h-3.52V7.05c0-1.05.28-1.73 1.76-1.73" />
            </svg>
            </a> -->

            <!-- Icon Api-->
            <!-- <a href="https://themeforest.net/user/key-design" target="_blank" class="w-10 h-10 rounded-xl bg-slate-50 hover:bg-slate-100 flex items-center justify-center text-blue-500 transition">
            <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24">
              <path d="M0 0h24v24H0z" fill="none" />
              <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3q1 4 4 6.5t3 5.5a1 1 0 0 1-14 0a5 5 0 0 1 1-3a1 1 0 0 0 5 0c0-2-1.5-3-1.5-5q0-2 2.5-4" />
            </svg>
            </a> -->

            <!-- Icon Globe -->
            <!-- <a href="https://dribbble.com/keydesign-themes" target="_blank" class="w-10 h-10 rounded-xl bg-slate-50 hover:bg-slate-100 flex items-center justify-center text-blue-600 transition">
              <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24">
                <path d="M0 0h24v24H0z" fill="none" />
                  <g fill="none" stroke="currentColor" stroke-width="1.2">
                    <circle cx="12" cy="12" r="8" fill="currentColor" fill-opacity=".25" />
                    <path d="M18.572 7.206c-.724.848-1.685 1.543-2.805 2.028a9.5 9.5 0 0 1-3.634.765a9.6 9.6 0 0 1-3.664-.666C7.33 8.877 6.34 8.209 5.583 7.38m12.989 9.414c-.724-.848-1.685-1.543-2.805-2.028A9.5 9.5 0 0 0 12.133 14a9.6 9.6 0 0 0-3.664.666c-1.139.456-2.128 1.124-2.886 1.952M12 4v16" />
                  </g>
            </svg>
            </a>
          </div>
        </div>
      </div>
 -->
      <!-- Garis pemisah bawah & copyright sesuai gambar -->
      <!-- <div class="border-t border-slate-100 mt-12 pt-8 flex flex-col sm:flex-row justify-between items-center text-slate-400 text-sm gap-4 text-left">
        <p>© Zando WordPress Theme. All Rights Reserved.</p>
        <div class="flex gap-6">
          <a href="#" class="hover:text-slate-900 transition">Terms & Conditions</a>
          <a href="#" class="hover:text-slate-900 transition">Privacy Policy</a>
        </div>
      </div> -->
    <!-- </div>
  </footer>
 -->

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