<script setup>
import { ref, onMounted, watch } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';

// Props passed from controller
const props = defineProps({
  auth: Object,
  group: Object,
  activeActivity: Object,
  materials: Array,
  hasCheckedIn: Boolean,
  activities: Array
});

// States
const personalCheckedIn = ref(props.hasCheckedIn);
const showSuccessNotification = ref(false);
const notificationMessage = ref('');
const showCreateSessionModal = ref(false);
const showDetailModal = ref(false);
const selectedActivityForDetail = ref(null);

const openDetailModal = (activity) => {
  selectedActivityForDetail.value = activity;
  showDetailModal.value = true;
};

// Form for creating new session + attendance
const sessionForm = useForm({
  group_id: props.group?.id || '',
  date: '',
  topic: '',
  description: '',
  attendances: []
});

const openCreateSessionModal = () => {
  sessionForm.reset();
  sessionForm.group_id = props.group?.id || '';
  
  // Set default date to local timezone formatted for datetime-local (YYYY-MM-DDTHH:MM)
  const now = new Date();
  const tzOffset = now.getTimezoneOffset() * 60000;
  const localISOTime = (new Date(now - tzOffset)).toISOString().slice(0, 16);
  sessionForm.date = localISOTime;
  
  if (props.group && props.group.members) {
    sessionForm.attendances = props.group.members.map(member => ({
      user_id: member.id,
      name: member.name,
      status: 'present'
    }));
  }
  showCreateSessionModal.value = true;
};

const submitCreateSession = () => {
  sessionForm.post('/activities', {
    onSuccess: () => {
      showCreateSessionModal.value = false;
      notificationMessage.value = 'Sesi kajian & presensi berhasil diajukan!';
      showSuccessNotification.value = true;
      setTimeout(() => showSuccessNotification.value = false, 4000);
      router.reload({ only: ['group', 'activeActivity'] });
      fetchWebRecapData();
    }
  });
};

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

// Form for bulk attendance input by Group Leader
const groupAttendanceForm = useForm({
  attendances: []
});

const initAttendanceForm = () => {
  if (props.group && props.group.members) {
    groupAttendanceForm.attendances = props.group.members.map(member => ({
      user_id: member.id,
      status: member.status || 'present'
    }));
  }
};

const getMemberStatus = (userId) => {
  const record = groupAttendanceForm.attendances.find(a => a.user_id === userId);
  return record ? record.status : 'present';
};

const updateMemberStatus = (userId, status) => {
  const record = groupAttendanceForm.attendances.find(a => a.user_id === userId);
  if (record) {
    record.status = status;
  }
};

const submitGroupAttendance = () => {
  if (!props.activeActivity) return;
  groupAttendanceForm.post(`/activities/${props.activeActivity.id}/attendances/approve`, {
    onSuccess: () => {
      notificationMessage.value = 'Absensi kelompok berhasil disimpan!';
      showSuccessNotification.value = true;
      setTimeout(() => showSuccessNotification.value = false, 4000);
      fetchWebRecapData(); // Refresh the monthly recap web view
    }
  });
};

const recapForm = ref({
  month: new Date().getMonth() + 1,
  year: new Date().getFullYear()
});

const downloadRecap = () => {
  const url = `/reports/download?group_id=${props.group?.id}&month=${recapForm.value.month}&year=${recapForm.value.year}&type=attendance_monthly`;
  window.open(url, '_blank');
};

const handleLogout = () => {
  router.post('/logout');
};

const webRecapFilter = ref({
  month: new Date().getMonth() + 1,
  year: new Date().getFullYear()
});
const isWebRecapLoading = ref(false);
const webRecapData = ref(null);

const fetchWebRecapData = async () => {
  if (!props.group) return;
  isWebRecapLoading.value = true;
  webRecapData.value = null;
  try {
    const response = await fetch(`/reports/recap-data?group_id=${props.group.id}&month=${webRecapFilter.value.month}&year=${webRecapFilter.value.year}`);
    if (response.ok) {
      webRecapData.value = await response.json();
    } else {
      const err = await response.json();
      alert(err.error || 'Gagal memuat rekap absensi.');
    }
  } catch (error) {
    console.error(error);
    alert('Terjadi kesalahan koneksi.');
  } finally {
    isWebRecapLoading.value = false;
  }
};

onMounted(() => {
  initAttendanceForm();
  fetchWebRecapData();
});

