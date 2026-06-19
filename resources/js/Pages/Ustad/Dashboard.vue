<script setup>
import { ref, computed } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';

// Props passed from Laravel Inertia Controller
const props = defineProps({
  auth: Object,
  groups: Array,
  activeActivity: Object,
  statistics: Object,
  materials: Array,
  activities: Array,
  grades: Array,
  attendances: Array
});

// Reactive States
const selectedGroupId = ref(props.groups[0]?.id || null);
const activeTab = ref('groups'); // 'groups' | 'materials' | 'activities' | 'attendances' | 'grades'

const showDelegationModal = ref(false);
const showMaterialModal = ref(false);
const showGradeBulkModal = ref(false);
const showActivityModal = ref(false);
const showAttendanceModal = ref(false); // for bulk input on Sesi Kajian tab

const isEditingActivity = ref(false);
const editingActivityId = ref(null);

const showSuccessNotification = ref(false);
const notificationMessage = ref('');

const triggerNotification = (msg) => {
  notificationMessage.value = msg;
  showSuccessNotification.value = true;
  setTimeout(() => showSuccessNotification.value = false, 4000);
};

// Computed Property to get currently active group details
const activeGroup = computed(() => {
  return props.groups.find(g => g.id === selectedGroupId.value) || null;
});

// Search Queries
const searchMaterialQuery = ref('');
const searchActivityQuery = ref('');
const searchGradeQuery = ref('');
const searchAttendanceQuery = ref('');

// Computed Filters
const filteredMaterials = computed(() => {
  if (!searchMaterialQuery.value) return props.materials;
  const q = searchMaterialQuery.value.toLowerCase();
  return props.materials.filter(m => m.title.toLowerCase().includes(q) || m.content?.toLowerCase().includes(q) || m.ustad_name.toLowerCase().includes(q));
});

const filteredActivities = computed(() => {
  if (!searchActivityQuery.value) return props.activities;
  const q = searchActivityQuery.value.toLowerCase();
  return props.activities.filter(a => a.topic.toLowerCase().includes(q) || a.group_name.toLowerCase().includes(q));
});

const filteredGrades = computed(() => {
  if (!searchGradeQuery.value) return props.grades;
  const q = searchGradeQuery.value.toLowerCase();
  return props.grades.filter(gr => gr.user_name.toLowerCase().includes(q) || gr.ustad_name.toLowerCase().includes(q));
});

const filteredAttendances = computed(() => {
  if (!searchAttendanceQuery.value) return props.attendances;
  const q = searchAttendanceQuery.value.toLowerCase();
  return props.attendances.filter(at => at.user_name.toLowerCase().includes(q) || at.activity_topic.toLowerCase().includes(q) || at.status.toLowerCase().includes(q));
});

// Forms
const delegationForm = useForm({
  is_delegated: activeGroup.value?.is_delegated || false,
  delegated_until: activeGroup.value?.delegated_until 
    ? new Date(activeGroup.value.delegated_until).toISOString().slice(0, 16) 
    : ''
});

const materialForm = useForm({
  title: '',
  content: '',
  file: null
});

const gradeBulkForm = useForm({
  month: new Date().getMonth() + 1,
  year: new Date().getFullYear(),
  grades: []
});

const activityForm = useForm({
  group_id: '',
  date: '',
  topic: '',
  description: ''
});

const selectedActivity = ref(null);

const selectedActivityMembers = computed(() => {
  if (!selectedActivity.value) return [];
  const group = props.groups.find(g => g.id === selectedActivity.value.group_id);
  if (!group) return [];
  
  return group.members.map(m => {
    const existing = props.attendances.find(at => at.user_id === m.id && at.activity_id === selectedActivity.value.id);
    return {
      id: m.id,
      name: m.name,
      status: existing ? existing.status : null,
      is_approved: existing ? (existing.approved_by !== '—' && existing.approved_by !== null) : false
    };
  });
});

// Update group selection
const changeGroup = (id) => {
  selectedGroupId.value = id;
};

// Open delegation configuration
const openDelegation = () => {
  if (activeGroup.value) {
    delegationForm.is_delegated = activeGroup.value.is_delegated;
    delegationForm.delegated_until = activeGroup.value.delegated_until
      ? new Date(activeGroup.value.delegated_until).toISOString().slice(0, 16)
      : '';
    showDelegationModal.value = true;
  }
};

// Actions: Attendance (Approve / Reject)
const approveMember = (activity, member) => {
  if (!activity) return;
  router.post(`/activities/${activity.id}/attendances/${member.id}/approve`, {}, {
    onSuccess: () => triggerNotification(`Kehadiran ${member.name} berhasil disetujui!`)
  });
};

const rejectMember = (activity, member) => {
  if (!activity) return;
  if (confirm(`Apakah Anda yakin ingin menolak/menghapus kehadiran ${member.name}?`)) {
    router.post(`/activities/${activity.id}/attendances/${member.id}/reject`, {}, {
      onSuccess: () => triggerNotification(`Kehadiran ${member.name} berhasil ditolak!`)
    });
  }
};

// Actions: Attendance (Modal from Sesi Kajian Tab)
const openAttendanceApproval = (activity) => {
  selectedActivity.value = activity;
  showAttendanceModal.value = true;
};

const deleteAttendance = (attendanceId) => {
  if (confirm('Hapus log presensi ini?')) {
    router.delete(`/attendances/${attendanceId}`, {
      onSuccess: () => triggerNotification('Log presensi berhasil dihapus.')
    });
  }
};

// Actions: Delegation
const submitDelegation = () => {
  delegationForm.post(`/groups/${activeGroup.value.id}/delegation/toggle`, {
    onSuccess: () => {
      showDelegationModal.value = false;
      triggerNotification(
        delegationForm.is_delegated 
          ? `Hak approval absensi berhasil didelegasikan kepada ${activeGroup.value.leader.name}!`
          : 'Delegasi approval absensi berhasil dinonaktifkan.'
      );
    }
  });
};

// Actions: Materials
const openUploadMaterial = () => {
  materialForm.reset();
  showMaterialModal.value = true;
};

const submitMaterial = () => {
  materialForm.post('/materials', {
    onSuccess: () => {
      showMaterialModal.value = false;
      triggerNotification('Materi kajian berhasil diunggah dan dibagikan.');
    }
  });
};

const deleteMaterial = (materialId) => {
  if (confirm('Apakah Anda yakin ingin menghapus materi ini?')) {
    router.delete(`/materials/${materialId}`, {
      onSuccess: () => triggerNotification('Materi berhasil dihapus.')
    });
  }
};

// Actions: Grades
const openBulkGrade = () => {
  if (activeGroup.value) {
    gradeBulkForm.grades = activeGroup.value.members.map(m => ({
      user_id: m.id,
      name: m.name,
      kehadiran: 20,
      kedisiplinan: 8,
      partisipasi: 8,
      sikap: 8,
      pemahaman: 16,
      bacaan: 20,
      notes_text: '',
      score: 80,
      notes: ''
    }));
    showGradeBulkModal.value = true;
  }
};

const calculateTotalScore = (item) => {
  return (item.kehadiran || 0) +
         (item.kedisiplinan || 0) +
         (item.partisipasi || 0) +
         (item.sikap || 0) +
         (item.pemahaman || 0) +
         (item.bacaan || 0);
};

const submitBulkGrades = () => {
  gradeBulkForm.grades.forEach(item => {
    const k = item.kehadiran || 0;
    const d = item.kedisiplinan || 0;
    const p = item.partisipasi || 0;
    const s = item.sikap || 0;
    const m = item.pemahaman || 0;
    const q = item.bacaan || 0;
    item.score = k + d + p + s + m + q;
    item.notes = `Rincian: Hdr ${k}/25 | Dsl ${d}/10 | Pts ${p}/10 | Skp ${s}/10 | Phm ${m}/20 | Qur ${q}/25. Catatan: ${item.notes_text || ''}`;
  });

  gradeBulkForm.post('/grades/bulk', {
    onSuccess: () => {
      showGradeBulkModal.value = false;
      triggerNotification('Rekap nilai bulanan berhasil disimpan.');
    }
  });
};

