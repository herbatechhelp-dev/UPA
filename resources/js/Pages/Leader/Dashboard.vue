<script setup>
import { ref } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';

// Props passed from controller
const props = defineProps({
  auth: Object,
  group: Object,
  activeActivity: Object,
  materials: Array,
  hasCheckedIn: Boolean
});

// States
const personalCheckedIn = ref(props.hasCheckedIn);
const showSuccessNotification = ref(false);
const notificationMessage = ref('');


// Form for self check-in
const checkInForm = useForm({
  activity_id: props.activeActivity?.id || '',
  status: 'present'
});

const selfCheckIn = () => {
  if (!props.activeActivity) {
    alert('Belum ada kajian aktif yang terbit.');
    return;
  }
  checkInForm.activity_id = props.activeActivity.id;
  checkInForm.post('/attendances/check-in', {
    onSuccess: () => {
      personalCheckedIn.value = true;
      notificationMessage.value = 'Absensi mandiri Anda berhasil dicatat!';
      showSuccessNotification.value = true;
      setTimeout(() => showSuccessNotification.value = false, 4000);
    }
  });
};

const approveMember = (member) => {
  if (!props.activeActivity) return;
  router.post(`/activities/${props.activeActivity.id}/attendances/${member.id}/approve`, {}, {
    onSuccess: () => {
      notificationMessage.value = `Kehadiran ${member.name} berhasil disetujui!`;
      showSuccessNotification.value = true;
      setTimeout(() => showSuccessNotification.value = false, 4000);
    }
  });
};

const rejectMember = (member) => {
  if (!props.activeActivity) return;
  if (confirm(`Apakah Anda yakin ingin menolak/menghapus kehadiran ${member.name}?`)) {
    router.post(`/activities/${props.activeActivity.id}/attendances/${member.id}/reject`, {}, {
      onSuccess: () => {
        notificationMessage.value = `Kehadiran ${member.name} berhasil ditolak!`;
        showSuccessNotification.value = true;
        setTimeout(() => showSuccessNotification.value = false, 4000);
      }
    });
  }
};

const handleLogout = () => {
  router.post('/logout');
};
</script>

