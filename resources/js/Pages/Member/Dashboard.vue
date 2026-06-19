<script setup>
import { ref } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';

// Props passed from controller
const props = defineProps({
  auth: Object,
  mentoringGroup: Object,
  activeActivity: Object,
  attendances: Array,
  grades: Array,
  materials: Array,
  hasCheckedIn: Boolean
});

// States
const checkedIn = ref(props.hasCheckedIn);
const showSuccessNotification = ref(false);

const checkInForm = useForm({
  activity_id: props.activeActivity?.id || '',
  status: 'present'
});

const performCheckIn = () => {
  if (!props.activeActivity) {
    alert('Belum ada kajian aktif.');
    return;
  }
  checkInForm.activity_id = props.activeActivity.id;
  checkInForm.post('/attendances/check-in', {
    onSuccess: () => {
      checkedIn.value = true;
      showSuccessNotification.value = true;
      setTimeout(() => showSuccessNotification.value = false, 4000);
    }
  });
};

const handleLogout = () => {
  router.post('/logout');
};
</script>

<template>
  <Head title="Dashboard Anggota - Unit Pembinaan Anggota" />

  <div class="relative min-h-screen bg-[#FAFAF9] text-gray-800 font-sans pb-12">
    <!-- Islamic Watermark -->
    <div 
      class="absolute inset-0 opacity-[0.05] pointer-events-none"
      style="background-image: url(&quot;data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M30 0 L38 22 L60 30 L38 38 L30 60 L22 38 L0 30 L22 22 Z' fill='none' stroke='%23047857' stroke-width='1.5'/%3E%3Ccircle cx='30' cy='30' r='8' fill='none' stroke='%23FBBF24' stroke-width='1'/%3E%3C/svg%3E&quot;); background-repeat: repeat;"
    ></div>

    <!-- Navigation Header -->
    <nav class="sticky top-0 z-30 bg-white border-b border-emerald-100 shadow-sm">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-14 sm:h-16">
          <div class="flex items-center space-x-2 sm:space-x-3">
            <div v-if="$page.props.settings?.logo_path" class="flex-shrink-0">
              <img :src="`/storage/${$page.props.settings.logo_path}`" class="h-8 w-8 sm:h-10 sm:w-10 object-contain rounded-xl" alt="Logo" />
            </div>
            <div v-else class="bg-emerald-700 text-amber-300 p-1.5 sm:p-2 rounded-xl shadow-md">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 sm:h-6 sm:w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
              </svg>
            </div>
            <div>
              <span class="text-base sm:text-lg font-bold text-emerald-800 tracking-wide block">{{ $page.props.settings?.app_title || 'Portal Anggota' }}</span>
              <span class="hidden sm:block text-xs text-emerald-600 font-medium">Unit Pembinaan Anggota (UPA)</span>
            </div>
          </div>
          <div class="flex items-center space-x-2 sm:space-x-4">
            <div class="bg-emerald-50 px-2 sm:px-3 py-1.5 rounded-full border border-emerald-100 text-xs font-semibold text-emerald-800 max-w-[120px] sm:max-w-none overflow-hidden">
              <span class="truncate block">{{ (auth?.name || auth?.user?.name || 'Akh Salman').split(' ').slice(0,2).join(' ') }}</span>
            </div>
            <a
              href="/quran"
              class="text-emerald-600 hover:text-emerald-800 font-semibold transition-colors flex items-center space-x-1 p-1"
              title="Al-Quran"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
              </svg>
              <span class="hidden sm:inline text-xs">Al-Quran</span>
            </a>
            <button 
              @click="handleLogout"
              class="text-gray-500 hover:text-red-700 font-semibold transition-colors flex items-center space-x-1 p-1"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
              </svg>
              <span class="hidden sm:inline text-xs">Logout</span>
            </button>
          </div>
        </div>
      </div>
    </nav>

    <!-- Main Container -->
    <main class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 mt-4 sm:mt-8 relative z-10">
      
      <!-- Welcome Header & Check-in Switcher -->
      <div class="bg-gradient-to-r from-emerald-800 to-emerald-950 text-white rounded-2xl p-4 sm:p-6 shadow-xl mb-5 sm:mb-8 border border-emerald-700 relative overflow-hidden flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div class="flex-1">
          <h1 class="text-xl sm:text-2xl font-bold tracking-tight">Ahlan wa Sahlan, Akh {{ (auth?.name || auth?.user?.name || 'Salman').split(' ').slice(0,3).join(' ') }}</h1>
          <p class="text-emerald-100 mt-1 text-xs sm:text-sm max-w-lg hidden sm:block">
            Pantau kehadiran, nilai perkembangan bulanan dari Ustad, serta unduh materi kajian terbaru untuk membantumu ber-mutaba'ah secara istiqomah.
          </p>
        </div>

        <!-- Check-in Board -->
        <div class="w-full sm:w-auto bg-white/10 backdrop-blur-md border border-white/20 p-3 sm:p-4 rounded-xl flex items-center justify-between sm:justify-start space-x-3 flex-shrink-0">
          <div>
            <span class="text-[10px] sm:text-xs text-emerald-200 block font-semibold uppercase tracking-wider">Absen Mandiri Anggota</span>
            <span class="text-xs text-amber-300 font-bold block max-w-[180px] sm:max-w-[140px] truncate">{{ activeActivity?.topic || 'Belum ada kajian aktif' }}</span>
          </div>
          <div v-if="!checkedIn" class="flex items-center space-x-2">
            <select v-model="checkInForm.status" class="bg-emerald-950/70 border border-emerald-800 text-white text-xs rounded-lg p-1.5 focus:ring-1 focus:ring-amber-400 outline-none">
              <option value="present">Hadir</option>
              <option value="sick">Sakit</option>
              <option value="permission">Izin</option>
            </select>
            <button 
              @click="performCheckIn"
              class="bg-amber-400 hover:bg-amber-500 text-emerald-950 font-bold text-xs py-2.5 px-3 sm:px-4 rounded-lg shadow-sm transition-all uppercase tracking-wider flex-shrink-0"
            >
              Check-In
            </button>
          </div>
          <span 
            v-else 
            class="bg-emerald-600/50 border border-emerald-500 text-emerald-100 text-xs font-bold px-2 sm:px-3 py-1.5 rounded-lg inline-flex items-center space-x-1 flex-shrink-0"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
            </svg>
            <span>Berhasil</span>
          </span>
        </div>
      </div>

      <!-- Main Layout Details Grid -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Column 1: Materials List -->
        <div class="lg:col-span-1 space-y-6">
          
          <!-- Halaqah Info Card -->
          <div class="bg-white rounded-xl shadow-sm border border-emerald-100 p-6">
            <h2 class="text-base font-bold text-emerald-950 mb-4 pb-2 border-b border-emerald-50 flex items-center gap-2">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-emerald-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
              </svg>
              Informasi Halaqah
            </h2>
            <div class="space-y-4">
              <div>
                <span class="text-[10px] text-gray-400 block uppercase font-bold tracking-wider">Nama Halaqah</span>
                <span class="text-sm font-bold text-emerald-800">{{ mentoringGroup?.name || 'Belum Terplot' }}</span>
              </div>
              <div>
                <span class="text-[10px] text-gray-400 block uppercase font-bold tracking-wider">Ustad Pembina</span>
                <span class="text-sm font-bold text-gray-900">{{ mentoringGroup?.ustad?.name || '—' }}</span>
              </div>
              <div>
                <span class="text-[10px] text-gray-400 block uppercase font-bold tracking-wider">Ketua Kelompok</span>
                <span class="text-sm font-bold text-gray-900">{{ mentoringGroup?.leader?.name || '—' }}</span>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-xl shadow-sm border border-emerald-100 p-6">
            <h2 class="text-base font-bold text-emerald-950 mb-4 pb-2 border-b border-emerald-50 flex items-center gap-2">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-emerald-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
              </svg>
              Unduh Materi Kajian
            </h2>
            <div class="space-y-3">
              <div v-for="material in materials" :key="material.id" class="p-4 rounded-xl border border-gray-100 hover:border-emerald-100 bg-white shadow-sm flex flex-col justify-between">
                <div>
                  <h3 class="font-bold text-gray-900 text-sm">{{ material.title }}</h3>
                  <span class="text-[11px] text-gray-400 block mt-1">Ukuran File: {{ material.size }}</span>
                </div>
                <a 
                  v-if="material.file_path"
                  :href="material.file_path"
                  class="mt-3 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-100 font-semibold text-xs py-2 px-3 rounded-lg text-center flex items-center justify-center space-x-1.5 transition-colors"
                >
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                  </svg>
                  <span>Unduh Materi</span>
                </a>
                <span v-else class="text-xs text-gray-400 mt-2 block">Teks Saja</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Column 2 & 3: History & Personal Grades -->
        <div class="lg:col-span-2 space-y-8">
          
          <!-- Attendance History Card -->
          <div class="bg-white rounded-xl shadow-sm border border-emerald-100 p-6">
            <h2 class="text-base font-bold text-emerald-950 mb-4 pb-2 border-b border-emerald-50 flex items-center gap-2">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-emerald-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
              </svg>
              Histori Absensi Anda
            </h2>
            <!-- Mobile view: cards list -->
            <div class="block sm:hidden space-y-3">
              <div v-for="att in attendances" :key="att.id" class="p-4 rounded-xl border border-gray-150 bg-[#FAFAF9]/40 space-y-2">
                <div class="flex justify-between items-start gap-2">
                  <span class="font-bold text-gray-950 text-sm block leading-snug">{{ att.topic }}</span>
                  <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full inline-block flex-shrink-0"
                    :class="{
                      'bg-emerald-100 text-emerald-800': att.status === 'present',
                      'bg-amber-100 text-amber-800': att.status === 'sick',
                      'bg-blue-100 text-blue-800': att.status === 'permission',
                      'bg-red-100 text-red-800': att.status === 'absent'
                    }">
                    {{ att.status === 'present' ? 'Hadir' : att.status === 'sick' ? 'Sakit' : att.status === 'permission' ? 'Izin' : 'Alpa' }}
                  </span>
                </div>
                <div class="flex justify-between items-center text-[11px] text-gray-500">
                  <span>{{ att.date }}</span>
                  <span>Verifikator: <strong class="text-emerald-700">{{ att.approver }}</strong></span>
                </div>
              </div>
            </div>

            <!-- Desktop view: table -->
            <div class="hidden sm:block overflow-x-auto">
              <table class="w-full text-left text-sm border-collapse">
                <thead>
                  <tr class="bg-gray-50/50 text-xs font-bold text-gray-500 uppercase tracking-wider border-b border-gray-100">
                    <th class="py-3 px-4">Topik Kajian</th>
                    <th class="py-3 px-4 text-center">Tanggal</th>
                    <th class="py-3 px-4 text-center">Status</th>
                    <th class="py-3 px-4">Verifikator</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                  <tr v-for="att in attendances" :key="att.id" class="hover:bg-gray-50/30">
                    <td class="py-3 px-4 font-bold text-gray-900">{{ att.topic }}</td>
                    <td class="py-3 px-4 text-center text-gray-500 text-xs">{{ att.date }}</td>
                    <td class="py-3 px-4 text-center">
                      <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full inline-block"
                        :class="{
                          'bg-emerald-100 text-emerald-800': att.status === 'present',
                          'bg-amber-100 text-amber-800': att.status === 'sick',
                          'bg-blue-100 text-blue-800': att.status === 'permission',
                          'bg-red-100 text-red-800': att.status === 'absent'
                        }">
                        {{ att.status === 'present' ? 'Hadir' : att.status === 'sick' ? 'Sakit' : att.status === 'permission' ? 'Izin' : 'Alpa' }}
                      </span>
                    </td>
                    <td class="py-3 px-4 text-gray-500 text-xs">{{ att.approver }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Personal Grade Card -->
          <div class="bg-white rounded-xl shadow-sm border border-emerald-100 p-6">
            <h2 class="text-base font-bold text-emerald-950 mb-4 pb-2 border-b border-emerald-50 flex items-center gap-2">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-emerald-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
              </svg>
              Nilai Perkembangan Bulanan (Ustad)
            </h2>
            <div class="space-y-4">
              <div v-for="grade in grades" :key="grade.id" class="p-5 border border-gray-100 rounded-xl bg-[#FAFAF9]/40 hover:shadow-sm transition-shadow">
                <div class="flex justify-between items-start gap-4">
                  <div>
                    <span class="text-xs text-emerald-700 font-bold block uppercase tracking-wider">Periode Penilaian</span>
                    <h3 class="font-bold text-gray-900 text-sm mt-0.5">{{ grade.month }} {{ grade.year }}</h3>
                  </div>
                  <div class="bg-emerald-800 text-white font-bold text-sm px-3.5 py-1.5 rounded-lg shadow-sm border border-emerald-700 flex items-center gap-1">
                    <span class="text-[10px] text-amber-300">Skor:</span>
                    <span>{{ grade.score }}</span>
                  </div>
                </div>
                <div class="mt-4 pt-3 border-t border-dashed border-gray-150">
                  <span class="text-[10px] text-gray-400 block uppercase font-bold tracking-wider">Catatan Ustad Pembina</span>
                  <p class="text-xs text-gray-600 mt-1 italic leading-relaxed">
                    "{{ grade.notes }}"
                  </p>
                </div>
              </div>
            </div>
          </div>

        </div>

      </div>
    </main>

    <!-- Toast Success Notification -->
    <div 
      v-if="showSuccessNotification" 
      class="fixed bottom-5 left-4 right-4 sm:left-auto sm:right-5 sm:w-auto z-50 bg-emerald-950 text-white py-3 px-4 sm:px-5 rounded-xl shadow-2xl flex items-center space-x-3 border border-emerald-800 animate-slide-in"
    >
      <div class="p-1 bg-amber-400 text-emerald-950 rounded-full flex-shrink-0">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
        </svg>
      </div>
      <span class="text-xs sm:text-sm font-semibold">Check-in berhasil! Data kehadiran Anda terkirim ke Ustad.</span>
    </div>

  </div>
</template>

<style scoped>
@keyframes slide-in {
  from {
    transform: translateY(20px);
    opacity: 0;
  }
  to {
    transform: translateY(0);
    opacity: 1;
  }
}
.animate-slide-in {
  animation: slide-in 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}
</style>