const deleteGrade = (gradeId) => {
  if (confirm('Hapus rekam nilai ini?')) {
    router.delete(`/grades/${gradeId}`, {
      onSuccess: () => triggerNotification('Data nilai bulanan berhasil dihapus.')
    });
  }
};

// Actions: Sesi Kajian (Activities)
const openAddActivity = () => {
  isEditingActivity.value = false;
  editingActivityId.value = null;
  activityForm.reset();
  activityForm.group_id = selectedGroupId.value || '';
  showActivityModal.value = true;
};

const openEditActivity = (activity) => {
  isEditingActivity.value = true;
  editingActivityId.value = activity.id;
  activityForm.group_id = activity.group_id;
  activityForm.date = activity.date;
  activityForm.topic = activity.topic;
  activityForm.description = activity.description;
  showActivityModal.value = true;
};

const submitActivity = () => {
  if (isEditingActivity.value) {
    activityForm.put(`/activities/${editingActivityId.value}`, {
      onSuccess: () => {
        showActivityModal.value = false;
        triggerNotification('Sesi kajian berhasil diperbarui.');
      }
    });
  } else {
    activityForm.post('/activities', {
      onSuccess: () => {
        showActivityModal.value = false;
        triggerNotification('Sesi kajian baru berhasil ditambahkan.');
      }
    });
  }
};

const deleteActivity = (activityId) => {
  if (confirm('Hapus sesi kajian ini? Absensi yang menempel pada sesi ini juga akan hilang.')) {
    router.delete(`/activities/${activityId}`, {
      onSuccess: () => triggerNotification('Sesi kajian berhasil dihapus.')
    });
  }
};

const handleLogout = () => {
  router.post('/logout');
};
</script>