<template>
  <Head title="Dashboard Ketua Kelompok - Unit Pembinaan Anggota" />

  <div class="relative min-h-screen bg-[#FAFAF9] text-gray-800 font-sans pb-12">
    <!-- Islamic Watermark -->
    <div 
      class="absolute inset-0 opacity-[0.05] pointer-events-none"
      style="background-image: url(&quot;data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M30 0 L38 22 L60 30 L38 38 L30 60 L22 38 L0 30 L22 22 Z' fill='none' stroke='%23047857' stroke-width='1.5'/%3E%3Ccircle cx='30' cy='30' r='8' fill='none' stroke='%23FBBF24' stroke-width='1'/%3E%3C/svg%3E&quot;); background-repeat: repeat;"
    ></div>

    <!-- Navigation Bar -->
    <nav class="sticky top-0 z-30 bg-white border-b border-emerald-100 shadow-sm">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-14 sm:h-16">
          <div class="flex items-center space-x-2 sm:space-x-3">
            <div v-if="$page.props.settings?.logo_path" class="flex-shrink-0">
              <img :src="`/storage/${$page.props.settings.logo_path}`" class="h-8 w-8 sm:h-10 sm:w-10 object-contain rounded-xl" alt="Logo" />
            </div>
            <div v-else class="bg-emerald-700 text-amber-300 p-1.5 sm:p-2 rounded-xl shadow-md">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 sm:h-6 sm:w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
              </svg>
            </div>
            <div>
              <span class="text-base sm:text-lg font-bold text-emerald-800 tracking-wide block">{{ $page.props.settings?.app_title || 'Ketua Kelompok' }}</span>
              <span class="hidden sm:block text-xs text-emerald-600 font-medium">{{ group?.name || 'Kelompok Mentoring' }}</span>
            </div>
          </div>
          <div class="flex items-center space-x-2 sm:space-x-4">
            <div class="bg-emerald-50 px-2 sm:px-3 py-1.5 rounded-full border border-emerald-100 text-xs font-semibold text-emerald-800 max-w-[120px] sm:max-w-none overflow-hidden">
              <span class="truncate block">{{ (auth?.name || auth?.user?.name || 'Ahmad Bukhari').split(' ').slice(0,2).join(' ') }}</span>
            </div>
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

    <!-- Main Content Container -->
    <main class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 mt-4 sm:mt-8 relative z-10">
      
      <!-- Welcome Header & Self Check-in -->
      <div v-if="group" class="bg-gradient-to-r from-emerald-800 to-emerald-950 text-white rounded-2xl p-4 sm:p-6 shadow-xl mb-5 sm:mb-8 border border-emerald-700 relative overflow-hidden flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div class="flex-1">
          <h1 class="text-xl sm:text-2xl font-bold tracking-tight">Assalamu'alaikum, Akh {{ (auth?.name || auth?.user?.name || 'Ahmad Bukhari').split(' ').slice(0,3).join(' ') }}</h1>
          <p class="text-emerald-100 mt-1 text-xs sm:text-sm max-w-lg hidden sm:block">
            Gunakan panel ini untuk melakukan absensi mandiri, meninjau materi kajian, serta memvalidasi kehadiran anggota kelompok apabila didelegasikan oleh Ustad Pembina.
          </p>
        </div>
        
        <!-- Personal Check-in Button -->
        <div class="bg-white/10 backdrop-blur-md border border-white/20 p-3 sm:p-4 rounded-xl flex items-center space-x-3 flex-shrink-0">
          <div>
            <span class="text-[10px] sm:text-xs text-emerald-200 block font-semibold uppercase tracking-wider">Absen Mandiri Anda</span>
            <span class="text-xs text-amber-300 font-bold block max-w-[140px] truncate">{{ activeActivity?.topic || 'Belum ada kajian aktif' }}</span>
          </div>
          <div v-if="!personalCheckedIn" class="flex items-center space-x-2">
            <select v-model="checkInForm.status" class="bg-emerald-950/70 border border-emerald-800 text-white text-xs rounded-lg p-1.5 focus:ring-1 focus:ring-amber-400 outline-none">
              <option value="present">Hadir</option>
              <option value="sick">Sakit</option>
              <option value="permission">Izin</option>
            </select>
            <button 
              @click="selfCheckIn"
              class="bg-amber-400 hover:bg-amber-500 text-emerald-950 font-bold text-xs py-2 px-3 rounded-lg shadow-sm transition-all uppercase tracking-wider flex-shrink-0"
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
            <span>Sudah Absen</span>
          </span>
        </div>
      </div>

      <!-- Main Columns Grid -->
      <div v-if="group" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Left Panel: Materials List -->
        <div :class="group.is_delegated ? 'lg:col-span-1' : 'lg:col-span-3'" class="space-y-6">
          <div class="bg-white rounded-xl shadow-sm border border-emerald-100 p-6">
            <h2 class="text-base font-bold text-emerald-950 mb-4 pb-2 border-b border-emerald-50 flex items-center gap-2">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-emerald-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
              </svg>
              Materi Kajian Terbaru
            </h2>

            <div :class="group.is_delegated ? 'space-y-3' : 'grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4'">
              <div 
                v-for="material in materials" 
                :key="material.id" 
                class="p-4 rounded-xl border border-gray-100 hover:border-emerald-100 bg-white shadow-sm flex flex-col justify-between"
              >
                <div>
                  <h3 class="font-bold text-gray-900 text-sm">{{ material.title }}</h3>
                  <span class="text-[11px] text-gray-400 block mt-1">Dipublikasi: {{ material.date }}</span>
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

        <!-- Right Panel: Delegated Attendance Check -->
        <div v-if="group.is_delegated" class="lg:col-span-2 space-y-6">
          <div class="bg-white rounded-xl shadow-sm border border-emerald-100 overflow-hidden">
            
            <!-- Conditional Header: Delegation Active -->
            <div 
              class="p-6 border-b flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-amber-50/50 border-amber-100"
            >
              <div>
                <!-- Status Badge -->
                <div class="inline-flex items-center space-x-1.5 mb-1.5">
                  <span class="h-2.5 w-2.5 rounded-full bg-amber-500 animate-pulse"></span>
                  <span class="text-xs font-bold uppercase tracking-wider text-amber-800">
                    Delegasi Approval Aktif
                  </span>
                </div>

                <h2 class="text-lg font-bold text-gray-900">Validasi Absensi Halaqah</h2>
                <span class="text-xs text-gray-500 block mt-1">Sesi: <strong>{{ activeActivity?.topic || 'Belum ada kajian aktif' }}</strong></span>
              </div>

              <!-- Delegation Time Limit Badge -->
              <span v-if="group.delegated_until" class="text-xs bg-amber-100 text-amber-800 px-3 py-1 rounded-full font-semibold border border-amber-250">
                Berlaku s/d {{ new Date(group.delegated_until).toLocaleDateString('id-ID', {day: 'numeric', month: 'short'}) }}
              </span>
            </div>

            <!-- Form layout if delegation active -->
            <div class="divide-y divide-gray-100">
              <div class="divide-y divide-gray-100">
                <div v-for="member in group.members" :key="member.id" class="p-4 sm:px-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 hover:bg-gray-50/30 transition-colors">
                  <div class="font-semibold text-gray-950 text-sm flex items-center gap-2">
                    <div class="h-7 w-7 rounded-full bg-emerald-700/10 text-emerald-800 font-bold flex items-center justify-center text-xs">
                      {{ member.name.replace('Akh ', '').charAt(0) }}
                    </div>
                    <span>{{ member.name }}</span>
                  </div>
                  
                  <div class="flex items-center space-x-3 justify-between sm:justify-end">
                    <!-- Status Submitted by User -->
                    <div>
                      <span v-if="!member.status" class="bg-gray-100 text-gray-500 text-xs font-semibold px-2.5 py-1 rounded-lg border border-gray-200">
                        Belum Check-in
                      </span>
                      <span v-else-if="member.status === 'present'" class="bg-emerald-50 text-emerald-700 text-xs font-bold px-2.5 py-1 rounded-lg border border-emerald-200">
                        Hadir
                      </span>
                      <span v-else-if="member.status === 'sick'" class="bg-amber-50 text-amber-700 text-xs font-bold px-2.5 py-1 rounded-lg border border-amber-200">
                        Sakit
                      </span>
                      <span v-else-if="member.status === 'permission'" class="bg-blue-50 text-blue-700 text-xs font-bold px-2.5 py-1 rounded-lg border border-blue-200">
                        Izin
                      </span>
                      <span v-else-if="member.status === 'absent'" class="bg-red-50 text-red-700 text-xs font-bold px-2.5 py-1 rounded-lg border border-red-200">
                        Alpa
                      </span>
                    </div>

                    <!-- Approval state & Action buttons -->
                    <div v-if="member.status" class="flex items-center space-x-2">
                      <span v-if="member.is_approved" class="text-xs bg-emerald-700 text-white font-bold px-2 py-1 rounded-lg flex items-center space-x-0.5 shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                        </svg>
                        <span>Disetujui</span>
                      </span>
                      
                      <button 
                        v-if="!member.is_approved"
                        type="button" 
                        @click="approveMember(member)"
                        class="bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-200 text-xs font-bold py-1 px-2.5 rounded-lg shadow-sm transition-colors"
                      >
                        Setujui
                      </button>
                      <button 
                        type="button" 
                        @click="rejectMember(member)"
                        class="bg-red-50 hover:bg-red-100 text-red-700 border border-red-200 text-xs font-bold py-1 px-2.5 rounded-lg shadow-sm transition-colors"
                      >
                        Tolak
                      </button>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Info Section -->
              <div class="p-6 bg-gray-50/50">
                <span class="text-[11px] text-gray-500 block">
                  ℹ️ Validasi absensi dilakukan dengan meninjau status check-in mandiri anggota kelompok, lalu menyetujui (Setujui) atau menolak (Tolak). Menolak akan menghapus log check-in anggota tersebut.
                </span>
              </div>
            </div>
          </div>
        </div>

      </div>
      <div v-else class="text-center p-12 bg-white rounded-2xl shadow-sm border border-emerald-100">
        <p class="text-gray-500">Anda belum di-plotting sebagai Ketua Kelompok di halaqah manapun.</p>
      </div>
    </main>

    <!-- Toast Success Notification -->
    <div 
      v-if="showSuccessNotification" 
      class="fixed bottom-5 left-4 right-4 sm:left-auto sm:right-5 sm:w-auto z-50 bg-emerald-950 text-white py-3 px-4 sm:px-5 rounded-xl shadow-2xl flex items-center space-x-3 border border-emerald-800 animate-slide-in"
    >
      <div class="p-1 bg-amber-400 text-emerald-950 rounded-full flex-shrink-0">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
        </svg>
      </div>
      <span class="text-xs sm:text-sm font-semibold">{{ notificationMessage }}</span>
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
