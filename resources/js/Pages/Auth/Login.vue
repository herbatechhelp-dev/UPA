<script setup>
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({
  email: '',
  password: '',
  remember: false,
});

const isLoading = ref(false);
const showPassword = ref(false);

const submit = () => {
  isLoading.value = true;
  form.post('/login', {
    onFinish: () => {
      isLoading.value = false;
    }
  });
};
</script>

<template>
  <Head title="Login - Unit Pembinaan Anggota" />

  <div class="min-h-screen flex items-center justify-center bg-[#FAFAF9] relative overflow-hidden px-4 py-8">
    
    <!-- Islamic Geometric Tessellation Background (Low Opacity) -->
    <div 
      class="absolute inset-0 opacity-[0.07] pointer-events-none"
      style="background-image: url(&quot;data:image/svg+xml,%3Csvg width='80' height='80' viewBox='0 0 80 80' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M40 0 L52 28 L80 40 L52 52 L40 80 L28 52 L0 40 L28 28 Z' fill='none' stroke='%23047857' stroke-width='1.2'/%3E%3Ccircle cx='40' cy='40' r='12' fill='none' stroke='%23FBBF24' stroke-width='0.8'/%3E%3Cpath d='M40 16 L44 36 L40 40 L36 36 Z' fill='none' stroke='%23047857' stroke-width='0.6'/%3E%3Cpath d='M64 40 L44 44 L40 40 L44 36 Z' fill='none' stroke='%23047857' stroke-width='0.6'/%3E%3Cpath d='M40 64 L36 44 L40 40 L44 44 Z' fill='none' stroke='%23047857' stroke-width='0.6'/%3E%3Cpath d='M16 40 L36 36 L40 40 L36 44 Z' fill='none' stroke='%23047857' stroke-width='0.6'/%3E%3C/svg%3E&quot;); background-repeat: repeat;"
    ></div>

    <!-- Large decorative emerald glow circle (top-right) -->
    <div class="absolute -top-32 -right-32 w-96 h-96 bg-emerald-500 rounded-full opacity-[0.06] blur-3xl"></div>
    <!-- Gold glow circle (bottom-left) -->
    <div class="absolute -bottom-32 -left-32 w-96 h-96 bg-amber-400 rounded-full opacity-[0.06] blur-3xl"></div>

    <!-- Login Card Container -->
    <div class="w-full max-w-md relative z-10">
      
      <!-- Logo & Brand Header -->
      <div class="text-center mb-8">
        <div v-if="$page.props.settings?.logo_path" class="inline-flex items-center justify-center mb-4">
          <img :src="`/storage/${$page.props.settings.logo_path}`" class="h-16 w-16 object-contain rounded-2xl shadow-md" alt="Logo" />
        </div>
        <div v-else class="inline-flex items-center justify-center w-16 h-16 bg-emerald-700 rounded-2xl shadow-lg mb-4">
          <!-- Book/Quran Icon -->
          <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-amber-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
          </svg>
        </div>
        <h1 class="text-2xl font-bold text-emerald-900 tracking-tight">{{ $page.props.settings?.app_title || 'Unit Pembinaan Anggota' }}</h1>
        <p class="text-sm text-gray-500 mt-1">Sistem Informasi Kajian & Mentoring</p>
      </div>

      <!-- Login Form Card -->
      <div class="bg-white rounded-2xl shadow-xl border border-emerald-100 overflow-hidden">
        
        <!-- Card Header with gradient -->
        <div class="bg-gradient-to-r from-emerald-800 to-emerald-950 px-6 py-5">
          <h2 class="text-lg font-bold text-white flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-amber-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
            </svg>
            Masuk ke Akun Anda
          </h2>
          <p class="text-xs text-emerald-200 mt-1">Gunakan kredensial yang telah diberikan oleh admin.</p>
        </div>

        <!-- Form Body -->
        <form @submit.prevent="submit" class="p-6 space-y-5">
          
          <!-- Success Flash (from registration) -->
          <div v-if="$page.props.flash?.success" class="bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs p-3 rounded-lg flex items-start gap-2.5">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-emerald-500 flex-shrink-0 mt-0.5" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
            </svg>
            <span class="leading-relaxed">{{ $page.props.flash.success }}</span>
          </div>

          <!-- Error Alert -->
          <div v-if="form.errors.email" class="bg-red-50 border border-red-200 text-red-700 text-xs p-3 rounded-lg flex items-start gap-2.5">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-500 flex-shrink-0 mt-0.5" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
            </svg>
            <span class="leading-relaxed">{{ form.errors.email }}</span>
          </div>

          <!-- Email Input -->
          <div class="space-y-1.5">
            <label for="email" class="text-xs font-bold text-gray-500 uppercase tracking-wider block">Alamat Email</label>
            <div class="relative">
              <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-gray-400">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
              </span>
              <input 
                type="email" 
                id="email" 
                v-model="form.email" 
                required
                autofocus
                autocomplete="email"
                placeholder="nama@upa.com"
                class="w-full border border-gray-200 rounded-lg p-2.5 pl-10 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-all"
                :class="form.errors.email ? 'border-red-300 bg-red-50' : ''"
              />
            </div>
            <span v-if="form.errors.email" class="text-xs text-red-600 font-medium">{{ form.errors.email }}</span>
          </div>

          <!-- Password Input -->
          <div class="space-y-1.5">
            <label for="password" class="text-xs font-bold text-gray-500 uppercase tracking-wider block">Kata Sandi</label>
            <div class="relative">
              <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-gray-400">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
              </span>
              <input 
                :type="showPassword ? 'text' : 'password'" 
                id="password" 
                v-model="form.password" 
                required
                autocomplete="current-password"
                placeholder="Masukkan kata sandi"
                class="w-full border border-gray-200 rounded-lg p-2.5 pl-10 pr-10 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-all"
                :class="form.errors.password ? 'border-red-300 bg-red-50' : ''"
              />
              <button
                type="button"
                @click="showPassword = !showPassword"
                class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-gray-400 hover:text-emerald-600 transition-colors"
                :title="showPassword ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'"
              >
                <!-- Eye open -->
                <svg v-if="!showPassword" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
                <!-- Eye closed -->
                <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                </svg>
              </button>
            </div>
            <span v-if="form.errors.password" class="text-xs text-red-600 font-medium">{{ form.errors.password }}</span>
          </div>

          <!-- Remember Me Toggle -->
          <div class="flex items-center justify-between">
            <label class="flex items-center space-x-2 cursor-pointer">
              <button 
                type="button"
                @click="form.remember = !form.remember"
                class="relative inline-flex h-5 w-9 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out outline-none"
                :class="form.remember ? 'bg-emerald-600' : 'bg-gray-300'"
              >
                <span 
                  class="pointer-events-none inline-block h-4 w-4 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                  :class="form.remember ? 'translate-x-4' : 'translate-x-0'"
                ></span>
              </button>
              <span class="text-xs text-gray-600 font-medium">Ingat saya</span>
            </label>
          </div>

          <!-- Submit Button -->
          <button 
            type="submit" 
            :disabled="form.processing"
            class="w-full bg-emerald-700 hover:bg-emerald-800 disabled:bg-emerald-400 text-white font-bold text-sm py-3 px-4 rounded-lg shadow-md hover:shadow-lg transition-all flex items-center justify-center gap-2"
          >
            <svg v-if="!form.processing" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-amber-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
            </svg>
            <!-- Loading spinner -->
            <svg v-else class="animate-spin h-5 w-5 text-amber-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span>{{ form.processing ? 'Memproses...' : 'Masuk ke Dashboard' }}</span>
          </button>
        </form>
      </div>

      <!-- Footer -->
      <div class="text-center mt-6">
        <p class="text-sm text-gray-500 mb-3">
          Belum punya akun?
          <a href="/register" class="text-emerald-700 hover:text-emerald-800 font-bold">Daftar di sini</a>
        </p>
        <p class="text-xs text-gray-400">
          &copy; 2026 Unit Pembinaan Anggota. Semua hak dilindungi.
        </p>
        <p class="text-[11px] text-gray-300 mt-1">
          Bismillahirrahmanirrahim
        </p>
      </div>
    </div>
  </div>
</template>
