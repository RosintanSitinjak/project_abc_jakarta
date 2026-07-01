<template>
  <div class="w-full grid grid-cols-1 auto-rows-max bg-[#f5f5f5]">

    <section class="w-full flex flex-col items-center justify-center px-6 md:px-10 text-center bg-black text-white py-16 md:py-24 m-0">
      
      <h1 class="text-3xl font-semibold tracking-tight leading-tight max-w-5xl">
        Zando – Creative Minimal Portfolio <br />
        Landing Page
      </h1>

      <p class="mt-8 text-zinc-300 text-sm max-w-3xl mx-auto leading-relaxed">
        Zando is a Creative Portfolio Template. <br />
        It provides responsive clean and Unique html template for your creative Agency.
      </p>

      <div class="mt-10 flex flex-row gap-2 w-full justify-center items-center">
        <el-button
          tag="a"
          href="https://rtsolutz.com/raven/demo-zando/#content"
          target="_blank" 
          round 
          size="large"
          class="!bg-white !text-black !border-none px-8 py-2 rounded-full text-base hover:!bg-[#212529] hover:!text-white !transition-all !duration-[500ms] !ease-in-out w-auto">
          CHECK DEMOS
        </el-button>

        <el-button 
          tag="a"
          href="https://themeforest.net/user/raventhemez"
          target="_blank"
          round 
          size="large"
          class="!ml-0 !bg-white !text-black !border-none px-8 py-2 rounded-full text-base hover:!bg-[#212529] hover:!text-white !transition-all !duration-[500ms] !ease-in-out w-auto">
          Developed By Raven Themez
        </el-button>
      </div>

    </section>

    <section id="content" class="py-20 bg-[#f5f5f5] text-center px-6 md:px-10 lg:px-20 m-0">
      <div class="max-w-7xl mx-auto">
        <h2 class="text-3xl md:text-4xl text-zinc-800 mb-16 md:mb-24">
          Light Version
        </h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
          <div v-for="page in lightPages" :key="page.title" class="group cursor-pointer">
            <el-tooltip content="Open Demo" placement="right-start">
              <a :href="page.link" target="_blank">
                <div class="overflow-hidden rounded-2xl shadow-xl border border-gray-100 transition-transform duration-300 group-hover:-translate-y-2 bg-white">
                  <img :src="page.image" :alt="page.title" class="w-full h-auto object-cover" />
                </div>
              </a>
            </el-tooltip>
            <h3 class="mt-8 text-[16px] font-bold text-gray-400 group-hover:text-black transition">
              {{ page.title }}
            </h3>
          </div>
        </div>
      </div>
    </section>

    <section class="py-20 pb-32 bg-[#f5f5f5] text-center px-6 md:px-10 lg:px-20 m-0">
      <div class="max-w-7xl mx-auto">
        <h2 class="text-3xl md:text-4xl text-black mb-16 md:mb-24">
          Dark Version
        </h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
          <div v-for="page in darkPages" :key="page.title" class="group cursor-pointer">
            <el-tooltip content="Open Demo" placement="right-start">
              <a :href="page.link" target="_blank">
                <div class="overflow-hidden rounded-2xl shadow-xl border border-gray-100 transition-transform duration-300 group-hover:-translate-y-2 bg-white">
                  <img :src="page.image" :alt="page.title" class="w-full h-auto object-cover" />
                </div>
              </a>
            </el-tooltip>
            <h3 class="mt-8 text-[16px] font-bold text-gray-400 group-hover:text-black transition">
              {{ page.title }}
            </h3>
          </div>
        </div>
      </div>
    </section>

  </div>
</template>


<script setup>
definePageMeta({
  layout: 'public',
})

