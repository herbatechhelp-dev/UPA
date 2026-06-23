<script setup>
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({
  name: '',
  email: '',
  phone: '',
  gender: '',
  department: '',
  password: '',
  password_confirmation: '',
});

const showPassword = ref(false);
const showConfirmPassword = ref(false);

const submit = () => {
  form.post('/register');
};
</script>

<template>
  <Head title="Daftar Akun - Unit Pembinaan Anggota" />

  <div class="min-h-screen flex items-center justify-center bg-[#FAFAF9] relative overflow-hidden px-4 py-8">

    <!-- Islamic Geometric Background -->
    <div
      class="absolute inset-0 opacity-[0.07] pointer-events-none"
      style="background-image: url(&quot;data:image/svg+xml,%3Csvg width='80' height='80' viewBox='0 0 80 80' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M40 0 L52 28 L80 40 L52 52 L40 80 L28 52 L0 40 L28 28 Z' fill='none' stroke='%23047857' stroke-width='1.2'/%3E%3Ccircle cx='40' cy='40' r='12' fill='none' stroke='%23FBBF24' stroke-width='0.8'/%3E%3C/svg%3E&quot;); background-repeat: repeat;"
    ></div>

    <div class="absolute -top-32 -right-32 w-96 h-96 bg-emerald-500 rounded-full opacity-[0.06] blur-3xl"></div>
    <div class="absolute -bottom-32 -left-32 w-96 h-96 bg-amber-400 rounded-full opacity-[0.06] blur-3xl"></div>

    <div class="w-full max-w-md relative z-10">

      <!-- Logo & Brand Header -->
      <div class="text-center mb-6">
        <div v-if="$page.props.settings?.logo_path" class="inline-flex items-center justify-center mb-3">
          <img :src="`/storage/${$page.props.settings.logo_path}`" class="h-14 w-14 object-contain rounded-2xl shadow-md" alt="Logo" />
        </div>
        <div v-else class="inline-flex items-center justify-center w-14 h-14 bg-emerald-700 rounded-2xl shadow-lg mb-3">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-amber-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
          </svg>
        </div>
        <h1 class="text-xl font-bold text-emerald-900 tracking-tight">{{ $page.props.settings?.app_title || 'Unit Pembinaan Anggota' }}</h1>
        <p class="text-sm text-gray-500 mt-1">Daftar akun baru untuk bergabung</p>
        <div class="mt-4 flex items-center justify-center gap-3">
          <a href="/quran" class="inline-flex items-center gap-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-semibold px-3 py-1.5 rounded-full text-xs transition-all border border-emerald-150 shadow-sm">
            <span>📖</span> Al-Quran
          </a>
          <a href="/matsurat" class="inline-flex items-center gap-1.5 bg-amber-50 hover:bg-amber-100 text-amber-700 font-semibold px-3 py-1.5 rounded-full text-xs transition-all border border-amber-150 shadow-sm">
            <span>✨</span> Al-Ma'tsurat
          </a>
        </div>
      </div>

      <!-- Registration Form Card -->
      <div class="bg-white rounded-2xl shadow-xl border border-emerald-100 overflow-hidden">

        <div class="bg-gradient-to-r from-emerald-800 to-emerald-950 px-6 py-4">
          <h2 class="text-lg font-bold text-white flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-amber-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
            </svg>
            Pendaftaran Akun Baru
          </h2>
          <p class="text-xs text-emerald-200 mt-1">Isi data dengan benar. Admin akan meninjau pendaftaran Anda.</p>
        </div>

        <form @submit.prevent="submit" class="p-5 space-y-4">

          <!-- Error Alert -->
          <div v-if="Object.keys(form.errors).length" class="bg-red-50 border border-red-200 text-red-700 text-xs p-3 rounded-lg">
            <ul class="list-disc list-inside space-y-0.5">
              <li v-for="(error, key) in form.errors" :key="key">{{ error }}</li>
            </ul>
          </div>

          <!-- Nama Lengkap -->
          <div class="space-y-1">
            <label for="reg_name" class="text-xs font-bold text-gray-500 uppercase tracking-wider block">Nama Lengkap</label>
            <div class="relative">
              <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
              </span>
              <input type="text" id="reg_name" v-model="form.name" required autofocus placeholder="Nama lengkap Anda" class="w-full border border-gray-200 rounded-lg p-2.5 pl-10 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none" />
            </div>
          </div>

          <!-- Email -->
          <div class="space-y-1">
            <label for="reg_email" class="text-xs font-bold text-gray-500 uppercase tracking-wider block">Alamat Email</label>
            <div class="relative">
              <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
              </span>
              <input type="email" id="reg_email" v-model="form.email" required autocomplete="email" placeholder="nama@email.com" class="w-full border border-gray-200 rounded-lg p-2.5 pl-10 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none" />
            </div>
          </div>

          <!-- Nomor WhatsApp -->
          <div class="space-y-1">
            <label for="reg_phone" class="text-xs font-bold text-gray-500 uppercase tracking-wider block">Nomor WhatsApp</label>
            <div class="relative">
              <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" /></svg>
              </span>
              <input type="tel" id="reg_phone" v-model="form.phone" required placeholder="08xxxxxxxxxx" class="w-full border border-gray-200 rounded-lg p-2.5 pl-10 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none" />
            </div>
          </div>

          <!-- Jenis Kelamin + Departemen -->
          <div class="grid grid-cols-2 gap-3">
            <div class="space-y-1">
              <label for="reg_gender" class="text-xs font-bold text-gray-500 uppercase tracking-wider block">Jenis Kelamin</label>
              <select id="reg_gender" v-model="form.gender" required class="w-full border border-gray-200 rounded-lg p-2.5 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none bg-white">
                <option value="" disabled>Pilih...</option>
                <option value="ikhwan">Ikhwan (Laki-laki)</option>
                <option value="akhwat">Akhwat (Perempuan)</option>
              </select>
            </div>
            <div class="space-y-1">
              <label for="reg_dept" class="text-xs font-bold text-gray-500 uppercase tracking-wider block">Departemen</label>
              <input type="text" id="reg_dept" v-model="form.department" required placeholder="Departemen" class="w-full border border-gray-200 rounded-lg p-2.5 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none" />
            </div>
          </div>

          <!-- Password -->
          <div class="space-y-1">
            <label for="reg_password" class="text-xs font-bold text-gray-500 uppercase tracking-wider block">Kata Sandi</label>
            <div class="relative">
              <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
              </span>
              <input :type="showPassword ? 'text' : 'password'" id="reg_password" v-model="form.password" required minlength="8" autocomplete="new-password" placeholder="Minimal 8 karakter" class="w-full border border-gray-200 rounded-lg p-2.5 pl-10 pr-10 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none" />
              <button type="button" @click="showPassword = !showPassword" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-emerald-600 transition-colors">
                <svg v-if="!showPassword" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" /></svg>
              </button>
            </div>
          </div>

          <!-- Konfirmasi Password -->
          <div class="space-y-1">
            <label for="reg_password_confirm" class="text-xs font-bold text-gray-500 uppercase tracking-wider block">Konfirmasi Kata Sandi</label>
            <div class="relative">
              <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
              </span>
              <input :type="showConfirmPassword ? 'text' : 'password'" id="reg_password_confirm" v-model="form.password_confirmation" required autocomplete="new-password" placeholder="Ulangi kata sandi" class="w-full border border-gray-200 rounded-lg p-2.5 pl-10 pr-10 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none" />
              <button type="button" @click="showConfirmPassword = !showConfirmPassword" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-emerald-600 transition-colors">
                <svg v-if="!showConfirmPassword" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" /></svg>
              </button>
            </div>
          </div>

          <!-- Info -->
          <div class="bg-amber-50 border border-amber-200 rounded-lg p-3 text-xs text-amber-800 flex items-start gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            <span>Setelah mendaftar, akun Anda akan ditinjau oleh admin. Anda akan mendapat akses setelah disetujui.</span>
          </div>

          <!-- Submit -->
          <button type="submit" :disabled="form.processing" class="w-full bg-emerald-700 hover:bg-emerald-800 disabled:bg-emerald-400 text-white font-bold text-sm py-3 px-4 rounded-lg shadow-md hover:shadow-lg transition-all flex items-center justify-center gap-2">
            <svg v-if="!form.processing" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-amber-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" /></svg>
            <svg v-else class="animate-spin h-5 w-5 text-amber-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
            <span>{{ form.processing ? 'Mendaftarkan...' : 'Daftar Akun' }}</span>
          </button>
        </form>
      </div>

      <!-- Footer -->
      <div class="text-center mt-5">
        <p class="text-sm text-gray-500">
          Sudah punya akun?
          <a href="/login" class="text-emerald-700 hover:text-emerald-800 font-bold">Masuk di sini</a>
        </p>
        <p class="text-xs text-gray-400 mt-2">&copy; 2026 Unit Pembinaan Anggota</p>
      </div>
    </div>
  </div>
</template>
