<script setup>
import { computed } from 'vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
  auth: Object,
});

// ── Dashboard URL based on role ─────────────────────────────────────────────
const dashboardUrl = computed(() => {
  const slug = props.auth?.role?.slug || props.auth?.user?.role?.slug || '';
  return slug ? `/${slug}/dashboard` : '/';
});

const userRoleName = computed(() => {
  return props.auth?.role?.name || props.auth?.user?.role?.name || 'Pengguna';
});

const userName = computed(() => {
  return props.auth?.name || props.auth?.user?.name || '';
});
</script>

<template>
  <Head title="Selamat Datang - Unit Pembinaan Anggota" />

  <div class="min-h-screen flex flex-col justify-between bg-[#FAFAF9] relative overflow-hidden px-4 py-8">
    
    <!-- Islamic Geometric Tessellation Background (Low Opacity) -->
    <div 
      class="absolute inset-0 opacity-[0.06] pointer-events-none"
      style="background-image: url(&quot;data:image/svg+xml,%3Csvg width='80' height='80' viewBox='0 0 80 80' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M40 0 L52 28 L80 40 L52 52 L40 80 L28 52 L0 40 L28 28 Z' fill='none' stroke='%23047857' stroke-width='1.2'/%3E%3Ccircle cx='40' cy='40' r='12' fill='none' stroke='%23FBBF24' stroke-width='0.8'/%3E%3Cpath d='M40 16 L44 36 L40 40 L36 36 Z' fill='none' stroke='%23047857' stroke-width='0.6'/%3E%3Cpath d='M64 40 L44 44 L40 40 L44 36 Z' fill='none' stroke='%23047857' stroke-width='0.6'/%3E%3Cpath d='M40 64 L36 44 L40 40 L44 44 Z' fill='none' stroke='%23047857' stroke-width='0.6'/%3E%3Cpath d='M16 40 L36 36 L40 40 L36 44 Z' fill='none' stroke='%23047857' stroke-width='0.6'/%3E%3C/svg%3E&quot;); background-repeat: repeat;"
    ></div>

    <!-- Large decorative emerald glow circles -->
    <div class="absolute -top-32 -right-32 w-96 h-96 bg-emerald-500 rounded-full opacity-[0.06] blur-3xl"></div>
    <div class="absolute -bottom-32 -left-32 w-96 h-96 bg-amber-400 rounded-full opacity-[0.06] blur-3xl"></div>

    <!-- Content Wrapper -->
    <div class="w-full max-w-5xl mx-auto my-auto relative z-10 flex flex-col items-center">
      
      <!-- Logo & Brand Header -->
      <div class="text-center mb-10 max-w-2xl">
        <div v-if="$page.props.settings?.logo_path" class="inline-flex items-center justify-center mb-5">
          <img :src="`/storage/${$page.props.settings.logo_path}`" class="h-20 w-20 object-contain rounded-2xl shadow-md" alt="Logo" />
        </div>
        <div v-else class="inline-flex items-center justify-center w-20 h-20 bg-emerald-700 rounded-3xl shadow-lg mb-5">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-amber-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
          </svg>
        </div>
        <h1 class="text-3xl sm:text-4xl font-extrabold text-emerald-950 tracking-tight leading-tight">
          {{ $page.props.settings?.app_title || 'Unit Pembinaan Anggota' }}
        </h1>
        <p class="text-sm sm:text-base text-emerald-800/80 mt-2 font-medium">
          Sistem Informasi Pembinaan Kajian, Mentoring & Dzikir Harian
        </p>

        <!-- Dynamic User Welcome Badge -->
        <div v-if="auth" class="mt-5 inline-flex items-center gap-2 bg-emerald-100/70 border border-emerald-250 text-emerald-800 px-4 py-2 rounded-2xl text-xs sm:text-sm font-semibold shadow-sm">
          <span>👋 Assalamualaikum,</span>
          <span class="font-extrabold">{{ userName }}</span>
          <span class="bg-emerald-700 text-white text-[10px] px-2 py-0.5 rounded-full font-bold uppercase">{{ userRoleName }}</span>
        </div>
      </div>

      <!-- Menu Grid Cards -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 w-full max-w-4xl px-2">
        
        <!-- Card 1: Al-Quran -->
        <div class="bg-white border border-emerald-100 hover:border-emerald-300 rounded-3xl p-6 flex flex-col justify-between shadow-sm hover:shadow-xl transition-all duration-300 group hover:-translate-y-1">
          <div class="space-y-4">
            <div class="w-12 h-12 bg-emerald-50 group-hover:bg-emerald-100 rounded-2xl flex items-center justify-center text-2xl transition-colors">
              📖
            </div>
            <div>
              <h2 class="text-lg font-bold text-emerald-950 group-hover:text-emerald-800 transition-colors">Al-Quran Al-Karim</h2>
              <p class="text-xs text-gray-500 mt-1 leading-relaxed">
                Baca 114 surah dengan terjemahan Indonesia lengkap, dengarkan audio murottal Mishary Alafasy per-ayat, serta pelajari Tafsir Ibn Kathir.
              </p>
            </div>
          </div>
          <div class="mt-6">
            <a 
              href="/quran" 
              class="w-full inline-flex items-center justify-center bg-emerald-50 group-hover:bg-emerald-700 text-emerald-750 group-hover:text-white font-bold text-xs py-3 px-4 rounded-xl transition-all border border-emerald-100 group-hover:border-emerald-700 shadow-sm"
            >
              Baca Al-Quran
              <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 ml-1.5 transform group-hover:translate-x-0.5 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M9 5l7 7-7 7" />
              </svg>
            </a>
          </div>
        </div>

        <!-- Card 2: Al-Ma'tsurat -->
        <div class="bg-white border border-amber-100 hover:border-amber-300 rounded-3xl p-6 flex flex-col justify-between shadow-sm hover:shadow-xl transition-all duration-300 group hover:-translate-y-1">
          <div class="space-y-4">
            <div class="w-12 h-12 bg-amber-50 group-hover:bg-amber-100 rounded-2xl flex items-center justify-center text-2xl transition-colors">
              ✨
            </div>
            <div>
              <h2 class="text-lg font-bold text-emerald-950 group-hover:text-amber-800 transition-colors">Al-Ma'tsurat Wazifah</h2>
              <p class="text-xs text-gray-500 mt-1 leading-relaxed">
                Amalkan dzikir pagi dan petang secara mudah dengan fitur tasbih counter ketukan suara interaktif untuk menjaga keistiqomahan dzikir harian.
              </p>
            </div>
          </div>
          <div class="mt-6">
            <a 
              href="/matsurat" 
              class="w-full inline-flex items-center justify-center bg-amber-50 group-hover:bg-amber-500 text-amber-900 group-hover:text-emerald-950 font-bold text-xs py-3 px-4 rounded-xl transition-all border border-amber-100 group-hover:border-amber-500 shadow-sm"
            >
              Dzikir Sekarang
              <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 ml-1.5 transform group-hover:translate-x-0.5 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M9 5l7 7-7 7" />
              </svg>
            </a>
          </div>
        </div>

        <!-- Card 3: Login / Dashboard -->
        <div class="bg-white border border-emerald-100 hover:border-emerald-350 rounded-3xl p-6 flex flex-col justify-between shadow-sm hover:shadow-xl transition-all duration-300 group hover:-translate-y-1">
          <div class="space-y-4">
            <div class="w-12 h-12 bg-emerald-800 text-white rounded-2xl flex items-center justify-center text-xl transition-colors">
              🔐
            </div>
            <div>
              <h2 class="text-lg font-bold text-emerald-950 group-hover:text-emerald-850 transition-colors">
                {{ auth ? 'Dashboard UPA' : 'Masuk ke Sistem' }}
              </h2>
              <p class="text-xs text-gray-500 mt-1 leading-relaxed">
                {{ auth 
                  ? 'Akses area panel pengawasan, rekap absen, tugas ustadz, laporan mentoring, serta unduhan materi pembinaan kajian UPA.' 
                  : 'Masuk dengan akun Ustadz, Ketua Kelompok, Admin, atau Anggota Anda untuk mengelola kehadiran dan pembinaan kajian.' 
                }}
              </p>
            </div>
          </div>
          <div class="mt-6">
            <a 
              :href="auth ? dashboardUrl : '/login'" 
              class="w-full inline-flex items-center justify-center bg-gradient-to-r from-emerald-800 to-emerald-900 hover:from-emerald-700 hover:to-emerald-800 text-white font-bold text-xs py-3 px-4 rounded-xl transition-all shadow-md hover:shadow-lg"
            >
              <span>{{ auth ? 'Buka Dashboard' : 'Masuk Akun' }}</span>
              <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 ml-1.5 transform group-hover:translate-x-0.5 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M9 5l7 7-7 7" />
              </svg>
            </a>
          </div>
        </div>

      </div>

    </div>

    <!-- Footer -->
    <div class="text-center mt-12 relative z-10">
      <p class="text-xs text-gray-400">
        &copy; 2026 Unit Pembinaan Anggota. Semua hak dilindungi.
      </p>
      <p class="text-[10px] text-gray-300 mt-1 font-semibold">
        Bismillahirrahmanirrahim · Menuju Ketaatan yang Lebih Istiqomah
      </p>
    </div>

  </div>
</template>

<style scoped>
.hover\:border-emerald-350:hover {
  border-color: #059669;
}
</style>