const lightPages = [
  {
    title: "Home Page",
    image: "https://rtsolutz.com/raven/demo-zando/images/light/home.png",
    link: "https://rtsolutz.com/raven/demo-zando/zando/index.html",
  },
  {
    title: "Portfolio 3 Columns",
    image: "https://rtsolutz.com/raven/demo-zando/images/light/portfolio-col3.png",
    link: "https://rtsolutz.com/raven/demo-zando/zando/portfolio-col-3.html",
  },
  {
    title: "Portfolio 2 Columns",
    image: "https://rtsolutz.com/raven/demo-zando/images/light/portfolio-col2.png",
    link: "https://rtsolutz.com/raven/demo-zando/zando/portfolio-col-2.html",
  },
  {
    title: "Portfolio 4 Columns",
    image: "https://rtsolutz.com/raven/demo-zando/images/light/portfolio-col4.png",
    link: "https://rtsolutz.com/raven/demo-zando/zando/portfolio-col-4.html",
  },
  {
    title: "Portfolio Detail",
    image: "https://rtsolutz.com/raven/demo-zando/images/light/portfolio-detail.png",
    link: "https://rtsolutz.com/raven/demo-zando/zando/portfolio-detail.html",
  },
  {
    title: "About Me",
    image: "https://rtsolutz.com/raven/demo-zando/images/light/about-me.png",
    link: "https://rtsolutz.com/raven/demo-zando/zando/about-me.html",
  },
  {
    title: "About Us",
    image: "https://rtsolutz.com/raven/demo-zando/images/light/about-us.png",
    link: "https://rtsolutz.com/raven/demo-zando/zando/about-us.html",
  },
  {
    title: "Blog Listing V1",
    image: "https://rtsolutz.com/raven/demo-zando/images/light/blog-listing1.png",
    link: "https://rtsolutz.com/raven/demo-zando/zando/blog-listing.html",
  },
  {
    title: "Blog Listing V2",
    image: "https://rtsolutz.com/raven/demo-zando/images/light/blog-listing2.png",
    link: "https://rtsolutz.com/raven/demo-zando/zando/blog-listing2.html",
  },
  {
    title: "Blog Single",
    image: "https://rtsolutz.com/raven/demo-zando/images/light/blog-single.png",
    link: "https://rtsolutz.com/raven/demo-zando/zando/blog-single.html",
  },
  {
    title: "Pricing",
    image: "https://rtsolutz.com/raven/demo-zando/images/light/pricing.png",
    link: "https://rtsolutz.com/raven/demo-zando/zando/pricing.html",
  },
  {
    title: "Contact Us",
    image: "https://rtsolutz.com/raven/demo-zando/images/light/contact.png",
    link: "https://rtsolutz.com/raven/demo-zando/zando/contact.html",
  },
  {
    title: "404 Page",
    image: "https://rtsolutz.com/raven/demo-zando/images/light/404.png",
    link: "https://rtsolutz.com/raven/demo-zando/zando/404.html",
  },
]

const darkPages = [
  {
    title: "Home Page",
    image: "https://rtsolutz.com/raven/demo-zando/images/dark/home.png",
    link: "https://rtsolutz.com/raven/demo-zando/zando-black/index.html",
  },
  {
    title: "Portfolio 3 Columns",
    image: "https://rtsolutz.com/raven/demo-zando/images/dark/portfolio-col3.png",
    link: "https://rtsolutz.com/raven/demo-zando/zando-black/portfolio-col-3.html",
  },
  {
    title: "Portfolio 2 Columns",
    image: "https://rtsolutz.com/raven/demo-zando/images/dark/portfolio-col2.png",
    link: "https://rtsolutz.com/raven/demo-zando/zando-black/portfolio-col-2.html",
  },
  {
    title: "Portfolio 4 Columns",
    image: "https://rtsolutz.com/raven/demo-zando/images/dark/portfolio-col4.png",
    link: "https://rtsolutz.com/raven/demo-zando/zando-black/portfolio-col-4.html",
  },
  {
    title: "Portfolio Detail",
    image: "https://rtsolutz.com/raven/demo-zando/images/dark/portfolio-detail.png",
    link: "https://rtsolutz.com/raven/demo-zando/zando-black/portfolio-detail.html",
  },
  {
    title: "About Me",
    image: "https://rtsolutz.com/raven/demo-zando/images/dark/about-me.png",
    link: "https://rtsolutz.com/raven/demo-zando/zando-black/about-me.html",
  },
  {
    title: "About Us",
    image: "https://rtsolutz.com/raven/demo-zando/images/dark/about-us.png",
    link: "https://rtsolutz.com/raven/demo-zando/zando-black/about-us.html",
  },
  {
    title: "Blog Listing V1",
    image: "https://rtsolutz.com/raven/demo-zando/images/dark/blog-listing1.png",
    link: "https://rtsolutz.com/raven/demo-zando/zando-black/blog-listing.html",
  },
  {
    title: "Blog Listing V2",
    image: "https://rtsolutz.com/raven/demo-zando/images/dark/blog-listing2.png",
    link: "https://rtsolutz.com/raven/demo-zando/zando-black/blog-listing2.html",
  },
  {
    title: "Blog Single",
    image: "https://rtsolutz.com/raven/demo-zando/images/dark/blog-single.png",
    link: "https://rtsolutz.com/raven/demo-zando/zando-black/blog-single.html",
  },
  {
    title: "Pricing",
    image: "https://rtsolutz.com/raven/demo-zando/images/dark/pricing.png",
    link: "https://rtsolutz.com/raven/demo-zando/zando-black/pricing.html",
  },
  {
    title: "Contact Us",
    image: "https://rtsolutz.com/raven/demo-zando/images/dark/contact.png",
    link: "https://rtsolutz.com/raven/demo-zando/zando-black/contact.html",
  },
  {
    title: "404 Page",
    image: "https://rtsolutz.com/raven/demo-zando/images/dark/404.png",
    link: "https://rtsolutz.com/raven/demo-zando/zando-black/404.html",
  },
]
</script>