<template>
  <Head title="Dashboard Ustad - Pembinaan Anggota" />

  <div class="relative min-h-screen bg-[#FAFAF9] text-gray-800 font-sans pb-12">
    <!-- Islamic Watermark -->
    <div 
      class="absolute inset-0 opacity-[0.06] pointer-events-none"
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
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
              </svg>
            </div>
            <div>
              <span class="text-base sm:text-lg font-bold text-emerald-800 tracking-wide block">{{ $page.props.settings?.app_title || 'UPA Pembina' }}</span>
              <span class="hidden sm:block text-xs text-emerald-600 font-medium">Unit Pembinaan Anggota</span>
            </div>
          </div>
          <div class="flex items-center space-x-2 sm:space-x-4">
            <div class="flex items-center space-x-1.5 sm:space-x-2 bg-emerald-50 px-2 sm:px-3 py-1.5 rounded-full border border-emerald-100 max-w-[140px] sm:max-w-none overflow-hidden">
              <div class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse flex-shrink-0"></div>
              <span class="text-xs font-semibold text-emerald-800 truncate">{{ (auth?.name || auth?.user?.name || 'Ustad').split(' ').slice(0,2).join(' ') }}</span>
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

    <!-- Main Content Body -->
    <main class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 mt-4 sm:mt-8 relative z-10">
      
      <!-- Welcome Header -->
      <div class="bg-gradient-to-r from-emerald-800 to-emerald-950 text-white rounded-2xl p-4 sm:p-6 lg:p-8 shadow-xl relative overflow-hidden mb-5 sm:mb-8 border border-emerald-700">
        <div class="absolute -right-16 -top-16 w-48 h-48 rounded-full bg-amber-400 opacity-10 blur-xl"></div>
        <div class="absolute right-12 bottom-0 w-32 h-32 rounded-full bg-emerald-500 opacity-20 blur-xl"></div>
        
        <div class="relative z-10 flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4">
          <div class="flex-1">
            <div class="inline-flex items-center space-x-2 bg-amber-400/20 text-amber-300 text-xs px-3 py-1 rounded-full border border-amber-400/30 mb-2">
              <span>Pembina / Ustad</span>
            </div>
            <h1 class="text-xl sm:text-2xl lg:text-3xl font-bold tracking-tight">Assalamu'alaikum, {{ (auth?.name || auth?.user?.name || 'Ustad').split(' ').slice(0,3).join(' ') }}</h1>
            <p class="text-emerald-100 mt-1.5 text-xs sm:text-sm max-w-xl hidden sm:block">
              Selamat datang di dashboard UPA. Kelola kajian, absensi mutaba'ah, dan monitoring perkembangan rohani mutarabbi dengan amanah.
            </p>
          </div>
          <div class="flex items-center space-x-2 sm:space-x-3 bg-white/10 backdrop-blur-md rounded-xl p-2.5 sm:p-3 border border-white/20 flex-shrink-0">
            <div class="p-1.5 sm:p-2 bg-amber-400 rounded-lg text-emerald-900">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <div>
              <span class="text-[10px] sm:text-xs text-emerald-200 block uppercase font-semibold">Kajian Aktif</span>
              <span class="text-xs sm:text-sm font-semibold text-amber-300 max-w-[160px] sm:max-w-[200px] truncate block">{{ activeActivity?.topic || 'Belum ada kajian aktif' }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Quick Statistics Panels -->
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-8">
        <div class="bg-white rounded-xl p-5 shadow-sm border border-emerald-50 hover:shadow-md transition-shadow">
          <div class="flex items-center justify-between">
            <span class="text-xs sm:text-sm font-medium text-gray-500">Total Binaan</span>
            <div class="p-2 bg-emerald-100 text-emerald-800 rounded-lg">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a7 7 0 00-7 7v1h12v-1a7 7 0 00-7-7z" />
              </svg>
            </div>
          </div>
          <div class="mt-4">
            <span class="text-2xl sm:text-3xl font-bold text-gray-900">{{ statistics.totalMembers }}</span>
            <span class="text-xs text-gray-400 block mt-1">aktif terdaftar</span>
          </div>
        </div>

        <div class="bg-white rounded-xl p-5 shadow-sm border border-emerald-50 hover:shadow-md transition-shadow">
          <div class="flex items-center justify-between">
            <span class="text-xs sm:text-sm font-medium text-gray-500">Absensi Pending</span>
            <div class="p-2 bg-amber-100 text-amber-800 rounded-lg">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
              </svg>
            </div>
          </div>
          <div class="mt-4">
            <span class="text-2xl sm:text-3xl font-bold text-amber-600">{{ statistics.pendingApprovals }}</span>
            <span class="text-xs text-gray-400 block mt-1">menunggu validasi</span>
          </div>
        </div>

        <div class="bg-white rounded-xl p-5 shadow-sm border border-emerald-50 hover:shadow-md transition-shadow">
          <div class="flex items-center justify-between">
            <span class="text-xs sm:text-sm font-medium text-gray-500">Materi Kajian</span>
            <div class="p-2 bg-emerald-100 text-emerald-800 rounded-lg">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd" />
              </svg>
            </div>
          </div>
          <div class="mt-4">
            <span class="text-2xl sm:text-3xl font-bold text-gray-900">{{ statistics.sharedMaterials }}</span>
            <span class="text-xs text-gray-400 block mt-1">dokumen dibagikan</span>
          </div>
        </div>

        <div class="bg-white rounded-xl p-5 shadow-sm border border-emerald-50 hover:shadow-md transition-shadow">
          <div class="flex items-center justify-between">
            <span class="text-xs sm:text-sm font-medium text-gray-500">Rata-rata Nilai</span>
            <div class="p-2 bg-amber-100 text-amber-800 rounded-lg">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
              </svg>
            </div>
          </div>
          <div class="mt-4">
            <span class="text-2xl sm:text-3xl font-bold text-emerald-800">{{ statistics.averageGrade }}</span>
            <span class="text-xs text-gray-400 block mt-1">skala indeks 100</span>
          </div>
        </div>
      </div>

      <!-- Tab Navigation Switcher -->
      <div class="flex flex-wrap border-b border-gray-200 mb-6 bg-white p-2 rounded-xl shadow-sm border border-emerald-50 gap-2">
        <button 
          v-for="tab in [
            { key: 'groups', label: 'Daftar Kelompok & Absensi' },
            { key: 'materials', label: 'Materi Kajian' },
            { key: 'activities', label: 'Sesi Kajian' },
            { key: 'attendances', label: 'Log Presensi' },
            { key: 'grades', label: 'Penilaian Bulanan' }
          ]" 
          :key="tab.key"
          @click="activeTab = tab.key"
          class="px-4 py-2 rounded-lg text-xs sm:text-sm font-bold transition-all"
          :class="activeTab === tab.key ? 'bg-emerald-700 text-white shadow-sm' : 'text-gray-500 hover:bg-gray-50'"
        >
          {{ tab.label }}
        </button>
      </div>

      <!-- Tab Contents -->
      <div>
        
        <!-- TAB 1: GROUPS & INTERACTIVE ATTENDANCE VERIFICATION -->
        <div v-if="activeTab === 'groups'" class="grid grid-cols-1 lg:grid-cols-3 gap-8 animate-slide-in">
          <!-- Left Column: List of Groups -->
          <div class="lg:col-span-1 space-y-6">
            <div class="bg-white rounded-xl shadow-sm border border-emerald-100 p-6">
              <div class="flex items-center justify-between mb-4 pb-2 border-b border-emerald-50">
                <h2 class="text-sm sm:text-base font-bold text-emerald-950 flex items-center gap-2">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-emerald-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                  </svg>
                  Halaqah Binaan
                </h2>
                <span class="text-xs bg-emerald-50 text-emerald-800 px-2 py-1 rounded font-semibold">{{ groups.length }} Kelompok</span>
              </div>

              <!-- Group Selection List -->
              <div class="space-y-3">
                <button 
                  v-for="group in groups" 
                  :key="group.id"
                  @click="changeGroup(group.id)"
                  class="w-full text-left p-4 rounded-xl border transition-all duration-200"
                  :class="selectedGroupId === group.id 
                    ? 'border-emerald-600 bg-emerald-50/50 shadow-sm ring-1 ring-emerald-600/20' 
                    : 'border-gray-100 hover:border-emerald-200 hover:bg-gray-50/50'"
                >
                  <div class="flex justify-between items-start">
                    <div>
                      <h3 class="font-bold text-gray-900 text-sm sm:text-base">{{ group.name }}</h3>
                      <p class="text-xs text-gray-500 mt-1">Ketua: <strong class="text-emerald-700">{{ group.leader.name }}</strong></p>
                    </div>
                    <span class="text-xs font-semibold text-emerald-800 bg-emerald-100/70 px-2.5 py-0.5 rounded-full">
                      {{ group.attendance_rate }} Hadir
                    </span>
                  </div>

                  <div class="mt-3 pt-3 border-t border-dashed border-gray-100 flex items-center justify-between text-xs">
                    <div class="flex items-center space-x-1">
                      <span 
                        class="h-2 w-2 rounded-full" 
                        :class="group.is_delegated ? 'bg-amber-400' : 'bg-gray-300'"
                      ></span>
                      <span class="text-gray-500 font-medium">
                        {{ group.is_delegated ? 'Delegasi Aktif' : 'Tanpa Delegasi' }}
                      </span>
                    </div>
                    <span class="text-gray-400 font-semibold text-[11px]">{{ group.members_count }} Anggota</span>
                  </div>
                </button>
              </div>
            </div>

            <!-- Quick Action Buttons Card -->
            <div class="bg-white rounded-xl shadow-sm border border-emerald-100 p-6 space-y-3">
              <h3 class="text-xs font-bold text-emerald-950 uppercase tracking-wider mb-2">Aksi Cepat Pembina</h3>
              
              <button @click="openAddActivity" class="w-full bg-emerald-750 hover:bg-emerald-800 text-white font-semibold text-sm py-2.5 px-4 rounded-lg shadow-sm flex items-center justify-center space-x-2 transition-all">
                <span>Buat Sesi Kajian Baru</span>
              </button>

              <button @click="openUploadMaterial" class="w-full bg-emerald-750 hover:bg-emerald-855 text-white font-semibold text-sm py-2.5 px-4 rounded-lg shadow-sm flex items-center justify-center space-x-2 transition-all">
                <span>Unggah Materi Kajian</span>
              </button>
              
              <button @click="openBulkGrade" class="w-full bg-white border border-emerald-600 text-emerald-700 hover:bg-emerald-50 font-semibold text-sm py-2.5 px-4 rounded-lg flex items-center justify-center space-x-2 transition-all">
                <span>Buat Rekap Nilai Bulanan</span>
              </button>
            </div>
          </div>

          <!-- Right Column: Interactive Attendance Verification -->
          <div class="lg:col-span-2 space-y-6">
            <div v-if="activeGroup" class="bg-white rounded-xl shadow-sm border border-emerald-100 overflow-hidden">
              <div class="p-6 bg-gradient-to-r from-emerald-50 to-emerald-100/50 border-b border-emerald-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                  <span class="text-xs text-emerald-700 font-bold uppercase block">Verifikasi & Approval</span>
                  <h2 class="text-lg font-bold text-emerald-950 mt-0.5">Kehadiran: {{ activeGroup.name }}</h2>
                  <div class="text-xs text-gray-500 mt-1 flex flex-wrap gap-x-3 gap-y-1">
                    <span>Materi: <strong>{{ activeActivity?.topic || 'Belum ada kajian aktif' }}</strong></span>
                    <span class="hidden sm:inline text-emerald-300">|</span>
                    <span>Jadwal: <strong>{{ activeActivity ? new Date(activeActivity.date).toLocaleDateString('id-ID', {day: 'numeric', month: 'short', year: 'numeric'}) : '—' }}</strong></span>
                  </div>
                </div>

                <button 
                  @click="openDelegation" 
                  class="px-3.5 py-1.5 rounded-lg text-xs font-semibold shadow-sm transition-all border inline-flex items-center space-x-1.5"
                  :class="activeGroup.is_delegated 
                    ? 'bg-amber-50 border-amber-200 text-amber-800 hover:bg-amber-100' 
                    : 'bg-emerald-700 border-emerald-700 text-white hover:bg-emerald-800'"
                >
                  <span>{{ activeGroup.is_delegated ? 'Kelola Delegasi' : 'Delegasikan Akses' }}</span>
                </button>
              </div>

              <div v-if="activeGroup.is_delegated" class="bg-amber-50/60 border-b border-amber-100 px-6 py-3 text-xs text-amber-800 flex items-center space-x-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-amber-600 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zm-1 9a1 1 0 100-2v-3a1 1 0 00-1-1H9a1 1 0 100 2v3a1 1 0 001 1h1z" clip-rule="evenodd" />
                </svg>
                <span>
                  Delegasi approval absensi sedang <strong>Aktif</strong> untuk ketua kelompok (<strong>{{ activeGroup.leader.name }}</strong>)
                  <span v-if="activeGroup.delegated_until"> hingga {{ new Date(activeGroup.delegated_until).toLocaleString('id-ID', {dateStyle: 'medium', timeStyle: 'short'}) }}</span>.
                </span>
              </div>

              <div class="divide-y divide-gray-100">
                <div class="divide-y divide-gray-100">
                  <div v-for="member in activeGroup.members" :key="member.id" class="p-4 sm:px-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 hover:bg-gray-50/40 transition-colors">
                    <div class="flex items-center space-x-3">
                      <div class="h-8 w-8 rounded-full bg-emerald-700/10 text-emerald-800 font-bold flex items-center justify-center text-xs">
                        {{ member.name.replace('Akh ', '').charAt(0) }}
                      </div>
                      <div>
                        <span class="text-sm font-semibold text-gray-900 block">{{ member.name }}</span>
                        <span class="text-xs text-gray-400">Mutarabbi</span>
                      </div>
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
                          @click="approveMember(activeActivity, member)"
                          class="bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-200 text-xs font-bold py-1 px-2.5 rounded-lg shadow-sm transition-colors"
                        >
                          Setujui
                        </button>
                        <button 
                          type="button" 
                          @click="rejectMember(activeActivity, member)"
                          class="bg-red-50 hover:bg-red-100 text-red-700 border border-red-200 text-xs font-bold py-1 px-2.5 rounded-lg shadow-sm transition-colors"
                        >
                          Tolak
                        </button>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="p-6 bg-gray-50/50">
                  <span class="text-xs text-gray-500 font-medium">
                    ℹ️ Validasi absensi dilakukan dengan meninjau status check-in mandiri anggota kelompok, lalu menyetujui (Setujui) atau menolak (Tolak). Menolak akan menghapus log check-in anggota tersebut.
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- TAB 2: MATERIALS LIST & CRUD -->
        <div v-if="activeTab === 'materials'" class="bg-white rounded-xl shadow-sm border border-emerald-100 p-6 animate-slide-in">
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <div>
              <h2 class="text-base sm:text-lg font-bold text-emerald-950">Materi Pembinaan</h2>
              <p class="text-xs text-gray-500 font-medium">Unduh dokumen materi kajian atau unggah modul pembinaan baru.</p>
            </div>
            <button 
              @click="openUploadMaterial"
              class="bg-emerald-700 hover:bg-emerald-800 text-white font-bold text-xs py-2.5 px-4 rounded-lg shadow-md transition-all flex items-center justify-center gap-1"
            >
              <span>+ Unggah Materi Baru</span>
            </button>
          </div>

          <div class="mb-4">
            <input 
              type="text" 
              v-model="searchMaterialQuery" 
              placeholder="Cari materi..."
              class="w-full sm:max-w-md border border-gray-250 rounded-lg p-2.5 text-xs focus:ring-1 focus:ring-emerald-500 outline-none shadow-sm"
            />
          </div>

          <!-- Mobile view: cards list -->
          <div class="block sm:hidden space-y-3">
            <div v-for="mat in filteredMaterials" :key="mat.id" class="p-4 rounded-xl border border-gray-150 bg-[#FAFAF9]/40 space-y-3 animate-slide-in">
              <div>
                <h3 class="font-bold text-gray-900 text-sm leading-snug">{{ mat.title }}</h3>
                <p class="text-xs text-gray-500 mt-1">{{ mat.content || '—' }}</p>
              </div>
              <div class="flex justify-between items-center text-[10px] text-gray-400">
                <span>Penerbit: <strong class="text-emerald-700">{{ mat.ustad_name }}</strong></span>
                <span>{{ mat.published_at }}</span>
              </div>
              <div class="pt-2 border-t border-dashed border-gray-200 flex justify-between items-center gap-2">
                <a 
                  v-if="mat.file_path" 
                  :href="`/materials/${mat.id}/download`" 
                  class="bg-emerald-50 border border-emerald-250 text-emerald-800 text-[10px] font-bold py-1 px-2.5 rounded hover:bg-emerald-100"
                >
                  Unduh Berkas
                </a>
                <span v-else class="text-[10px] text-gray-400">Teks Saja</span>

                <button 
                  v-if="mat.can_delete"
                  @click="deleteMaterial(mat.id)" 
                  class="text-[10px] text-red-600 hover:text-red-950 font-bold border border-red-200 bg-red-50 py-1 px-2.5 rounded"
                >
                  Hapus
                </button>
              </div>
            </div>
          </div>

          <!-- Desktop view: table -->
          <div class="hidden sm:block overflow-x-auto rounded-lg border border-gray-150">
            <table class="w-full text-left border-collapse text-xs">
              <thead>
                <tr class="bg-gray-50 font-bold text-gray-500 uppercase tracking-wider border-b border-gray-150">
                  <th class="py-3 px-6">Judul Materi</th>
                  <th class="py-3 px-6">Deskripsi/Ringkasan</th>
                  <th class="py-3 px-6">Penerbit</th>
                  <th class="py-3 px-6">Tanggal Rilis</th>
                  <th class="py-3 px-6 text-center">Berkas</th>
                  <th class="py-3 px-6 text-center font-bold">Aksi</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100">
                <tr v-for="mat in filteredMaterials" :key="mat.id" class="hover:bg-gray-50/40">
                  <td class="py-3 px-6 font-bold text-gray-900">{{ mat.title }}</td>
                  <td class="py-3 px-6 text-gray-500 max-w-xs truncate">{{ mat.content || '—' }}</td>
                  <td class="py-3 px-6 text-emerald-800 font-semibold">{{ mat.ustad_name }}</td>
                  <td class="py-3 px-6 text-gray-500">{{ mat.published_at }}</td>
                  <td class="py-3 px-6 text-center">
                    <a 
                      v-if="mat.file_path" 
                      :href="`/materials/${mat.id}/download`" 
                      class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-2 py-0.5 rounded font-bold hover:bg-emerald-100"
                    >
                      Unduh Berkas
                    </a>
                    <span v-else class="text-gray-400">Teks Saja</span>
                  </td>
                  <td class="py-3 px-6 text-center">
                    <button 
                      v-if="mat.can_delete"
                      @click="deleteMaterial(mat.id)" 
                      class="text-[10px] text-red-650 hover:text-red-900 font-bold"
                    >
                      Hapus
                    </button>
                    <span v-else class="text-gray-400">—</span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- TAB 3: ACTIVITIES (SESI KAJIAN) CRUD -->
        <div v-if="activeTab === 'activities'" class="bg-white rounded-xl shadow-sm border border-emerald-100 p-6 animate-slide-in">
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <div>
              <h2 class="text-base sm:text-lg font-bold text-emerald-950">Sesi Kajian Halaqah</h2>
              <p class="text-xs text-gray-500 font-medium">Kelola aktivitas pertemuan halaqah binaan Anda. Buat sesi baru, edit agenda, atau lakukan verifikasi absensi.</p>
            </div>
            <button 
              @click="openAddActivity"
              class="bg-emerald-700 hover:bg-emerald-800 text-white font-bold text-xs py-2.5 px-4 rounded-lg shadow-md transition-all flex items-center justify-center gap-1"
            >
              <span>+ Buat Sesi Kajian Baru</span>
            </button>
          </div>

          <div class="mb-4">
            <input 
              type="text" 
              v-model="searchActivityQuery" 
              placeholder="Cari sesi kajian..."
              class="w-full sm:max-w-md border border-gray-250 rounded-lg p-2.5 text-xs focus:ring-1 focus:ring-emerald-500 outline-none shadow-sm"
            />
          </div>

          <!-- Mobile view: cards list -->
          <div class="block sm:hidden space-y-3">
            <div v-for="act in filteredActivities" :key="act.id" class="p-4 rounded-xl border border-gray-150 bg-[#FAFAF9]/40 space-y-3 animate-slide-in">
              <div>
                <div class="flex justify-between items-start gap-2">
                  <span class="font-bold text-gray-950 text-sm block leading-snug">{{ act.topic }}</span>
                  <span class="bg-emerald-50 text-emerald-800 text-[10px] font-bold px-2 py-0.5 rounded border border-emerald-100 flex-shrink-0">
                    {{ act.group_name }}
                  </span>
                </div>
                <p class="text-xs text-gray-500 mt-1">{{ act.description || '—' }}</p>
              </div>
              <div class="text-[10px] text-gray-400 font-semibold font-sans">
                Jadwal: {{ act.date_human }}
              </div>
              <div class="pt-2 border-t border-dashed border-gray-200 flex items-center justify-end space-x-2">
                <button @click="openAttendanceApproval(act)" class="text-[10px] text-blue-600 hover:text-blue-800 font-bold border border-blue-200 bg-blue-50 py-1 px-2.5 rounded">Input/Edit Absen</button>
                <button @click="openEditActivity(act)" class="text-[10px] text-emerald-700 hover:text-emerald-950 font-bold border border-emerald-200 bg-emerald-50 py-1 px-2.5 rounded">Edit</button>
                <button @click="deleteActivity(act.id)" class="text-[10px] text-red-655 hover:text-red-900 font-bold border border-red-200 bg-red-50 py-1 px-2.5 rounded">Hapus</button>
              </div>
            </div>
          </div>

          <!-- Desktop view: table -->
          <div class="hidden sm:block overflow-x-auto rounded-lg border border-gray-150">
            <table class="w-full text-left border-collapse text-xs">
              <thead>
                <tr class="bg-gray-50 font-bold text-gray-500 uppercase tracking-wider border-b border-gray-150">
                  <th class="py-3 px-6">Halaqah</th>
                  <th class="py-3 px-6">Topik Pembahasan</th>
                  <th class="py-3 px-6">Jadwal Sesi</th>
                  <th class="py-3 px-6">Deskripsi Kajian</th>
                  <th class="py-3 px-6 text-center font-bold">Aksi</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100">
                <tr v-for="act in filteredActivities" :key="act.id" class="hover:bg-gray-50/40">
                  <td class="py-3 px-6 font-bold text-gray-900">{{ act.group_name }}</td>
                  <td class="py-3 px-6 text-emerald-800 font-semibold">{{ act.topic }}</td>
                  <td class="py-3 px-6 text-gray-600 font-semibold">{{ act.date_human }}</td>
                  <td class="py-3 px-6 text-gray-500 max-w-xs truncate">{{ act.description || '—' }}</td>
                  <td class="py-3 px-6 text-center space-x-2">
                    <button @click="openAttendanceApproval(act)" class="text-[10px] text-blue-600 hover:text-blue-800 font-bold">Input/Edit Absen</button>
                    <button @click="openEditActivity(act)" class="text-[10px] text-emerald-700 hover:text-emerald-950 font-bold">Edit</button>
                    <button @click="deleteActivity(act.id)" class="text-[10px] text-red-655 hover:text-red-900 font-bold">Hapus</button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- TAB 4: ATTENDANCES (LOG PRESENSI) -->
        <div v-if="activeTab === 'attendances'" class="bg-white rounded-xl shadow-sm border border-emerald-100 p-6 animate-slide-in">
          <div class="mb-6 border-b border-gray-100 pb-3">
            <h2 class="text-base sm:text-lg font-bold text-emerald-950">Log Presensi Anggota</h2>
            <p class="text-xs text-gray-500 font-medium">Rekaman kehadiran mutarabbi pada sesi halaqah binaan Anda.</p>
          </div>

          <div class="mb-4">
            <input 
              type="text" 
              v-model="searchAttendanceQuery" 
              placeholder="Cari log absensi..."
              class="w-full sm:max-w-md border border-gray-250 rounded-lg p-2.5 text-xs focus:ring-1 focus:ring-emerald-500 outline-none shadow-sm"
            />
          </div>

          <!-- Mobile view: cards list -->
          <div class="block sm:hidden space-y-3">
            <div v-for="att in filteredAttendances" :key="att.id" class="p-4 rounded-xl border border-gray-150 bg-[#FAFAF9]/40 space-y-2.5 animate-slide-in">
              <div class="flex justify-between items-center gap-2">
                <span class="font-bold text-gray-900 text-sm block leading-snug">{{ att.user_name }}</span>
                <span 
                  class="px-2 py-0.5 rounded text-[10px] font-bold border flex-shrink-0"
                  :class="{
                    'bg-emerald-50 border-emerald-200 text-emerald-700': att.status === 'present',
                    'bg-amber-50 border-amber-200 text-amber-700': att.status === 'sick',
                    'bg-blue-50 border-blue-200 text-blue-700': att.status === 'permission',
                    'bg-red-50 border-red-200 text-red-700': att.status === 'absent'
                  }"
                >
                  {{ att.status === 'present' ? 'Hadir' : (att.status === 'sick' ? 'Sakit' : (att.status === 'permission' ? 'Izin' : 'Alpa')) }}
                </span>
              </div>
              <div class="text-xs text-gray-600">
                Topik: <strong class="text-gray-800">{{ att.activity_topic }}</strong>
              </div>
              <div class="text-[10px] text-gray-400 space-y-1">
                <div>Verifikator: <strong class="text-emerald-700">{{ att.approved_by }}</strong></div>
                <div>Waktu: {{ att.approved_at }}</div>
              </div>
              <div class="pt-2 border-t border-dashed border-gray-200 flex justify-end">
                <button @click="deleteAttendance(att.id)" class="text-[10px] text-red-655 hover:text-red-900 font-bold border border-red-200 bg-red-50 py-1 px-2.5 rounded">Hapus</button>
              </div>
            </div>
          </div>

          <!-- Desktop view: table -->
          <div class="hidden sm:block overflow-x-auto rounded-lg border border-gray-150">
            <table class="w-full text-left border-collapse text-xs">
              <thead>
                <tr class="bg-gray-50 font-bold text-gray-500 uppercase tracking-wider border-b border-gray-150">
                  <th class="py-3 px-6">Nama Anggota</th>
                  <th class="py-3 px-6">Sesi Kajian (Topik)</th>
                  <th class="py-3 px-6">Status Kehadiran</th>
                  <th class="py-3 px-6">Diverifikasi Oleh</th>
                  <th class="py-3 px-6">Waktu Validasi</th>
                  <th class="py-3 px-6 text-center font-bold">Aksi</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100">
                <tr v-for="att in filteredAttendances" :key="att.id" class="hover:bg-gray-50/40">
                  <td class="py-3 px-6 font-bold text-gray-900">{{ att.user_name }}</td>
                  <td class="py-3 px-6 text-gray-600">{{ att.activity_topic }}</td>
                  <td class="py-3 px-6">
                    <span 
                      class="px-2.5 py-0.5 rounded text-[10px] font-bold border"
                      :class="{
                        'bg-emerald-50 border-emerald-200 text-emerald-700': att.status === 'present',
                        'bg-amber-50 border-amber-200 text-amber-700': att.status === 'sick',
                        'bg-blue-50 border-blue-200 text-blue-700': att.status === 'permission',
                        'bg-red-50 border-red-200 text-red-700': att.status === 'absent'
                      }"
                    >
                      {{ att.status === 'present' ? 'Hadir' : (att.status === 'sick' ? 'Sakit' : (att.status === 'permission' ? 'Izin' : 'Alpa')) }}
                    </span>
                  </td>
                  <td class="py-3 px-6 text-emerald-800 font-semibold">{{ att.approved_by }}</td>
                  <td class="py-3 px-6 text-gray-500">{{ att.approved_at }}</td>
                  <td class="py-3 px-6 text-center">
                    <button @click="deleteAttendance(att.id)" class="text-[10px] text-red-600 hover:text-red-900 font-bold">Hapus</button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- TAB 5: GRADES (PENILAIAN BULANAN) -->
        <div v-if="activeTab === 'grades'" class="bg-white rounded-xl shadow-sm border border-emerald-100 p-6 animate-slide-in">
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <div>
              <h2 class="text-base sm:text-lg font-bold text-emerald-950">Penilaian Bulanan</h2>
              <p class="text-xs text-gray-500 font-medium">Rekap pencapaian perkembangan bulanan mutarabbi binaan Anda.</p>
            </div>
            <button 
              @click="openBulkGrade"
              class="bg-emerald-700 hover:bg-emerald-800 text-white font-bold text-xs py-2.5 px-4 rounded-lg shadow-md transition-all flex items-center justify-center gap-1"
            >
              <span>+ Masukkan Rekap Nilai</span>
            </button>
          </div>

          <div class="mb-4">
            <input 
              type="text" 
              v-model="searchGradeQuery" 
              placeholder="Cari nilai..."
              class="w-full sm:max-w-md border border-gray-250 rounded-lg p-2.5 text-xs focus:ring-1 focus:ring-emerald-500 outline-none shadow-sm"
            />
          </div>

          <!-- Mobile view: cards list -->
          <div class="block sm:hidden space-y-3">
            <div v-for="gr in filteredGrades" :key="gr.id" class="p-4 rounded-xl border border-gray-150 bg-[#FAFAF9]/40 space-y-2.5 animate-slide-in">
              <div class="flex justify-between items-center gap-2">
                <span class="font-bold text-gray-900 text-sm block leading-snug">{{ gr.user_name }}</span>
                <span class="bg-emerald-850 text-white font-bold text-xs px-2.5 py-1 rounded-lg shadow-sm border border-emerald-800 flex-shrink-0">
                  <span class="text-[9px] text-amber-300">Nilai:</span> {{ gr.score }}
                </span>
              </div>
              <div class="text-[10px] text-gray-400 font-semibold font-sans">
                Periode: Bulan {{ gr.month }}, {{ gr.year }}
              </div>
              <p class="text-xs text-gray-500 italic">"{{ gr.notes || '—' }}"</p>
              <div class="pt-2 border-t border-dashed border-gray-200 flex justify-between items-center text-[10px] text-gray-400">
                <span>Penilai: <strong class="text-emerald-700">{{ gr.ustad_name }}</strong></span>
                <button 
                  v-if="gr.can_delete"
                  @click="deleteGrade(gr.id)" 
                  class="text-[10px] text-red-655 hover:text-red-900 font-bold border border-red-200 bg-red-50 py-1 px-2.5 rounded"
                >
                  Hapus
                </button>
              </div>
            </div>
          </div>

          <!-- Desktop view: table -->
          <div class="hidden sm:block overflow-x-auto rounded-lg border border-gray-150">
            <table class="w-full text-left border-collapse text-xs">
              <thead>
                <tr class="bg-gray-50 font-bold text-gray-500 uppercase tracking-wider border-b border-gray-150">
                  <th class="py-3 px-6">Nama Anggota</th>
                  <th class="py-3 px-6">Pembina Penilai</th>
                  <th class="py-3 px-6">Periode</th>
                  <th class="py-3 px-6 text-center">Skor (Indeks 100)</th>
                  <th class="py-3 px-6">Catatan Perkembangan</th>
                  <th class="py-3 px-6 text-center font-bold">Aksi</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100">
                <tr v-for="gr in filteredGrades" :key="gr.id" class="hover:bg-gray-50/40">
                  <td class="py-3 px-6 font-bold text-gray-900">{{ gr.user_name }}</td>
                  <td class="py-3 px-6 text-emerald-800 font-semibold">{{ gr.ustad_name }}</td>
                  <td class="py-3 px-6 text-gray-600 font-semibold font-sans">Bulan {{ gr.month }}, {{ gr.year }}</td>
                  <td class="py-3 px-6 text-center font-bold text-emerald-700 text-sm">{{ gr.score }}</td>
                  <td class="py-3 px-6 text-gray-500 max-w-xs truncate">{{ gr.notes || '—' }}</td>
                  <td class="py-3 px-6 text-center">
                    <button 
                      v-if="gr.can_delete"
                      @click="deleteGrade(gr.id)" 
                      class="text-[10px] text-red-600 hover:text-red-900 font-bold"
                    >
                      Hapus
                    </button>
                    <span v-else class="text-gray-400">—</span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

      </div>
    </main>

    <!-- SUCCESS TOAST NOTIFICATION -->
    <div v-if="showSuccessNotification" class="fixed bottom-5 left-4 right-4 sm:left-auto sm:right-5 sm:w-auto z-50 bg-emerald-950 text-white py-3 px-4 sm:px-5 rounded-xl shadow-2xl flex items-center space-x-3 border border-emerald-800 animate-slide-in">
      <div class="p-1 bg-amber-400 text-emerald-950 rounded-full flex-shrink-0">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
        </svg>
      </div>
      <span class="text-xs sm:text-sm font-semibold">{{ notificationMessage }}</span>
    </div>

    <!-- MODAL: DELEGASI -->
    <div v-if="showDelegationModal && activeGroup" class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4" role="dialog" aria-modal="true">
      <div class="fixed inset-0 bg-emerald-950/40 backdrop-blur-sm" @click="showDelegationModal = false"></div>
      <div class="relative bg-white rounded-t-2xl sm:rounded-2xl w-full sm:max-w-lg max-h-[90vh] overflow-y-auto shadow-2xl border border-emerald-50 animate-slide-up">
        <div class="bg-emerald-800 text-white px-5 py-4 flex items-center justify-between sticky top-0 z-10">
          <h3 class="text-base font-bold">Delegasi Approval Absensi</h3>
          <button @click="showDelegationModal = false" class="text-emerald-200 hover:text-white transition-colors p-1">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
        <form @submit.prevent="submitDelegation" class="p-5 space-y-4">
          <p class="text-xs text-gray-500 font-medium">
            Delegasikan hak absensi kepada Ketua Kelompok (<strong class="text-emerald-900">{{ activeGroup.leader.name }}</strong>) jika Anda berhalangan hadir.
          </p>
          <div class="flex items-center justify-between p-3.5 bg-emerald-50/50 rounded-xl border border-emerald-100">
            <div>
              <span class="text-sm font-semibold text-emerald-955 block">Status Delegasi</span>
              <span class="text-xs text-gray-400">Aktifkan hak akses approval Ketua Kelompok</span>
            </div>
            <button type="button" @click="delegationForm.is_delegated = !delegationForm.is_delegated"
              class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 focus:outline-none"
              :class="delegationForm.is_delegated ? 'bg-emerald-600' : 'bg-gray-200'">
              <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200"
                :class="delegationForm.is_delegated ? 'translate-x-5' : 'translate-x-0'"></span>
            </button>
          </div>
          <div v-if="delegationForm.is_delegated" class="space-y-2">
            <label for="delegated_until" class="text-xs font-bold text-gray-500 uppercase block">Delegasikan Hingga</label>
            <input type="datetime-local" id="delegated_until" v-model="delegationForm.delegated_until" class="w-full border border-gray-200 rounded-lg p-2.5 text-sm focus:ring-1 focus:ring-emerald-500 outline-none" />
          </div>
          <div class="pt-4 border-t border-gray-100 flex gap-3">
            <button type="button" @click="showDelegationModal = false" class="flex-1 bg-white border border-gray-200 text-gray-700 px-4 py-2.5 rounded-lg text-sm font-medium hover:bg-gray-50 transition-colors">Batal</button>
            <button type="submit" class="flex-1 bg-emerald-700 hover:bg-emerald-800 text-white px-5 py-2.5 rounded-lg text-sm font-bold shadow-sm transition-colors">Simpan</button>
          </div>
        </form>
      </div>
    </div>

    <!-- MODAL: UPLOAD MATERIAL -->
    <div v-if="showMaterialModal" class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4" role="dialog" aria-modal="true">
      <div class="fixed inset-0 bg-emerald-950/40 backdrop-blur-sm" @click="showMaterialModal = false"></div>
      <div class="relative bg-white rounded-t-2xl sm:rounded-2xl w-full sm:max-w-md max-h-[90vh] overflow-y-auto shadow-2xl border border-emerald-50 animate-slide-up">
        <div class="bg-emerald-800 text-white px-5 py-4 flex items-center justify-between sticky top-0 z-10">
          <h3 class="text-base font-bold">Unggah Materi Kajian Baru</h3>
          <button @click="showMaterialModal = false" class="text-emerald-200 hover:text-white transition-colors p-1">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
        <form @submit.prevent="submitMaterial" class="p-5 space-y-4">
          <div class="space-y-1">
            <label for="mat_title" class="text-xs font-bold text-gray-500 uppercase block">Judul Materi</label>
            <input type="text" id="mat_title" v-model="materialForm.title" required class="w-full border border-gray-200 rounded-lg p-2.5 text-sm focus:ring-1 focus:ring-emerald-500 outline-none" />
          </div>
          <div class="space-y-1">
            <label for="mat_content" class="text-xs font-bold text-gray-500 uppercase block">Ringkasan / Konten</label>
            <textarea id="mat_content" v-model="materialForm.content" rows="3" class="w-full border border-gray-200 rounded-lg p-2.5 text-sm focus:ring-1 focus:ring-emerald-500 outline-none"></textarea>
          </div>
          <div class="space-y-1">
            <label for="mat_file" class="text-xs font-bold text-gray-500 uppercase block">Dokumen Lampiran (PDF, Word, PPT)</label>
            <input type="file" id="mat_file" @input="materialForm.file = $event.target.files[0]" class="w-full border border-gray-200 rounded-lg p-2 text-xs focus:ring-1 focus:ring-emerald-500 outline-none" />
          </div>
          <div class="pt-4 border-t border-gray-100 flex gap-3">
            <button type="button" @click="showMaterialModal = false" class="flex-1 bg-white border border-gray-200 text-gray-700 px-4 py-2.5 rounded-lg text-sm font-medium hover:bg-gray-50 transition-colors">Batal</button>
            <button type="submit" class="flex-1 bg-emerald-700 hover:bg-emerald-800 text-white px-5 py-2.5 rounded-lg text-sm font-bold shadow-sm transition-colors">Unggah</button>
          </div>
        </form>
      </div>
    </div>

    <!-- MODAL: BULK GRADE -->
    <div v-if="showGradeBulkModal && activeGroup" class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4" role="dialog" aria-modal="true">
      <div class="fixed inset-0 bg-emerald-950/40 backdrop-blur-sm" @click="showGradeBulkModal = false"></div>
      <div class="relative bg-white rounded-t-2xl sm:rounded-2xl w-full sm:max-w-xl max-h-[90vh] overflow-y-auto shadow-2xl border border-emerald-50 animate-slide-up">
        <div class="bg-emerald-800 text-white px-5 py-4 flex items-center justify-between sticky top-0 z-10">
          <h3 class="text-base font-bold truncate mr-2">Rekap Nilai: {{ activeGroup.name }}</h3>
          <button @click="showGradeBulkModal = false" class="text-emerald-200 hover:text-white transition-colors p-1 flex-shrink-0">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
        <form @submit.prevent="submitBulkGrades" class="p-5 space-y-4">
          <div class="grid grid-cols-2 gap-3">
            <div class="space-y-1">
              <label for="grd_month" class="text-xs font-bold text-gray-500 uppercase block">Bulan</label>
              <select id="grd_month" v-model="gradeBulkForm.month" required class="w-full border border-gray-200 rounded-lg p-2 text-sm focus:ring-1 focus:ring-emerald-500 outline-none">
                <option v-for="m in 12" :key="m" :value="m">Bulan {{ m }}</option>
              </select>
            </div>
            <div class="space-y-1">
              <label for="grd_year" class="text-xs font-bold text-gray-500 uppercase block">Tahun</label>
              <input type="number" id="grd_year" v-model="gradeBulkForm.year" required min="2020" max="2100" class="w-full border border-gray-200 rounded-lg p-2 text-sm focus:ring-1 focus:ring-emerald-500 outline-none" />
            </div>
          </div>
          <div class="divide-y divide-gray-100 border border-gray-150 rounded-lg max-h-[50vh] overflow-y-auto">
            <div v-for="(item, idx) in gradeBulkForm.grades" :key="item.user_id" class="p-4 space-y-3">
              <div class="flex justify-between items-center">
                <span class="text-xs font-bold text-emerald-900">{{ item.name }}</span>
                <span class="text-xs font-bold bg-emerald-50 text-emerald-800 border border-emerald-100 py-1 px-2.5 rounded-lg">
                  Skor: {{ calculateTotalScore(item) }}/100
                </span>
              </div>
              <div class="grid grid-cols-3 sm:grid-cols-6 gap-2">
                <div>
                  <label class="text-[9px] text-gray-400 block font-semibold">Kehadiran (25)</label>
                  <input type="number" v-model.number="gradeBulkForm.grades[idx].kehadiran" min="0" max="25" class="w-full border border-gray-200 p-1.5 rounded text-xs outline-none text-center" />
                </div>
                <div>
                  <label class="text-[9px] text-gray-400 block font-semibold">Kedisiplinan (10)</label>
                  <input type="number" v-model.number="gradeBulkForm.grades[idx].kedisiplinan" min="0" max="10" class="w-full border border-gray-200 p-1.5 rounded text-xs outline-none text-center" />
                </div>
                <div>
                  <label class="text-[9px] text-gray-400 block font-semibold">Partisipasi (10)</label>
                  <input type="number" v-model.number="gradeBulkForm.grades[idx].partisipasi" min="0" max="10" class="w-full border border-gray-200 p-1.5 rounded text-xs outline-none text-center" />
                </div>
                <div>
                  <label class="text-[9px] text-gray-400 block font-semibold">Sikap/Etika (10)</label>
                  <input type="number" v-model.number="gradeBulkForm.grades[idx].sikap" min="0" max="10" class="w-full border border-gray-200 p-1.5 rounded text-xs outline-none text-center" />
                </div>
                <div>
                  <label class="text-[9px] text-gray-400 block font-semibold">Pemahaman (20)</label>
                  <input type="number" v-model.number="gradeBulkForm.grades[idx].pemahaman" min="0" max="20" class="w-full border border-gray-200 p-1.5 rounded text-xs outline-none text-center" />
                </div>
                <div>
                  <label class="text-[9px] text-gray-400 block font-semibold">Al-Quran (25)</label>
                  <input type="number" v-model.number="gradeBulkForm.grades[idx].bacaan" min="0" max="25" class="w-full border border-gray-200 p-1.5 rounded text-xs outline-none text-center" />
                </div>
              </div>
              <div>
                <input type="text" v-model="gradeBulkForm.grades[idx].notes_text" placeholder="Catatan perkembangan..." class="w-full border border-gray-200 p-2 rounded text-xs outline-none" />
              </div>
            </div>
          </div>
          <div class="pt-3 border-t border-gray-100 flex gap-3">
            <button type="button" @click="showGradeBulkModal = false" class="flex-1 bg-white border border-gray-200 text-gray-700 px-4 py-2.5 rounded-lg text-sm font-medium hover:bg-gray-50 transition-colors">Batal</button>
            <button type="submit" class="flex-1 bg-emerald-700 hover:bg-emerald-800 text-white px-5 py-2.5 rounded-lg text-sm font-bold shadow-sm transition-colors">Simpan Rekap</button>
          </div>
        </form>
      </div>
    </div>

    <!-- MODAL: ADD/EDIT ACTIVITY -->
    <div v-if="showActivityModal" class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4" role="dialog" aria-modal="true">
      <div class="fixed inset-0 bg-emerald-950/40 backdrop-blur-sm" @click="showActivityModal = false"></div>
      <div class="relative bg-white rounded-t-2xl sm:rounded-2xl w-full sm:max-w-md max-h-[90vh] overflow-y-auto shadow-2xl border border-emerald-50 animate-slide-up">
        <div class="bg-emerald-800 text-white px-5 py-4 flex items-center justify-between sticky top-0 z-10">
          <h3 class="text-base font-bold">{{ isEditingActivity ? 'Edit Sesi Kajian' : 'Buat Sesi Kajian Baru' }}</h3>
          <button @click="showActivityModal = false" class="text-emerald-200 hover:text-white transition-colors p-1">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
        <form @submit.prevent="submitActivity" class="p-5 space-y-4">
          <div v-if="!isEditingActivity" class="space-y-1">
            <label for="act_group" class="text-xs font-bold text-gray-500 uppercase block">Kelompok Halaqah</label>
            <select id="act_group" v-model="activityForm.group_id" required class="w-full border border-gray-200 rounded-lg p-2.5 text-sm focus:ring-1 focus:ring-emerald-500 outline-none">
              <option value="" disabled selected>Pilih Kelompok...</option>
              <option v-for="g in groups" :key="g.id" :value="g.id">{{ g.name }}</option>
            </select>
          </div>
          <div class="space-y-1">
            <label for="act_topic" class="text-xs font-bold text-gray-500 uppercase block">Topik Pembahasan</label>
            <input type="text" id="act_topic" v-model="activityForm.topic" required placeholder="e.g. Tafsir Surah Yasin" class="w-full border border-gray-200 rounded-lg p-2.5 text-sm focus:ring-1 focus:ring-emerald-500 outline-none" />
          </div>
          <div class="space-y-1">
            <label for="act_date" class="text-xs font-bold text-gray-500 uppercase block">Jadwal Sesi</label>
            <input type="datetime-local" id="act_date" v-model="activityForm.date" required class="w-full border border-gray-200 rounded-lg p-2.5 text-sm focus:ring-1 focus:ring-emerald-500 outline-none" />
          </div>
          <div class="space-y-1">
            <label for="act_desc" class="text-xs font-bold text-gray-500 uppercase block">Deskripsi Kajian</label>
            <textarea id="act_desc" v-model="activityForm.description" rows="3" class="w-full border border-gray-200 rounded-lg p-2.5 text-sm focus:ring-1 focus:ring-emerald-500 outline-none"></textarea>
          </div>
          <div class="pt-4 border-t border-gray-100 flex gap-3">
            <button type="button" @click="showActivityModal = false" class="flex-1 bg-white border border-gray-200 text-gray-700 px-4 py-2.5 rounded-lg text-sm font-medium hover:bg-gray-50 transition-colors">Batal</button>
            <button type="submit" class="flex-1 bg-emerald-700 hover:bg-emerald-800 text-white px-5 py-2.5 rounded-lg text-sm font-bold shadow-sm transition-colors">Simpan Sesi</button>
          </div>
        </form>
      </div>
    </div>

    <!-- MODAL: ATTENDANCE INPUT BULK (FROM ACTIVITIES TAB) -->
    <div v-if="showAttendanceModal" class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4" role="dialog" aria-modal="true">
      <div class="fixed inset-0 bg-emerald-950/40 backdrop-blur-sm" @click="showAttendanceModal = false"></div>
      <div class="relative bg-white rounded-t-2xl sm:rounded-2xl w-full sm:max-w-lg max-h-[90vh] overflow-y-auto shadow-2xl border border-emerald-50 animate-slide-up">
        <div class="bg-emerald-800 text-white px-5 py-4 flex items-center justify-between sticky top-0 z-10">
          <h3 class="text-base font-bold">Validasi Kehadiran Sesi</h3>
          <button @click="showAttendanceModal = false" class="text-emerald-200 hover:text-white transition-colors p-1">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
        <div class="p-6 space-y-4">
          <p class="text-xs text-gray-500 font-medium">Verifikasi presensi untuk topik: <strong class="text-emerald-950">{{ selectedActivity?.topic }}</strong></p>
          
          <div class="max-h-80 overflow-y-auto divide-y divide-gray-100">
            <div v-for="member in selectedActivityMembers" :key="member.id" class="py-3 flex items-center justify-between">
              <span class="text-xs font-semibold text-gray-800">{{ member.name }}</span>
              <div class="flex items-center space-x-2">
                <!-- Status Badge -->
                <span v-if="!member.status" class="bg-gray-100 text-gray-500 text-[10px] font-semibold px-2 py-0.5 rounded border border-gray-200">
                  Belum Check-in
                </span>
                <span v-else-if="member.status === 'present'" class="bg-emerald-50 text-emerald-700 text-[10px] font-bold px-2 py-0.5 rounded border border-emerald-200">
                  Hadir
                </span>
                <span v-else-if="member.status === 'sick'" class="bg-amber-50 text-amber-700 text-[10px] font-bold px-2 py-0.5 rounded border border-amber-200">
                  Sakit
                </span>
                <span v-else-if="member.status === 'permission'" class="bg-blue-50 text-blue-700 text-[10px] font-bold px-2 py-0.5 rounded border border-blue-200">
                  Izin
                </span>
                <span v-else-if="member.status === 'absent'" class="bg-red-50 text-red-700 text-[10px] font-bold px-2 py-0.5 rounded border border-red-200">
                  Alpa
                </span>

                <!-- Actions -->
                <div v-if="member.status" class="flex items-center space-x-1">
                  <span v-if="member.is_approved" class="text-[10px] bg-emerald-700 text-white font-bold px-1.5 py-0.5 rounded flex items-center space-x-0.5">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-2.5 w-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>Disetujui</span>
                  </span>
                  <button 
                    v-if="!member.is_approved"
                    type="button" 
                    @click="approveMember(selectedActivity, member)"
                    class="bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-200 text-[10px] font-bold py-0.5 px-2 rounded transition-colors"
                  >
                    Setujui
                  </button>
                  <button 
                    type="button" 
                    @click="rejectMember(selectedActivity, member)"
                    class="bg-red-55 hover:bg-red-100 text-red-700 border border-red-200 text-[10px] font-bold py-0.5 px-2 rounded transition-colors"
                  >
                    Tolak
                  </button>
                </div>
              </div>
            </div>
          </div>

          <div class="pt-4 border-t border-gray-100 flex justify-end">
            <button type="button" @click="showAttendanceModal = false" class="bg-white border border-gray-200 text-gray-700 px-5 py-2 rounded-lg text-sm font-medium hover:bg-gray-50 transition-colors">Tutup</button>
          </div>
        </div>
      </div>
    </div>

  </div>
</template>

<style scoped>
@keyframes slide-in {
  from { transform: translateY(20px); opacity: 0; }
  to { transform: translateY(0); opacity: 1; }
}
.animate-slide-in {
  animation: slide-in 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}
@keyframes slide-up {
  from { transform: translateY(100%); opacity: 0; }
  to { transform: translateY(0); opacity: 1; }
}
.animate-slide-up {
  animation: slide-up 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}
@media (min-width: 640px) {
  .animate-slide-up {
    animation: slide-in 0.25s cubic-bezier(0.16, 1, 0.3, 1) forwards;
  }
}
</style>