watch(() => props.group?.members, () => {
  initAttendanceForm();
}, { deep: true });
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

    <!-- Main Content Container -->
    <main class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 mt-4 sm:mt-8 relative z-10">
      
      <!-- Welcome Header & Self Check-in -->
      <div v-if="group" class="bg-gradient-to-r from-emerald-800 to-emerald-950 text-white rounded-2xl p-4 sm:p-6 shadow-xl mb-5 sm:mb-8 border border-emerald-700 relative overflow-hidden flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div class="flex-1">
          <h1 class="text-xl sm:text-2xl font-bold tracking-tight">Assalamu'alaikum, Akh {{ (auth?.name || auth?.user?.name || 'Ahmad Bukhari').replace(/^Akh\s+/i, '').split(' ').slice(0,3).join(' ') }}</h1>
          <p class="text-emerald-100 mt-1 text-xs sm:text-sm max-w-lg hidden sm:block">
            Gunakan panel ini untuk melakukan absensi mandiri, meninjau materi kajian, serta memvalidasi kehadiran anggota kelompok apabila didelegasikan oleh Ustad Pembina.
          </p>
          <div class="mt-3 flex flex-wrap items-center gap-x-4 gap-y-1.5 text-[11px] text-emerald-200">
            <span class="flex items-center gap-1 bg-emerald-900/50 px-2 py-0.5 rounded border border-emerald-700/50">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
              </svg>
              Kelompok: <strong class="text-white ml-0.5">{{ group.name }}</strong>
            </span>
            <span v-if="group.ustad?.name" class="flex items-center gap-1 bg-emerald-900/50 px-2 py-0.5 rounded border border-emerald-700/50">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
              </svg>
              Pembina: <strong class="text-white ml-0.5">{{ group.ustad.name }}</strong>
            </span>
          </div>
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
        <div class="lg:col-span-3 space-y-6">
          <!-- Top Row: Session Creation and Download Recap side-by-side -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Card: Buat Sesi & Presensi Baru -->
            <div class="bg-white rounded-xl shadow-sm border border-emerald-100 p-6 flex flex-col justify-between">
              <div>
                <h2 class="text-base font-bold text-emerald-950 mb-3 pb-2 border-b border-emerald-50 flex items-center gap-2">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-emerald-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                  </svg>
                  Sesi Halaqah & Presensi
                </h2>
                <p class="text-xs text-gray-500 mb-4 font-medium">
                  Buat sesi kajian halaqah baru sekaligus mengisi daftar kehadiran seluruh mutarabbi kelompok Anda.
                </p>
              </div>
              <button 
                @click="openCreateSessionModal"
                class="w-full bg-emerald-700 hover:bg-emerald-800 text-white font-bold text-xs py-2.5 px-4 rounded-lg shadow-md transition-colors flex items-center justify-center gap-1.5 mt-auto"
              >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-amber-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>Buat Sesi & Presensi Baru</span>
              </button>
            </div>

            <!-- Rekap Absensi Bulanan Card -->
            <div class="bg-white rounded-xl shadow-sm border border-emerald-100 p-6 flex flex-col justify-between">
              <div>
                <h2 class="text-base font-bold text-emerald-950 mb-3 pb-2 border-b border-emerald-50 flex items-center gap-2">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-emerald-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                  </svg>
                  Unduh Rekap Absensi Bulanan
                </h2>
                <p class="text-xs text-gray-500 mb-4 font-medium">Unduh rekapitulasi kehadiran bulanan seluruh anggota kelompok dalam format CSV.</p>
                
                <div class="grid grid-cols-2 gap-3 mb-4">
                  <div>
                    <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block mb-1">Bulan</label>
                    <select v-model="recapForm.month" class="w-full border border-gray-250 rounded-lg p-2.5 text-xs focus:ring-1 focus:ring-emerald-500 outline-none shadow-sm">
                      <option v-for="m in 12" :key="m" :value="m">
                        {{ new Date(2026, m-1, 1).toLocaleString('id-ID', { month: 'long' }) }}
                      </option>
                    </select>
                  </div>
                  <div>
                    <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block mb-1">Tahun</label>
                    <select v-model="recapForm.year" class="w-full border border-gray-250 rounded-lg p-2.5 text-xs focus:ring-1 focus:ring-emerald-500 outline-none shadow-sm">
                      <option v-for="y in [2025, 2026, 2027]" :key="y" :value="y">{{ y }}</option>
                    </select>
                  </div>
                </div>
              </div>
              
              <button 
                @click="downloadRecap"
                class="w-full bg-emerald-700 hover:bg-emerald-800 text-white font-bold text-xs py-2.5 px-4 rounded-lg shadow-md transition-colors flex items-center justify-center gap-1.5 mt-auto"
              >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-amber-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                <span>Unduh Laporan</span>
              </button>
            </div>
          </div>

          <!-- Card: Riwayat Sesi Kajian & Status Pengajuan Absensi -->
          <div class="bg-white rounded-xl shadow-sm border border-emerald-100 p-6">
            <h2 class="text-base font-bold text-emerald-950 mb-4 pb-2 border-b border-emerald-50 flex items-center gap-2">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-emerald-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 002 2h2a2 2 0 002-2" />
              </svg>
              Riwayat Sesi Kajian & Status Pengajuan Absensi
            </h2>

            <div class="overflow-x-auto rounded-lg border border-gray-150">
              <table class="w-full text-left border-collapse text-xs">
                <thead>
                  <tr class="bg-gray-50 font-bold text-gray-500 uppercase tracking-wider border-b border-gray-150">
                    <th class="py-3 px-4 w-12">No</th>
                    <th class="py-3 px-4">Tanggal & Waktu</th>
                    <th class="py-3 px-4">Topik Kajian</th>
                    <th class="py-3 px-4">Kehadiran Anggota</th>
                    <th class="py-3 px-4 text-center">Status Approval</th>
                    <th class="py-3 px-4 text-center">Aksi</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                  <tr v-for="(act, index) in activities" :key="act.id" class="hover:bg-gray-50/40">
                    <td class="py-3 px-4 font-semibold text-gray-900">{{ index + 1 }}</td>
                    <td class="py-3 px-4 font-semibold text-gray-900">{{ act.date_human }}</td>
                    <td class="py-3 px-4 font-bold text-emerald-900">
                      {{ act.topic }}
                      <span v-if="act.description" class="block text-[10px] text-gray-500 font-medium normal-case mt-0.5">{{ act.description }}</span>
                    </td>
                    <td class="py-3 px-4 text-gray-600 font-semibold">
                      <span class="inline-flex items-center gap-1.5 mr-3">
                        <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                        {{ act.stats.present }} Hadir
                      </span>
                      <span class="inline-flex items-center gap-1.5 mr-3" v-if="act.stats.sick > 0">
                        <span class="h-2 w-2 rounded-full bg-amber-500"></span>
                        {{ act.stats.sick }} Sakit
                      </span>
                      <span class="inline-flex items-center gap-1.5 mr-3" v-if="act.stats.permission > 0">
                        <span class="h-2 w-2 rounded-full bg-blue-500"></span>
                        {{ act.stats.permission }} Izin
                      </span>
                      <span class="inline-flex items-center gap-1.5 mr-3" v-if="act.stats.absent > 0">
                        <span class="h-2 w-2 rounded-full bg-red-500"></span>
                        {{ act.stats.absent }} Alpa
                      </span>
                    </td>
                    <td class="py-3 px-4 text-center">
                      <span 
                        v-if="act.status === 'Pending'" 
                        class="inline-block px-2.5 py-1 text-[10px] font-bold rounded-full bg-amber-100 text-amber-800 border border-amber-200"
                      >
                        Menunggu Approval Pembina
                      </span>
                      <span 
                        v-else 
                        class="inline-block px-2.5 py-1 text-[10px] font-bold rounded-full bg-emerald-100 text-emerald-800 border border-emerald-200"
                      >
                        Telah Disetujui
                      </span>
                    </td>
                    <td class="py-3 px-4 text-center">
                      <button 
                        @click="openDetailModal(act)"
                        class="text-[10px] text-emerald-700 hover:text-emerald-950 font-bold border border-emerald-200 bg-emerald-50 py-1 px-2.5 rounded shadow-sm hover:bg-emerald-100 transition-all"
                      >
                        Lihat Detail
                      </button>
                    </td>
                  </tr>
                  <tr v-if="activities.length === 0">
                    <td colspan="6" class="py-6 text-center text-gray-400 italic">Belum ada riwayat sesi kajian yang dibuat.</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Card: Materi Kajian Terbaru -->
          <div class="bg-white rounded-xl shadow-sm border border-emerald-100 p-6">
            <h2 class="text-base font-bold text-emerald-950 mb-4 pb-2 border-b border-emerald-50 flex items-center gap-2">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-emerald-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
              </svg>
              Materi Kajian Terbaru
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
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

        <!-- Right Panel: Removed -->
        <div v-if="false">
          <div class="bg-white rounded-xl shadow-sm border border-emerald-100 overflow-hidden">
            
            <!-- Conditional Header: Delegation Status -->
            <div 
              class="p-6 border-b flex flex-col sm:flex-row sm:items-center justify-between gap-4 transition-all duration-300"
              :class="group.is_delegated ? 'bg-amber-50/50 border-amber-100' : 'bg-emerald-50/50 border-emerald-100'"
            >
              <div>
                <!-- Status Badge -->
                <div class="inline-flex items-center space-x-1.5 mb-1.5">
                  <span 
                    class="h-2.5 w-2.5 rounded-full"
                    :class="group.is_delegated ? 'bg-amber-500 animate-pulse' : 'bg-emerald-600'"
                  ></span>
                  <span 
                    class="text-xs font-bold uppercase tracking-wider"
                    :class="group.is_delegated ? 'text-amber-800' : 'text-emerald-800'"
                  >
                    {{ group.is_delegated ? 'Delegasi Approval Aktif' : 'Akses Approval Ketua Kelompok' }}
                  </span>
                </div>

                <h2 class="text-lg font-bold text-gray-900">Validasi Absensi Halaqah</h2>
                <span class="text-xs text-gray-500 block mt-1">Sesi: <strong>{{ activeActivity?.topic || 'Belum ada kajian aktif' }}</strong></span>
              </div>

              <!-- Delegation Time Limit Badge -->
              <span v-if="group.is_delegated && group.delegated_until" class="text-xs bg-amber-100 text-amber-800 px-3 py-1 rounded-full font-semibold border border-amber-250">
                Berlaku s/d {{ new Date(group.delegated_until).toLocaleDateString('id-ID', {day: 'numeric', month: 'short'}) }}
              </span>
            </div>

            <!-- Form layout if delegation active/leader approval access -->
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
                    <!-- Status Button Group (Tepat Waktu, Terlambat, Sakit, Izin, Alpa) -->
                    <div class="flex items-center bg-gray-100 p-0.5 rounded-lg border border-gray-250/30">
                      <button 
                        type="button"
                        @click="updateMemberStatus(member.id, 'present')"
                        class="px-2.5 sm:px-3 py-1 rounded-md text-[11px] font-bold transition-all"
                        :class="getMemberStatus(member.id) === 'present' ? 'bg-emerald-600 text-white shadow-sm' : 'text-gray-500 hover:text-gray-800'"
                      >
                        Tepat Waktu
                      </button>
                      <button 
                        type="button"
                        @click="updateMemberStatus(member.id, 'late')"
                        class="px-2.5 sm:px-3 py-1 rounded-md text-[11px] font-bold transition-all"
                        :class="getMemberStatus(member.id) === 'late' ? 'bg-teal-600 text-white shadow-sm' : 'text-gray-500 hover:text-gray-800'"
                      >
                        Terlambat
                      </button>
                      <button 
                        type="button"
                        @click="updateMemberStatus(member.id, 'sick')"
                        class="px-2.5 sm:px-3 py-1 rounded-md text-[11px] font-bold transition-all"
                        :class="getMemberStatus(member.id) === 'sick' ? 'bg-amber-500 text-white shadow-sm' : 'text-gray-500 hover:text-gray-800'"
                      >
                        Sakit
                      </button>
                      <button 
                        type="button"
                        @click="updateMemberStatus(member.id, 'permission')"
                        class="px-2.5 sm:px-3 py-1 rounded-md text-[11px] font-bold transition-all"
                        :class="getMemberStatus(member.id) === 'permission' ? 'bg-blue-600 text-white shadow-sm' : 'text-gray-500 hover:text-gray-800'"
                      >
                        Izin
                      </button>
                      <button 
                        type="button"
                        @click="updateMemberStatus(member.id, 'absent')"
                        class="px-2.5 sm:px-3 py-1 rounded-md text-[11px] font-bold transition-all"
                        :class="getMemberStatus(member.id) === 'absent' ? 'bg-red-600 text-white shadow-sm' : 'text-gray-500 hover:text-gray-800'"
                      >
                        Alpa
                      </button>
                    </div>

                    <!-- Approval Badge / Indicator -->
                    <div>
                      <span v-if="member.is_approved" class="text-[10px] bg-emerald-100 text-emerald-800 font-bold px-2 py-1 rounded border border-emerald-200 inline-flex items-center gap-0.5">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                        </svg>
                        <span>Sudah Disimpan</span>
                      </span>
                      <span v-else class="text-[10px] bg-amber-50 text-amber-800 font-bold px-2 py-1 rounded border border-amber-200">
                        Belum Simpan
                      </span>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Submit and Info Section -->
              <div class="p-6 bg-gray-50/50 flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-t border-gray-100">
                <span class="text-[11px] text-gray-500 max-w-sm">
                  ℹ️ Pilih status kehadiran untuk masing-masing anggota di atas, lalu klik <strong>Simpan Absensi Kelompok</strong> untuk menyimpan data ke server.
                </span>
                <button 
                  type="button"
                  @click="submitGroupAttendance"
                  :disabled="groupAttendanceForm.processing"
                  class="bg-emerald-700 hover:bg-emerald-800 disabled:bg-gray-300 text-white font-bold text-xs py-2.5 px-4 rounded-lg shadow-md transition-all flex items-center justify-center gap-1.5"
                >
                  <svg v-if="groupAttendanceForm.processing" class="animate-spin h-3.5 w-3.5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  <span>Simpan Absensi Kelompok</span>
                </button>
              </div>
            </div>
          </div>
        </div>

      </div>
      <div v-else class="text-center p-12 bg-white rounded-2xl shadow-sm border border-emerald-100">
        <p class="text-gray-500">Anda belum di-plotting sebagai Ketua Kelompok di halaqah manapun.</p>
      </div>

      <!-- SECTION: REKAP ABSENSI BULANAN INTERAKTIF -->
      <div v-if="group" class="mt-8 bg-white rounded-xl shadow-sm border border-emerald-100 p-6 animate-slide-in">
        <div class="mb-6 border-b border-gray-100 pb-3 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
          <div>
            <h2 class="text-base sm:text-lg font-bold text-emerald-950">Tampilan Rekap Absensi Bulanan</h2>
            <p class="text-xs text-gray-500 font-medium">Tinjau kehadiran anggota kelompok Anda di bulan terpilih secara interaktif.</p>
          </div>
        </div>

        <!-- Filter Form -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 items-end mb-6 bg-gray-50/50 p-4 rounded-xl border border-gray-100">
          <div>
            <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block mb-1">Bulan</label>
            <select v-model="webRecapFilter.month" class="w-full border border-gray-250 rounded-lg p-2 text-xs focus:ring-1 focus:ring-emerald-500 outline-none shadow-sm">
              <option v-for="m in 12" :key="m" :value="m">
                {{ new Date(2026, m-1, 1).toLocaleString('id-ID', { month: 'long' }) }}
              </option>
            </select>
          </div>
          <div>
            <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block mb-1">Tahun</label>
            <select v-model="webRecapFilter.year" class="w-full border border-gray-250 rounded-lg p-2 text-xs focus:ring-1 focus:ring-emerald-500 outline-none shadow-sm">
              <option v-for="y in [2025, 2026, 2027]" :key="y" :value="y">{{ y }}</option>
            </select>
          </div>
          <div>
            <button @click="fetchWebRecapData" :disabled="isWebRecapLoading" class="w-full bg-emerald-700 hover:bg-emerald-800 text-white font-bold text-xs py-2.5 px-4 rounded-lg shadow-sm transition-all flex items-center justify-center gap-1">
              <span v-if="isWebRecapLoading">Memuat...</span>
              <span v-else>Tampilkan Rekap</span>
            </button>
          </div>
        </div>

        <!-- Loading Spinner -->
        <div v-if="isWebRecapLoading" class="py-12 flex flex-col items-center justify-center space-y-3">
          <div class="animate-spin rounded-full h-8 w-8 border-4 border-emerald-750 border-t-transparent"></div>
          <span class="text-xs text-gray-500 font-medium">Sedang memproses data rekapitulasi...</span>
        </div>

        <!-- Empty State -->
        <div v-else-if="!webRecapData" class="py-12 text-center text-gray-400 text-xs font-medium">
          Silakan klik "Tampilkan Rekap" untuk memuat data rekapitulasi kehadiran kelompok Anda.
        </div>

        <!-- Data Grid -->
        <div v-else class="space-y-4">
          <div class="overflow-x-auto rounded-lg border border-gray-150">
            <table class="w-full text-left border-collapse text-xs">
              <thead>
                <tr class="bg-gray-50 font-bold text-gray-500 uppercase tracking-wider border-b border-gray-150">
                  <th class="py-3 px-4">No</th>
                  <th class="py-3 px-4">Nama Anggota</th>
                  <th class="py-3 px-4">Kelompok</th>
                  <th v-for="act in webRecapData.activities" :key="act.id" class="py-3 px-2 text-center select-none">
                    <span 
                      class="underline decoration-dotted cursor-help block font-bold transition-colors" 
                      :class="act.is_pending ? 'text-amber-600 font-extrabold' : 'text-emerald-950'"
                      :title="act.date + ' - ' + act.topic + (act.is_pending ? ' (Menunggu Approval)' : '')"
                    >
                      {{ act.label }}{{ act.is_pending ? '*' : '' }}
                    </span>
                  </th>
                  <th class="py-3 px-3 text-center">H</th>
                  <th class="py-3 px-3 text-center">S</th>
                  <th class="py-3 px-3 text-center">I</th>
                  <th class="py-3 px-3 text-center">A</th>
                  <th class="py-3 px-4 text-center">Persentase</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100">
                <tr v-for="row in webRecapData.rows" :key="row.no" class="hover:bg-gray-50/40">
                  <td class="py-3 px-4 font-bold text-gray-900">{{ row.no }}</td>
                  <td class="py-3 px-4 font-bold text-emerald-850">{{ row.member_name }}</td>
                  <td class="py-3 px-4 text-gray-655 font-semibold">{{ row.group_name }}</td>
                  
                  <!-- Session Cells matrix -->
                  <td v-for="act in webRecapData.activities" :key="act.id" class="py-3 px-2 text-center font-bold">
                    <span 
                      class="inline-block w-6 h-6 leading-6 rounded text-[10px] text-center"
                      :class="{
                        'bg-emerald-50 text-emerald-700 border border-emerald-150': row.sessions[act.id] === 'H',
                        'bg-amber-50 text-amber-700 border border-amber-150': row.sessions[act.id] === 'S',
                        'bg-blue-50 text-blue-700 border border-blue-150': row.sessions[act.id] === 'I',
                        'bg-red-50 text-red-700 border border-red-150': row.sessions[act.id] === 'A',
                        'bg-gray-50 text-gray-400 border border-gray-150': row.sessions[act.id] === '—'
                      }"
                    >
                      {{ row.sessions[act.id] }}
                    </span>
                  </td>

                  <td class="py-3 px-3 text-center text-emerald-700 font-bold">{{ row.present }}</td>
                  <td class="py-3 px-3 text-center text-amber-700 font-bold">{{ row.sick }}</td>
                  <td class="py-3 px-3 text-center text-blue-700 font-bold">{{ row.permission }}</td>
                  <td class="py-3 px-3 text-center text-red-700 font-bold">{{ row.absent }}</td>
                  <td class="py-3 px-4 text-center">
                    <span class="font-bold text-emerald-800 bg-emerald-50 px-2 py-0.5 rounded-full text-[10px] border border-emerald-100">
                      {{ row.percentage }}
                    </span>
                  </td>
                </tr>
                <tr v-if="webRecapData.rows.length === 0">
                  <td :colspan="8 + webRecapData.activities.length" class="py-6 text-center text-gray-400 italic">Tidak ada data anggota atau sesi halaqah pada periode ini.</td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Legend for Matrix -->
          <div class="flex flex-wrap items-center gap-4 text-[10px] text-gray-500 font-semibold bg-gray-50 p-3 rounded-lg border border-gray-150">
            <span class="text-gray-700 uppercase tracking-wider">Keterangan:</span>
            <span class="flex items-center gap-1.5"><span class="inline-block w-4 h-4 bg-emerald-50 border border-emerald-150 rounded text-center text-[9px] font-bold text-emerald-700">H</span> Hadir</span>
            <span class="flex items-center gap-1.5"><span class="inline-block w-4 h-4 bg-amber-50 border border-amber-150 rounded text-center text-[9px] font-bold text-amber-700">S</span> Sakit</span>
            <span class="flex items-center gap-1.5"><span class="inline-block w-4 h-4 bg-blue-50 border border-blue-150 rounded text-center text-[9px] font-bold text-blue-700">I</span> Izin</span>
            <span class="flex items-center gap-1.5"><span class="inline-block w-4 h-4 bg-red-50 border border-red-150 rounded text-center text-[9px] font-bold text-red-700">A</span> Alpa (Tanpa Keterangan)</span>
            <span class="flex items-center gap-1.5"><span class="inline-block w-4 h-4 bg-gray-50 border border-gray-150 rounded text-center text-[9px] font-bold text-gray-400">—</span> Belum Input Sesi</span>
            <span class="text-emerald-950 font-bold ml-auto">* Kode berwarna oranye (*) = Menunggu Approval. Arahkan kursor pada P1/P2/dst untuk detail tanggal & topik.</span>
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
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
        </svg>
      </div>
      <span class="text-xs sm:text-sm font-semibold">{{ notificationMessage }}</span>
    </div>

    <!-- Modal: Buat Sesi & Presensi -->
    <div v-if="showCreateSessionModal" class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4" role="dialog" aria-modal="true">
      <div class="fixed inset-0 bg-emerald-950/40 backdrop-blur-sm" @click="showCreateSessionModal = false"></div>
      <div class="relative bg-white rounded-t-2xl sm:rounded-2xl w-full sm:max-w-2xl max-h-[90vh] overflow-y-auto shadow-2xl border border-emerald-50 animate-slide-in">
        <!-- Modal Header -->
        <div class="bg-emerald-800 text-white px-5 py-4 flex items-center justify-between sticky top-0 z-10">
          <div class="flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-amber-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <h3 class="text-base font-bold">Buat Sesi & Input Presensi</h3>
          </div>
          <button @click="showCreateSessionModal = false" class="text-emerald-200 hover:text-white transition-colors p-1">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <form @submit.prevent="submitCreateSession" class="p-6 space-y-6">
          <!-- Session Form details -->
          <div class="space-y-4">
            <h4 class="text-xs font-bold text-emerald-900 uppercase tracking-wider border-b pb-1">Detail Kajian</h4>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block mb-1">Tanggal & Waktu Sesi</label>
                <input 
                  type="datetime-local" 
                  v-model="sessionForm.date" 
                  required
                  class="w-full border border-gray-250 rounded-lg p-2.5 text-xs focus:ring-1 focus:ring-emerald-500 outline-none shadow-sm"
                />
              </div>
              <div>
                <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block mb-1">Topik Pembahasan</label>
                <input 
                  type="text" 
                  v-model="sessionForm.topic" 
                  placeholder="Contoh: Fiqh Prioritas, Tazkiyatun Nafs"
                  required
                  class="w-full border border-gray-250 rounded-lg p-2.5 text-xs focus:ring-1 focus:ring-emerald-500 outline-none shadow-sm"
                />
              </div>
            </div>
            <div>
              <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block mb-1">Deskripsi/Catatan Tambahan (Opsional)</label>
              <textarea 
                v-model="sessionForm.description" 
                rows="2" 
                placeholder="Tulis ringkasan singkat atau pesan penting untuk sesi kajian ini..."
                class="w-full border border-gray-250 rounded-lg p-2.5 text-xs focus:ring-1 focus:ring-emerald-500 outline-none shadow-sm"
              ></textarea>
            </div>
          </div>

          <!-- Attendance Form details -->
          <div class="space-y-4">
            <h4 class="text-xs font-bold text-emerald-900 uppercase tracking-wider border-b pb-1">Kehadiran Anggota (Mutarabbi)</h4>
            <div class="divide-y divide-gray-100 max-h-60 overflow-y-auto pr-1">
              <div 
                v-for="member in sessionForm.attendances" 
                :key="member.user_id" 
                class="py-3 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3"
              >
                <span class="text-xs font-bold text-gray-900">{{ member.name }}</span>
                <div class="flex items-center bg-gray-100 p-0.5 rounded-lg border border-gray-250/30">
                  <button 
                    type="button"
                    @click="member.status = 'present'"
                    class="px-2 py-1 rounded-md text-[10px] font-bold transition-all"
                    :class="member.status === 'present' ? 'bg-emerald-600 text-white shadow-sm' : 'text-gray-500 hover:text-gray-800'"
                  >
                    Hadir
                  </button>
                  <button 
                    type="button"
                    @click="member.status = 'late'"
                    class="px-2 py-1 rounded-md text-[10px] font-bold transition-all"
                    :class="member.status === 'late' ? 'bg-teal-600 text-white shadow-sm' : 'text-gray-500 hover:text-gray-800'"
                  >
                    Terlambat
                  </button>
                  <button 
                    type="button"
                    @click="member.status = 'sick'"
                    class="px-2 py-1 rounded-md text-[10px] font-bold transition-all"
                    :class="member.status === 'sick' ? 'bg-amber-500 text-white shadow-sm' : 'text-gray-500 hover:text-gray-800'"
                  >
                    Sakit
                  </button>
                  <button 
                    type="button"
                    @click="member.status = 'permission'"
                    class="px-2 py-1 rounded-md text-[10px] font-bold transition-all"
                    :class="member.status === 'permission' ? 'bg-blue-600 text-white shadow-sm' : 'text-gray-500 hover:text-gray-800'"
                  >
                    Izin
                  </button>
                  <button 
                    type="button"
                    @click="member.status = 'absent'"
                    class="px-2 py-1 rounded-md text-[10px] font-bold transition-all"
                    :class="member.status === 'absent' ? 'bg-red-600 text-white shadow-sm' : 'text-gray-500 hover:text-gray-800'"
                  >
                    Alpa
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Actions -->
          <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
            <button 
              type="button" 
              @click="showCreateSessionModal = false"
              class="px-4 py-2 border border-gray-250 text-gray-700 font-bold text-xs rounded-lg hover:bg-gray-50 shadow-sm transition-colors"
            >
              Batal
            </button>
            <button 
              type="submit" 
              :disabled="sessionForm.processing"
              class="px-4 py-2 bg-emerald-700 hover:bg-emerald-800 disabled:bg-gray-300 text-white font-bold text-xs rounded-lg shadow-md transition-colors flex items-center gap-1.5"
            >
              <svg v-if="sessionForm.processing" class="animate-spin h-3.5 w-3.5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              <span>Ajukan Sesi & Presensi</span>
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal Detail Absensi Sesi Kajian -->
    <div v-if="showDetailModal && selectedActivityForDetail" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-950/40 backdrop-blur-sm">
      <div class="bg-white rounded-2xl w-full max-w-lg shadow-xl border border-emerald-100 overflow-hidden animate-slide-in">
        <!-- Header -->
        <div class="bg-gradient-to-r from-emerald-800 to-emerald-750 py-4 px-6 flex justify-between items-center text-white">
          <div>
            <h3 class="text-sm sm:text-base font-bold">Detail Presensi Sesi</h3>
            <p class="text-[10px] text-emerald-100 font-medium">Topik: {{ selectedActivityForDetail.topic }}</p>
          </div>
          <button @click="showDetailModal = false" class="text-white hover:text-emerald-200 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <div class="p-6 space-y-4">
          <!-- Info Sesi -->
          <div class="grid grid-cols-2 gap-4 bg-gray-50 p-3 rounded-lg border border-gray-150 text-xs">
            <div>
              <span class="text-gray-400 font-semibold block uppercase tracking-wider text-[9px]">Jadwal Sesi</span>
              <span class="font-bold text-gray-900">{{ selectedActivityForDetail.date_human }}</span>
            </div>
            <div>
              <span class="text-gray-400 font-semibold block uppercase tracking-wider text-[9px]">Status Approval</span>
              <span 
                class="inline-block px-2 py-0.5 rounded text-[10px] font-bold mt-0.5"
                :class="selectedActivityForDetail.status === 'Pending' ? 'bg-amber-100 text-amber-800 border border-amber-200' : 'bg-emerald-100 text-emerald-800 border border-emerald-200'"
              >
                {{ selectedActivityForDetail.status === 'Pending' ? 'Menunggu Approval' : 'Disetujui Pembina' }}
              </span>
            </div>
            <div class="col-span-2" v-if="selectedActivityForDetail.description">
              <span class="text-gray-400 font-semibold block uppercase tracking-wider text-[9px]">Deskripsi</span>
              <span class="text-gray-700 mt-0.5 block leading-relaxed font-sans">{{ selectedActivityForDetail.description }}</span>
            </div>
          </div>

          <!-- Statistik Ringkas -->
          <div class="flex flex-wrap gap-2 text-xs font-semibold">
            <span class="px-2 py-1 bg-emerald-50 text-emerald-700 rounded border border-emerald-150">
              {{ selectedActivityForDetail.stats.present }} Hadir
            </span>
            <span v-if="selectedActivityForDetail.stats.sick > 0" class="px-2 py-1 bg-amber-50 text-amber-700 rounded border border-amber-150">
              {{ selectedActivityForDetail.stats.sick }} Sakit
            </span>
            <span v-if="selectedActivityForDetail.stats.permission > 0" class="px-2 py-1 bg-blue-50 text-blue-700 rounded border border-blue-150">
              {{ selectedActivityForDetail.stats.permission }} Izin
            </span>
            <span v-if="selectedActivityForDetail.stats.absent > 0" class="px-2 py-1 bg-red-50 text-red-700 rounded border border-red-150">
              {{ selectedActivityForDetail.stats.absent }} Alpa
            </span>
          </div>

          <!-- Daftar Anggota & Kehadiran -->
          <div>
            <h4 class="text-xs font-bold text-emerald-900 uppercase tracking-wider border-b pb-1 mb-2">Daftar Kehadiran Anggota</h4>
            <div class="divide-y divide-gray-100 max-h-60 overflow-y-auto pr-1">
              <div 
                v-for="item in selectedActivityForDetail.details" 
                :key="item.id" 
                class="py-2.5 flex items-center justify-between text-xs"
              >
                <span class="font-bold text-gray-900">{{ item.user_name }}</span>
                <div class="flex items-center gap-3">
                  <!-- Status Badge -->
                  <span 
                    class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider"
                    :class="{
                      'bg-emerald-100 text-emerald-800 border border-emerald-200': item.status === 'present' || item.status === 'late',
                      'bg-amber-100 text-amber-800 border border-amber-200': item.status === 'sick',
                      'bg-blue-100 text-blue-800 border border-blue-200': item.status === 'permission',
                      'bg-red-100 text-red-800 border border-red-250': item.status === 'absent',
                    }"
                  >
                    {{ 
                      item.status === 'present' ? 'Hadir' : 
                      item.status === 'late' ? 'Terlambat' : 
                      item.status === 'sick' ? 'Sakit' : 
                      item.status === 'permission' ? 'Izin' : 'Alpa' 
                    }}
                  </span>

                  <!-- Status Approval -->
                  <span 
                    class="text-[9px] font-bold px-1.5 py-0.5 rounded"
                    :class="item.is_approved ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700'"
                  >
                    {{ item.is_approved ? '✓ Disetujui' : '⚡ Pending' }}
                  </span>
                </div>
              </div>
              <div v-if="!selectedActivityForDetail.details || selectedActivityForDetail.details.length === 0" class="py-4 text-center text-gray-400 italic">
                Tidak ada data absensi untuk sesi ini.
              </div>
            </div>
          </div>
        </div>

        <!-- Footer -->
        <div class="flex justify-end gap-3 p-4 bg-gray-50 border-t border-gray-100">
          <button 
            type="button" 
            @click="showDetailModal = false"
            class="px-4 py-2 bg-emerald-800 hover:bg-emerald-950 text-white font-bold text-xs rounded-lg shadow-md transition-colors"
          >
            Tutup
          </button>
        </div>
      </div>
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
