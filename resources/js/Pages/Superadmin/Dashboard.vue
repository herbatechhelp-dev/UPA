<script setup>
import { ref, computed } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';

// Props passed from Laravel controller
const props = defineProps({
  auth: Object,
  roles: Array,
  users: Array,
  groups: Array,
  ustads: Array,
  potentialLeaders: Array,
  potentialMembers: Array,
  materials: Array,
  activities: Array,
  grades: Array,
  attendances: Array,
  systemStats: Object,
  settings: Object,
  pendingUsers: Array,
});

// Navigation States
const activeTab = ref('dashboard'); // 'dashboard' | 'users' | 'groups' | 'materials' | 'activities' | 'grades' | 'attendances' | 'pending'

// Notification States
const showSuccessNotification = ref(false);
const notificationMessage = ref('');

const triggerNotification = (msg) => {
  notificationMessage.value = msg;
  showSuccessNotification.value = true;
  setTimeout(() => showSuccessNotification.value = false, 4000);
};

// --- Modal & Form States ---
// User Modals
const showUserModal = ref(false);
const isEditingUser = ref(false);
const editingUserId = ref(null);
const userForm = useForm({
  name: '',
  email: '',
  password: '',
  role_id: ''
});

// Group Modals
const showGroupModal = ref(false);
const isEditingGroup = ref(false);
const editingGroupId = ref(null);
const groupForm = useForm({
  name: '',
  ustad_id: '',
  leader_id: ''
});

// Plotting Members Modals
const showPlottingModal = ref(false);
const plottingGroup = ref(null);
const plottingForm = useForm({
  user_ids: []
});

// Material Modals
const showMaterialModal = ref(false);
const isEditingMaterial = ref(false);
const editingMaterialId = ref(null);
const materialForm = useForm({
  title: '',
  content: '',
  ustad_id: '',
  file: null
});

// Activity Modals
const showActivityModal = ref(false);
const isEditingActivity = ref(false);
const editingActivityId = ref(null);
const activityForm = useForm({
  group_id: '',
  date: '',
  topic: '',
  description: ''
});

// Grade Modals
const showGradeModal = ref(false);
const isEditingGrade = ref(false);
const gradeForm = useForm({
  user_id: '',
  ustad_id: '',
  month: new Date().getMonth() + 1,
  year: new Date().getFullYear(),
  score: '',
  notes: ''
});

// Breakdown refs for single grade
const singleGradeKehadiran = ref(20);
const singleGradeKedisiplinan = ref(8);
const singleGradePartisipasi = ref(8);
const singleGradeSikap = ref(8);
const singleGradePemahaman = ref(16);
const singleGradeAlQuran = ref(20);
const singleGradeNotesText = ref('');

const computedSingleScore = computed(() => {
  return (singleGradeKehadiran.value || 0) +
         (singleGradeKedisiplinan.value || 0) +
         (singleGradePartisipasi.value || 0) +
         (singleGradeSikap.value || 0) +
         (singleGradePemahaman.value || 0) +
         (singleGradeAlQuran.value || 0);
});

// Attendance Approval Modal
const showAttendanceModal = ref(false);
const selectedActivity = ref(null);
const attendanceForm = useForm({
  attendances: []
});

// Registration Approval
const showApproveModal = ref(false);
const approvingUser = ref(null);
const approveForm = useForm({
  role_id: ''
});

// Import Users Form
const showImportModal = ref(false);
const importForm = useForm({
  file: null
});

const openImportModal = () => {
  importForm.reset();
  showImportModal.value = true;
};

const handleImportFileUpload = (event) => {
  importForm.file = event.target.files[0];
};

const submitImport = () => {
  if (!importForm.file) {
    alert('Silakan pilih file CSV terlebih dahulu.');
    return;
  }
  importForm.post('/users/import', {
    onSuccess: () => {
      showImportModal.value = false;
      importForm.reset();
      triggerNotification('Data pengguna berhasil diimpor!');
    }
  });
};

const downloadCSVTemplate = () => {
  const headers = ['Nama Lengkap', 'Email', 'No Telepon', 'Gender (ikhwan/akhwat)', 'Jurusan/Departemen', 'Peran (member/leader/ustad)', 'Nama Kelompok'];
  const sampleRow = ['Budi Santoso', 'budi@email.com', '08123456789', 'ikhwan', 'Teknik Informatika', 'member', 'Halaqah A'];
  const csvContent = "\uFEFF" + [headers.join(','), sampleRow.join(',')].join('\n');
  
  const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
  const url = URL.createObjectURL(blob);
  const link = document.createElement("a");
  link.setAttribute("href", url);
  link.setAttribute("download", "template_impor_pengguna.csv");
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
};

// Search and Filter States
const searchUserQuery = ref('');
const searchGroupQuery = ref('');
const searchMaterialQuery = ref('');
const searchActivityQuery = ref('');
const searchGradeQuery = ref('');
const searchAttendanceQuery = ref('');

// --- Filtered Computed Lists ---
const filteredUsers = computed(() => {
  if (!searchUserQuery.value) return props.users;
  const q = searchUserQuery.value.toLowerCase();
  return props.users.filter(u => u.name.toLowerCase().includes(q) || u.email.toLowerCase().includes(q) || u.role_name.toLowerCase().includes(q));
});

const filteredGroups = computed(() => {
  if (!searchGroupQuery.value) return props.groups;
  const q = searchGroupQuery.value.toLowerCase();
  return props.groups.filter(g => g.name.toLowerCase().includes(q) || g.ustad.toLowerCase().includes(q) || g.leader.toLowerCase().includes(q));
});

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


// --- CRUD Handlers ---

// Configuration Save
const configForm = useForm({
  enable_self_checkin: true,
  max_delegation_days: 14,
  maintenance_mode: false
});
const saveConfig = () => {
  triggerNotification('Konfigurasi server berhasil diperbarui!');
};

// Branding & Settings Form
const settingsForm = useForm({
  app_title: props.settings?.app_title || 'Unit Pembinaan Anggota',
  logo: null,
  favicon: null
});

const submitSettingsForm = () => {
  settingsForm.post('/superadmin/settings', {
    onSuccess: () => {
      triggerNotification('Pengaturan logo, favicon, dan judul web berhasil diperbarui!');
    }
  });
};

// Users CRUD
const openAddUser = () => {
  isEditingUser.value = false;
  editingUserId.value = null;
  userForm.reset();
  showUserModal.value = true;
};

const openEditUser = (user) => {
  isEditingUser.value = true;
  editingUserId.value = user.id;
  userForm.name = user.name;
  userForm.email = user.email;
  userForm.password = '';
  userForm.role_id = user.role_id;
  showUserModal.value = true;
};

const submitUserForm = () => {
  if (isEditingUser.value) {
    userForm.put(`/superadmin/users/${editingUserId.value}`, {
      onSuccess: () => {
        showUserModal.value = false;
        triggerNotification('Data user berhasil diperbarui!');
      }
    });
  } else {
    userForm.post('/superadmin/users', {
      onSuccess: () => {
        showUserModal.value = false;
        triggerNotification('User baru berhasil ditambahkan!');
      }
    });
  }
};

const deleteUser = (userId) => {
  if (confirm('Apakah Anda yakin ingin menghapus user ini? Semua data relasi kelompok akan terlepas.')) {
    router.delete(`/superadmin/users/${userId}`, {
      onSuccess: () => triggerNotification('User berhasil dihapus.')
    });
  }
};

// Groups CRUD
const openAddGroup = () => {
  isEditingGroup.value = false;
  editingGroupId.value = null;
  groupForm.reset();
  showGroupModal.value = true;
};

const openEditGroup = (group) => {
  isEditingGroup.value = true;
  editingGroupId.value = group.id;
  groupForm.name = group.name;
  groupForm.ustad_id = group.ustad_id;
  groupForm.leader_id = group.leader_id;
  showGroupModal.value = true;
};

const submitGroupForm = () => {
  if (isEditingGroup.value) {
    groupForm.put(`/superadmin/groups/${editingGroupId.value}`, {
      onSuccess: () => {
        showGroupModal.value = false;
        triggerNotification('Kelompok halaqah berhasil diperbarui!');
      }
    });
  } else {
    groupForm.post('/superadmin/groups', {
      onSuccess: () => {
        showGroupModal.value = false;
        triggerNotification('Kelompok halaqah baru berhasil dibuat!');
      }
    });
  }
};

const deleteGroup = (groupId) => {
  if (confirm('Apakah Anda yakin ingin menghapus kelompok ini? Anggota di dalamnya akan ter-unplot.')) {
    router.delete(`/superadmin/groups/${groupId}`, {
      onSuccess: () => triggerNotification('Kelompok halaqah berhasil dihapus.')
    });
  }
};

// Group Plotting
const openPlotting = (group) => {
  plottingGroup.value = group;
  plottingForm.user_ids = group.members.map(m => m.id);
  showPlottingModal.value = true;
};

const submitPlottingForm = () => {
  plottingForm.post(`/superadmin/groups/${plottingGroup.value.id}/members`, {
    onSuccess: () => {
      showPlottingModal.value = false;
      triggerNotification('Plotting anggota berhasil diperbarui!');
    }
  });
};

const detachMember = (groupId, userId) => {
  if (confirm('Hapus anggota ini dari kelompok?')) {
    router.delete(`/superadmin/groups/${groupId}/members/${userId}`, {
      onSuccess: () => triggerNotification('Anggota berhasil dikeluarkan dari kelompok.')
    });
  }
};

// Material CRUD
const openAddMaterial = () => {
  isEditingMaterial.value = false;
  editingMaterialId.value = null;
  materialForm.reset();
  delete materialForm._method;
  showMaterialModal.value = true;
};

const openEditMaterial = (mat) => {
  isEditingMaterial.value = true;
  editingMaterialId.value = mat.id;
  materialForm.title = mat.title;
  materialForm.content = mat.content || '';
  materialForm.ustad_id = mat.ustad_id;
  materialForm.file = null;
  showMaterialModal.value = true;
};

const submitMaterialForm = () => {
  if (isEditingMaterial.value) {
    const data = {
      _method: 'PUT',
      title: materialForm.title,
      content: materialForm.content,
      ustad_id: materialForm.ustad_id,
      file: materialForm.file,
    };
    router.post(`/superadmin/materials/${editingMaterialId.value}`, data, {
      forceFormData: true,
      onSuccess: () => {
        showMaterialModal.value = false;
        triggerNotification('Materi kajian berhasil diperbarui!');
      }
    });
  } else {
    materialForm.post('/superadmin/materials', {
      onSuccess: () => {
        showMaterialModal.value = false;
        triggerNotification('Materi kajian berhasil diunggah!');
      }
    });
  }
};

const deleteMaterial = (materialId) => {
  if (confirm('Hapus materi kajian ini?')) {
    router.delete(`/superadmin/materials/${materialId}`, {
      onSuccess: () => triggerNotification('Materi berhasil dihapus.')
    });
  }
};

// Activity CRUD
const openAddActivity = () => {
  isEditingActivity.value = false;
  editingActivityId.value = null;
  activityForm.reset();
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

const submitActivityForm = () => {
  if (isEditingActivity.value) {
    activityForm.put(`/superadmin/activities/${editingActivityId.value}`, {
      onSuccess: () => {
        showActivityModal.value = false;
        triggerNotification('Sesi kajian berhasil diperbarui!');
      }
    });
  } else {
    activityForm.post('/superadmin/activities', {
      onSuccess: () => {
        showActivityModal.value = false;
        triggerNotification('Sesi kajian baru berhasil dibuat!');
      }
    });
  }
};

const deleteActivity = (activityId) => {
  if (confirm('Hapus sesi kajian ini? Absensi yang menempel pada sesi ini juga akan hilang.')) {
    router.delete(`/superadmin/activities/${activityId}`, {
      onSuccess: () => triggerNotification('Sesi kajian berhasil dihapus.')
    });
  }
};

// Grades CRUD
const openAddGrade = () => {
  isEditingGrade.value = false;
  singleGradeKehadiran.value = 20;
  singleGradeKedisiplinan.value = 8;
  singleGradePartisipasi.value = 8;
  singleGradeSikap.value = 8;
  singleGradePemahaman.value = 16;
  singleGradeAlQuran.value = 20;
  singleGradeNotesText.value = '';
  gradeForm.reset();
  showGradeModal.value = true;
};

const submitGradeForm = () => {
  const k = singleGradeKehadiran.value || 0;
  const d = singleGradeKedisiplinan.value || 0;
  const p = singleGradePartisipasi.value || 0;
  const s = singleGradeSikap.value || 0;
  const m = singleGradePemahaman.value || 0;
  const q = singleGradeAlQuran.value || 0;
  gradeForm.score = k + d + p + s + m + q;
  gradeForm.notes = `Rincian: Hdr ${k}/25 | Dsl ${d}/10 | Pts ${p}/10 | Skp ${s}/10 | Phm ${m}/20 | Qur ${q}/25. Catatan: ${singleGradeNotesText.value}`;

  gradeForm.post('/superadmin/grades', {
    onSuccess: () => {
      showGradeModal.value = false;
      triggerNotification('Nilai bulanan berhasil disimpan!');
    }
  });
};

const deleteGrade = (gradeId) => {
  if (confirm('Hapus rekam nilai ini?')) {
    router.delete(`/superadmin/grades/${gradeId}`, {
      onSuccess: () => triggerNotification('Data nilai bulanan berhasil dihapus.')
    });
  }
};

// Attendance CRUD
const openAttendanceApproval = (activity) => {
  selectedActivity.value = activity;
  
  // Find members of this activity's group
  const group = props.groups.find(g => g.id === activity.group_id);
  const members = group ? group.members : [];

  // Map existing attendances or defaults
  attendanceForm.attendances = members.map(m => {
    const existing = props.attendances.find(at => at.user_id === m.id && at.activity_id === activity.id);
    return {
      user_id: m.id,
      name: m.name,
      status: existing ? existing.status : 'present'
    };
  });

  showAttendanceModal.value = true;
};

const submitAttendanceForm = () => {
  attendanceForm.post(`/superadmin/activities/${selectedActivity.value.id}/attendances/approve`, {
    onSuccess: () => {
      showAttendanceModal.value = false;
      triggerNotification('Absensi sesi kajian berhasil diperbarui!');
    }
  });
};

const deleteAttendance = (attendanceId) => {
  if (confirm('Hapus log presensi ini?')) {
    router.delete(`/superadmin/attendances/${attendanceId}`, {
      onSuccess: () => triggerNotification('Log presensi berhasil dihapus.')
    });
  }
};

const handleLogout = () => {
  router.post('/logout');
};

// Registration Approval Actions
const openApproveModal = (user) => {
  approvingUser.value = user;
  approveForm.role_id = '';
  showApproveModal.value = true;
};

const submitApprove = () => {
  approveForm.post(`/superadmin/users/${approvingUser.value.id}/approve`, {
    onSuccess: () => {
      showApproveModal.value = false;
      triggerNotification(`${approvingUser.value.name} berhasil disetujui!`);
    }
  });
};

const rejectUser = (user) => {
  if (confirm(`Apakah Anda yakin ingin menolak pendaftaran ${user.name}?`)) {
    router.post(`/superadmin/users/${user.id}/reject`, {}, {
      onSuccess: () => triggerNotification(`Pendaftaran ${user.name} telah ditolak.`)
    });
  }
};
</script>

<template>
  <Head title="Superadmin Control Center - Unit Pembinaan Anggota" />

  <div class="relative min-h-screen bg-[#FAFAF9] text-gray-800 font-sans pb-12">
    <!-- Islamic Watermark -->
    <div 
      class="absolute inset-0 opacity-[0.04] pointer-events-none"
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
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
              </svg>
            </div>
            <div>
              <span class="text-base sm:text-lg font-bold text-emerald-800 tracking-wide block">{{ $page.props.settings?.app_title || 'Control Center' }}</span>
              <span class="hidden sm:block text-xs text-emerald-600 font-medium">Superadmin System Panel</span>
            </div>
          </div>
          <div class="flex items-center space-x-2 sm:space-x-4">
            <div class="bg-red-50 px-2 sm:px-3 py-1.5 rounded-full border border-red-100 text-xs font-semibold text-red-800 max-w-[120px] sm:max-w-none overflow-hidden">
              <span class="truncate block">{{ (auth?.name || auth?.user?.name || 'Superadmin').split(' ').slice(0,2).join(' ') }}</span>
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
      
      <!-- Welcome Header -->
      <div class="bg-gradient-to-r from-emerald-800 to-emerald-950 text-white rounded-2xl p-4 sm:p-6 shadow-xl mb-5 sm:mb-8 border border-emerald-850 relative overflow-hidden">
        <div class="absolute -right-16 -top-16 w-48 h-48 rounded-full bg-amber-400 opacity-10 blur-xl"></div>
        <h1 class="text-xl sm:text-2xl font-bold tracking-tight">Superadmin System Administration</h1>
        <p class="text-emerald-100 mt-1.5 text-xs sm:text-sm max-w-xl hidden sm:block">
          Pusat kendali utama Server UPA. CRUD penuh untuk memonitor, membuat, memperbarui, dan menghapus seluruh entitas: User, Halaqah, Materi, Sesi Kajian, Absensi, dan Nilai Binaan.
        </p>
      </div>

      <!-- Stats Cards Row -->
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-xl p-5 shadow-sm border border-emerald-50">
          <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider block">Status Sistem</span>
          <span class="text-xl sm:text-2xl font-bold text-emerald-800 block mt-2">{{ systemStats.systemStatus }}</span>
        </div>
        <div class="bg-white rounded-xl p-5 shadow-sm border border-emerald-50">
          <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider block">Total Pengguna</span>
          <span class="text-xl sm:text-2xl font-bold text-gray-900 block mt-2">{{ systemStats.totalUsers }} user</span>
        </div>
        <div class="bg-white rounded-xl p-5 shadow-sm border border-emerald-50">
          <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider block">Total Kelompok</span>
          <span class="text-xl sm:text-2xl font-bold text-gray-900 block mt-2">{{ systemStats.totalGroups }} halaqah</span>
        </div>
        <div class="bg-white rounded-xl p-5 shadow-sm border border-emerald-50">
          <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider block">Total Materi</span>
          <span class="text-xl sm:text-2xl font-bold text-emerald-700 block mt-2">{{ systemStats.totalMaterials }} berkas</span>
        </div>
      </div>

      <!-- Tab Switcher -->
      <div class="flex flex-wrap border-b border-gray-200 mb-6 bg-white p-2 rounded-xl shadow-sm border border-emerald-50 gap-2">
        <button 
          v-for="tab in [
            { key: 'dashboard', label: 'Dashboard & Config' },
            { key: 'users', label: 'User CRUD' },
            { key: 'groups', label: 'Halaqah CRUD' },
            { key: 'materials', label: 'Materi CRUD' },
            { key: 'activities', label: 'Kajian CRUD' },
            { key: 'attendances', label: 'Absensi CRUD' },
            { key: 'grades', label: 'Penilaian CRUD' },
            { key: 'pending', label: `Pendaftaran Pending (${(pendingUsers || []).length})` }
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
        
        <!-- 1. DASHBOARD & GLOBAL CONFIG -->
        <div v-if="activeTab === 'dashboard'" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
          <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-xl shadow-sm border border-emerald-100 p-6">
              <h2 class="text-base font-bold text-emerald-950 mb-4 pb-2 border-b border-emerald-50 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-emerald-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                Matriks Peran (Role Matrix Configuration)
              </h2>
              <div class="space-y-4">
                <div v-for="role in roles" :key="role.id" class="p-4 border border-gray-100 rounded-xl bg-white hover:border-emerald-100 transition-all shadow-sm">
                  <div class="flex justify-between items-center">
                    <span class="bg-emerald-50 text-emerald-800 px-3 py-1 rounded-md text-xs font-bold border border-emerald-100">{{ role.name }}</span>
                    <span class="text-xs text-gray-400 font-mono">slug: {{ role.slug }}</span>
                  </div>
                  <p class="text-xs text-gray-500 mt-2.5 leading-relaxed">{{ role.desc }}</p>
                </div>
              </div>
            </div>
          </div>

          <div class="lg:col-span-1 space-y-6">
            <div class="bg-white rounded-xl shadow-sm border border-emerald-100 p-6">
              <h2 class="text-base font-bold text-emerald-950 mb-4 pb-2 border-b border-emerald-50 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-emerald-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                Konfigurasi Sistem
              </h2>

              <form @submit.prevent="saveConfig" class="space-y-4">
                <div class="flex items-center justify-between p-3.5 bg-gray-50/50 border border-gray-150 rounded-xl">
                  <div>
                    <span class="text-xs font-semibold text-gray-900 block">Absen Mandiri Anggota</span>
                    <span class="text-[10px] text-gray-400 block mt-0.5">Izinkan anggota check-in via HP</span>
                  </div>
                  <button 
                    type="button"
                    @click="configForm.enable_self_checkin = !configForm.enable_self_checkin"
                    class="relative inline-flex h-5 w-10 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out outline-none"
                    :class="configForm.enable_self_checkin ? 'bg-emerald-700' : 'bg-gray-300'"
                  >
                    <span class="pointer-events-none inline-block h-4.5 w-4.5 transform rounded-full bg-white shadow transition duration-200"
                      :class="configForm.enable_self_checkin ? 'translate-x-5' : 'translate-x-0'"></span>
                  </button>
                </div>

                <div class="space-y-1">
                  <label for="max_days" class="text-xs font-bold text-gray-500 uppercase block">Batas Maksimal Delegasi (Hari)</label>
                  <input 
                    type="number" 
                    id="max_days" 
                    v-model="configForm.max_delegation_days" 
                    class="w-full border border-gray-200 rounded-lg p-2 text-sm focus:ring-1 focus:ring-emerald-500 outline-none"
                  />
                </div>

                <div class="flex items-center justify-between p-3.5 bg-red-50/40 border border-red-100 rounded-xl">
                  <div>
                    <span class="text-xs font-semibold text-red-950 block">Mode Perawatan</span>
                    <span class="text-[10px] text-red-400 block mt-0.5">Kunci portal untuk pemeliharaan</span>
                  </div>
                  <button 
                    type="button"
                    @click="configForm.maintenance_mode = !configForm.maintenance_mode"
                    class="relative inline-flex h-5 w-10 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out outline-none"
                    :class="configForm.maintenance_mode ? 'bg-red-600' : 'bg-gray-300'"
                  >
                    <span class="pointer-events-none inline-block h-4.5 w-4.5 transform rounded-full bg-white shadow transition duration-200"
                      :class="configForm.maintenance_mode ? 'translate-x-5' : 'translate-x-0'"></span>
                  </button>
                </div>

                <button 
                  type="submit" 
                  class="w-full bg-emerald-700 hover:bg-emerald-800 text-white font-bold text-xs py-2.5 px-4 rounded-lg shadow-md transition-colors"
                >
                  Terapkan Perubahan
                </button>
              </form>
            </div>

            <!-- Branding & Identitas Web -->
            <div class="bg-white rounded-xl shadow-sm border border-emerald-100 p-6">
              <h2 class="text-base font-bold text-emerald-950 mb-4 pb-2 border-b border-emerald-50 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-emerald-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                Identitas & Branding Web
              </h2>

              <form @submit.prevent="submitSettingsForm" class="space-y-4">
                <!-- App Title -->
                <div class="space-y-1">
                  <label for="app_title" class="text-xs font-bold text-gray-500 uppercase block">Judul Aplikasi / Web</label>
                  <input 
                    type="text" 
                    id="app_title" 
                    v-model="settingsForm.app_title" 
                    required
                    class="w-full border border-gray-200 rounded-lg p-2.5 text-sm focus:ring-1 focus:ring-emerald-500 outline-none"
                  />
                </div>

                <!-- Logo Upload -->
                <div class="space-y-1">
                  <label for="branding_logo" class="text-xs font-bold text-gray-500 uppercase block">Ikon / Logo Baru</label>
                  <input 
                    type="file" 
                    id="branding_logo" 
                    @input="settingsForm.logo = $event.target.files[0]" 
                    class="w-full border border-gray-200 rounded-lg p-1.5 text-xs focus:ring-1 focus:ring-emerald-500 outline-none"
                  />
                  <span class="text-[10px] text-gray-400 block">Rekomendasi format PNG/SVG transparan.</span>
                </div>

                <!-- Favicon Upload -->
                <div class="space-y-1">
                  <label for="branding_favicon" class="text-xs font-bold text-gray-500 uppercase block">Favicon (.ico / .png)</label>
                  <input 
                    type="file" 
                    id="branding_favicon" 
                    @input="settingsForm.favicon = $event.target.files[0]" 
                    class="w-full border border-gray-200 rounded-lg p-1.5 text-xs focus:ring-1 focus:ring-emerald-500 outline-none"
                  />
                  <span class="text-[10px] text-gray-400 block">Ikon kecil tab browser.</span>
                </div>

                <!-- Submit Button -->
                <button 
                  type="submit"
                  class="w-full bg-emerald-700 hover:bg-emerald-800 text-white font-bold py-2.5 rounded-lg shadow-md transition-colors text-xs"
                >
                  Simpan Branding & Identitas
                </button>
              </form>
            </div>
          </div>
        </div>

        <!-- 2. USER CRUD -->
        <div v-if="activeTab === 'users'" class="bg-white rounded-xl shadow-sm border border-emerald-100 p-6">
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <div>
              <h2 class="text-lg font-bold text-emerald-950">Manajemen Pengguna (User CRUD)</h2>
              <p class="text-xs text-gray-500 font-medium">Buat, perbarui, dan hapus user beserta peran/akses sistem.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
              <button 
                @click="openImportModal"
                class="bg-white hover:bg-gray-50 text-emerald-700 border border-emerald-200 font-bold text-xs py-2.5 px-4 rounded-lg shadow-sm transition-colors flex items-center justify-center gap-1.5"
              >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                </svg>
                <span>Impor Pengguna (CSV)</span>
              </button>
              <button 
                @click="openAddUser"
                class="bg-emerald-700 hover:bg-emerald-800 text-white font-bold text-xs py-2.5 px-4 rounded-lg shadow-md transition-all flex items-center justify-center gap-1"
              >
                <span>+ Tambah User Baru</span>
              </button>
            </div>
          </div>

          <div class="mb-4">
            <input 
              type="text" 
              v-model="searchUserQuery" 
              placeholder="Cari user berdasarkan nama, email, atau role..."
              class="w-full sm:max-w-md border border-gray-250 rounded-lg p-2.5 text-xs focus:ring-1 focus:ring-emerald-500 outline-none shadow-sm"
            />
          </div>

          <!-- Mobile view: cards list -->
          <div class="block sm:hidden space-y-3">
            <div v-for="user in filteredUsers" :key="user.id" class="p-4 rounded-xl border border-gray-150 bg-[#FAFAF9]/40 space-y-3">
              <div class="flex justify-between items-start gap-2">
                <span class="font-bold text-gray-900 text-sm block leading-snug">{{ user.name }}</span>
                <span 
                  class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider border flex-shrink-0"
                  :class="{
                    'bg-red-50 border-red-200 text-red-700': user.role_slug === 'superadmin',
                    'bg-indigo-50 border-indigo-200 text-indigo-700': user.role_slug === 'admin',
                    'bg-emerald-50 border-emerald-200 text-emerald-700': user.role_slug === 'ustad',
                    'bg-amber-50 border-amber-200 text-amber-700': user.role_slug === 'leader',
                    'bg-gray-100 border-gray-300 text-gray-700': user.role_slug === 'member'
                  }"
                >
                  {{ user.role_name }}
                </span>
              </div>
              <div class="text-xs space-y-1">
                <div>Email: <span class="text-gray-655 font-medium">{{ user.email }}</span></div>
                <div>Halaqah: <span class="text-gray-800 font-semibold">{{ user.group_name || '—' }}</span></div>
                <div v-if="user.ustad_name && user.ustad_name !== '—'">Pembina: <span class="text-emerald-700 font-semibold">{{ user.ustad_name }}</span></div>
                <div v-if="user.leader_name && user.leader_name !== '—'">Ketua: <span class="text-amber-700 font-semibold">{{ user.leader_name }}</span></div>
              </div>
              <div class="pt-2 border-t border-dashed border-gray-200 flex justify-end items-center gap-2">
                <button @click="openEditUser(user)" class="text-[10px] text-emerald-700 hover:text-emerald-950 font-bold border border-emerald-200 bg-emerald-50 py-1 px-2.5 rounded">Edit</button>
                <button @click="deleteUser(user.id)" class="text-[10px] text-red-600 hover:text-red-900 font-bold border border-red-200 bg-red-50 py-1 px-2.5 rounded">Hapus</button>
              </div>
            </div>
          </div>

          <!-- Desktop view: table -->
          <div class="hidden sm:block overflow-x-auto rounded-lg border border-gray-150">
            <table class="w-full text-left border-collapse text-xs">
              <thead>
                <tr class="bg-gray-50 font-bold text-gray-500 uppercase tracking-wider border-b border-gray-150">
                  <th class="py-3 px-6">Nama</th>
                  <th class="py-3 px-6">Email</th>
                  <th class="py-3 px-6">Peran (Role)</th>
                  <th class="py-3 px-6">Halaqah Group</th>
                  <th class="py-3 px-6 text-center">Aksi</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100">
                <tr v-for="user in filteredUsers" :key="user.id" class="hover:bg-gray-50/40">
                  <td class="py-3 px-6 font-bold text-gray-900">{{ user.name }}</td>
                  <td class="py-3 px-6 text-gray-600">{{ user.email }}</td>
                  <td class="py-3 px-6">
                    <span 
                      class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider border"
                      :class="{
                        'bg-red-50 border-red-200 text-red-700': user.role_slug === 'superadmin',
                        'bg-indigo-50 border-indigo-200 text-indigo-700': user.role_slug === 'admin',
                        'bg-emerald-50 border-emerald-200 text-emerald-700': user.role_slug === 'ustad',
                        'bg-amber-50 border-amber-200 text-amber-700': user.role_slug === 'leader',
                        'bg-gray-100 border-gray-300 text-gray-700': user.role_slug === 'member'
                      }"
                    >
                      {{ user.role_name }}
                    </span>
                  </td>
                  <td class="py-3 px-6 text-gray-500 font-medium">
                    <div class="font-semibold text-gray-800">{{ user.group_name }}</div>
                    <div v-if="user.ustad_name && user.ustad_name !== '—'" class="text-[10px] text-emerald-700 mt-0.5 font-medium">
                      Pembina: {{ user.ustad_name }}
                    </div>
                    <div v-if="user.leader_name && user.leader_name !== '—'" class="text-[10px] text-amber-700 font-medium">
                      Ketua: {{ user.leader_name }}
                    </div>
                  </td>
                  <td class="py-3 px-6 text-center space-x-2">
                    <button @click="openEditUser(user)" class="text-[10px] text-emerald-700 hover:text-emerald-950 font-bold">Edit</button>
                    <button @click="deleteUser(user.id)" class="text-[10px] text-red-600 hover:text-red-900 font-bold">Hapus</button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- 3. HALAQAH CRUD -->
        <div v-if="activeTab === 'groups'" class="bg-white rounded-xl shadow-sm border border-emerald-100 p-6">
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <div>
              <h2 class="text-lg font-bold text-emerald-950">Manajemen Halaqah (Group CRUD)</h2>
              <p class="text-xs text-gray-500 font-medium">Buat kelompok halaqah, tugaskan Ustad, tentukan Ketua Kelompok, dan plot mutarabbi.</p>
            </div>
            <button 
              @click="openAddGroup"
              class="bg-emerald-700 hover:bg-emerald-800 text-white font-bold text-xs py-2.5 px-4 rounded-lg shadow-md transition-all flex items-center justify-center gap-1"
            >
              <span>+ Buat Kelompok Baru</span>
            </button>
          </div>

          <div class="mb-4">
            <input 
              type="text" 
              v-model="searchGroupQuery" 
              placeholder="Cari kelompok halaqah..."
              class="w-full sm:max-w-md border border-gray-250 rounded-lg p-2.5 text-xs focus:ring-1 focus:ring-emerald-500 outline-none shadow-sm"
            />
          </div>

          <!-- Mobile view: cards list -->
          <div class="block sm:hidden space-y-3">
            <div v-for="group in filteredGroups" :key="group.id" class="p-4 rounded-xl border border-gray-150 bg-[#FAFAF9]/40 space-y-3">
              <div class="flex justify-between items-start gap-2">
                <span class="font-bold text-gray-900 text-sm block leading-snug">{{ group.name }}</span>
                <span class="bg-blue-50 text-blue-800 border border-blue-100 px-2 py-0.5 rounded text-[10px] font-bold flex-shrink-0">
                  {{ group.members_count }} Mutarabbi
                </span>
              </div>
              <div class="text-xs space-y-1">
                <div>Ustad: <strong class="text-emerald-700">{{ group.ustad }}</strong></div>
                <div>Ketua: <strong class="text-amber-700">{{ group.leader }}</strong></div>
              </div>
              <div class="pt-2 border-t border-dashed border-gray-200 flex justify-end items-center gap-2">
                <button @click="openPlotting(group)" class="text-[10px] text-blue-600 hover:text-blue-800 font-bold border border-blue-200 bg-blue-50 py-1 px-2.5 rounded">Plot Anggota</button>
                <button @click="openEditGroup(group)" class="text-[10px] text-emerald-700 hover:text-emerald-950 font-bold border border-emerald-200 bg-emerald-50 py-1 px-2.5 rounded">Edit</button>
                <button @click="deleteGroup(group.id)" class="text-[10px] text-red-600 hover:text-red-900 font-bold border border-red-200 bg-red-50 py-1 px-2.5 rounded">Hapus</button>
              </div>
            </div>
          </div>

          <!-- Desktop view: table -->
          <div class="hidden sm:block overflow-x-auto rounded-lg border border-gray-150">
            <table class="w-full text-left border-collapse text-xs">
              <thead>
                <tr class="bg-gray-50 font-bold text-gray-500 uppercase tracking-wider border-b border-gray-150">
                  <th class="py-3 px-6">Nama Kelompok</th>
                  <th class="py-3 px-6">Ustad / Pembina</th>
                  <th class="py-3 px-6">Ketua Kelompok (Leader)</th>
                  <th class="py-3 px-6 text-center">Anggota Aktif</th>
                  <th class="py-3 px-6 text-center font-bold">Aksi Kelompok</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100">
                <tr v-for="group in filteredGroups" :key="group.id" class="hover:bg-gray-50/40">
                  <td class="py-4 px-6 font-bold text-gray-900">{{ group.name }}</td>
                  <td class="py-4 px-6">
                    <span class="bg-emerald-50 text-emerald-800 border border-emerald-100 px-2.5 py-1 rounded text-[11px] font-semibold">
                      {{ group.ustad }}
                    </span>
                  </td>
                  <td class="py-4 px-6">
                    <span class="bg-amber-50 text-amber-800 border border-amber-100 px-2.5 py-1 rounded text-[11px] font-semibold">
                      {{ group.leader }}
                    </span>
                  </td>
                  <td class="py-4 px-6 text-center font-bold text-gray-600">
                    {{ group.members_count }} Mutarabbi
                  </td>
                  <td class="py-4 px-6 text-center space-x-3">
                    <button @click="openPlotting(group)" class="text-[10px] text-blue-600 hover:text-blue-800 font-bold">Plot Anggota</button>
                    <button @click="openEditGroup(group)" class="text-[10px] text-emerald-700 hover:text-emerald-950 font-bold">Edit</button>
                    <button @click="deleteGroup(group.id)" class="text-[10px] text-red-600 hover:text-red-900 font-bold">Hapus</button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- 4. MATERIALS CRUD -->
        <div v-if="activeTab === 'materials'" class="bg-white rounded-xl shadow-sm border border-emerald-100 p-6">
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <div>
              <h2 class="text-lg font-bold text-emerald-950">Manajemen Berkas Kajian (Materials CRUD)</h2>
              <p class="text-xs text-gray-500 font-medium">Unggah materi pembinaan atau hapus berkas kajian sistem.</p>
            </div>
            <button 
              @click="openAddMaterial"
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
            <div v-for="mat in filteredMaterials" :key="mat.id" class="p-4 rounded-xl border border-gray-150 bg-[#FAFAF9]/40 space-y-3">
              <div>
                <h3 class="font-bold text-gray-900 text-sm leading-snug">{{ mat.title }}</h3>
                <p class="text-xs text-gray-500 mt-1 line-clamp-2">{{ mat.content || '—' }}</p>
              </div>
              <div class="flex justify-between items-center text-[10px] text-gray-400">
                <span>Penerbit: <strong class="text-emerald-700">{{ mat.ustad_name }}</strong></span>
                <span>{{ mat.published_at }}</span>
              </div>
              <div class="pt-2 border-t border-dashed border-gray-200 flex justify-between items-center gap-2">
                <a 
                  v-if="mat.file_path" 
                  :href="`/materials/${mat.id}/download`" 
                  class="bg-emerald-50 border border-emerald-200 text-emerald-800 text-[10px] font-bold py-1 px-2.5 rounded hover:bg-emerald-100"
                >
                  Unduh Berkas
                </a>
                <span v-else class="text-[10px] text-gray-400 font-semibold">Teks Saja</span>

                <div class="flex gap-1">
                  <button 
                    @click="openEditMaterial(mat)" 
                    class="text-[10px] text-emerald-700 hover:text-emerald-950 font-bold border border-emerald-200 bg-emerald-50 py-1 px-2.5 rounded"
                  >
                    Edit
                  </button>
                  <button 
                    @click="deleteMaterial(mat.id)" 
                    class="text-[10px] text-red-600 hover:text-red-900 font-bold border border-red-200 bg-red-50 py-1 px-2.5 rounded"
                  >
                    Hapus
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Desktop view: table -->
          <div class="hidden sm:block overflow-x-auto rounded-lg border border-gray-150">
            <table class="w-full text-left border-collapse text-xs">
              <thead>
                <tr class="bg-gray-50 font-bold text-gray-500 uppercase tracking-wider border-b border-gray-150">
                  <th class="py-3 px-6">Judul Materi</th>
                  <th class="py-3 px-6">Deskripsi/Konten</th>
                  <th class="py-3 px-6">Dipublikasikan Oleh</th>
                  <th class="py-3 px-6">Tanggal Rilis</th>
                  <th class="py-3 px-6 text-center">Berkas</th>
                  <th class="py-3 px-6 text-center font-bold">Aksi</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100">
                <tr v-for="mat in filteredMaterials" :key="mat.id" class="hover:bg-gray-50/40">
                  <td class="py-3 px-6 font-bold text-gray-900">{{ mat.title }}</td>
                  <td class="py-3 px-6 text-gray-500 line-clamp-2 max-w-xs mt-1">{{ mat.content || '—' }}</td>
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
                    <div class="flex items-center justify-center gap-2">
                      <button @click="openEditMaterial(mat)" class="text-[10px] text-emerald-700 hover:text-emerald-950 font-bold">Edit</button>
                      <button @click="deleteMaterial(mat.id)" class="text-[10px] text-red-600 hover:text-red-900 font-bold">Hapus</button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- 5. ACTIVITIES CRUD -->
        <div v-if="activeTab === 'activities'" class="bg-white rounded-xl shadow-sm border border-emerald-100 p-6">
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <div>
              <h2 class="text-lg font-bold text-emerald-950">Manajemen Sesi Kajian (Activities CRUD)</h2>
              <p class="text-xs text-gray-500 font-medium">Buat sesi kajian untuk kelompok halaqah tertentu, edit deskripsi, atau hapus sesi.</p>
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
            <div v-for="act in filteredActivities" :key="act.id" class="p-4 rounded-xl border border-gray-150 bg-[#FAFAF9]/40 space-y-3">
              <div class="flex justify-between items-start gap-2">
                <div>
                  <span class="font-bold text-gray-900 text-sm block leading-snug">{{ act.topic }}</span>
                  <span class="text-[10px] text-emerald-800 font-bold block mt-0.5">{{ act.group_name }}</span>
                </div>
                <span class="bg-gray-100 text-gray-650 border border-gray-200 px-2 py-0.5 rounded text-[10px] font-semibold flex-shrink-0">
                  {{ act.date_human }}
                </span>
              </div>
              <p class="text-xs text-gray-500 line-clamp-2">{{ act.description || '—' }}</p>
              <div class="pt-2 border-t border-dashed border-gray-200 flex justify-end items-center gap-2">
                <button @click="openAttendanceApproval(act)" class="text-[10px] text-blue-600 hover:text-blue-800 font-bold border border-blue-200 bg-blue-50 py-1 px-2.5 rounded">Input/Edit Absen</button>
                <button @click="openEditActivity(act)" class="text-[10px] text-emerald-700 hover:text-emerald-950 font-bold border border-emerald-200 bg-emerald-50 py-1 px-2.5 rounded">Edit</button>
                <button @click="deleteActivity(act.id)" class="text-[10px] text-red-600 hover:text-red-900 font-bold border border-red-200 bg-red-50 py-1 px-2.5 rounded">Hapus</button>
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
                    <button @click="deleteActivity(act.id)" class="text-[10px] text-red-600 hover:text-red-900 font-bold">Hapus</button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- 6. ATTENDANCES CRUD -->
        <div v-if="activeTab === 'attendances'" class="bg-white rounded-xl shadow-sm border border-emerald-100 p-6">
          <div class="mb-6 border-b border-gray-100 pb-3">
            <h2 class="text-lg font-bold text-emerald-950">Manajemen Presensi (Attendances CRUD)</h2>
            <p class="text-xs text-gray-500 font-medium">Log absensi riil anggota dari setiap pertemuan halaqah. Edit langsung status kehadiran.</p>
          </div>

          <div class="mb-4">
            <input 
              type="text" 
              v-model="searchAttendanceQuery" 
              placeholder="Cari absensi berdasarkan nama mutarabbi, topik, status..."
              class="w-full sm:max-w-md border border-gray-250 rounded-lg p-2.5 text-xs focus:ring-1 focus:ring-emerald-500 outline-none shadow-sm"
            />
          </div>

          <!-- Mobile view: cards list -->
          <div class="block sm:hidden space-y-3">
            <div v-for="att in filteredAttendances" :key="att.id" class="p-4 rounded-xl border border-gray-150 bg-[#FAFAF9]/40 space-y-3">
              <div class="flex justify-between items-start gap-2">
                <div>
                  <span class="font-bold text-gray-900 text-sm block leading-snug">{{ att.user_name }}</span>
                  <span class="text-xs text-gray-500 block mt-0.5">{{ att.activity_topic }}</span>
                </div>
                <span 
                  class="px-2 py-0.5 rounded text-[10px] font-bold border flex-shrink-0"
                  :class="{
                    'bg-emerald-50 border-emerald-200 text-emerald-700': att.status === 'present',
                    'bg-teal-50 border-teal-200 text-teal-700': att.status === 'late',
                    'bg-amber-50 border-amber-200 text-amber-700': att.status === 'sick',
                    'bg-blue-50 border-blue-200 text-blue-700': att.status === 'permission',
                    'bg-red-50 border-red-200 text-red-700': att.status === 'absent'
                  }"
                >
                  {{ att.status === 'present' ? 'Hadir' : (att.status === 'late' ? 'Terlambat' : (att.status === 'sick' ? 'Sakit' : (att.status === 'permission' ? 'Izin' : 'Alpa'))) }}
                </span>
              </div>
              <div class="text-[10px] text-gray-400 space-y-0.5">
                <div>Diverifikasi Oleh: <strong class="text-emerald-700">{{ att.approved_by }}</strong></div>
                <div>Waktu Validasi: <span>{{ att.approved_at }}</span></div>
              </div>
              <div class="pt-2 border-t border-dashed border-gray-200 flex justify-end items-center">
                <button @click="deleteAttendance(att.id)" class="text-[10px] text-red-600 hover:text-red-900 font-bold border border-red-200 bg-red-50 py-1 px-2.5 rounded">Hapus</button>
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
                        'bg-teal-50 border-teal-200 text-teal-700': att.status === 'late',
                        'bg-amber-50 border-amber-200 text-amber-700': att.status === 'sick',
                        'bg-blue-50 border-blue-200 text-blue-700': att.status === 'permission',
                        'bg-red-55 border-red-200 text-red-700': att.status === 'absent'
                      }"
                    >
                      {{ att.status === 'present' ? 'Hadir' : (att.status === 'late' ? 'Terlambat' : (att.status === 'sick' ? 'Sakit' : (att.status === 'permission' ? 'Izin' : 'Alpa'))) }}
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

        <!-- 7. GRADES CRUD -->
        <div v-if="activeTab === 'grades'" class="bg-white rounded-xl shadow-sm border border-emerald-100 p-6">
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <div>
              <h2 class="text-lg font-bold text-emerald-950">Manajemen Nilai Bulanan (Grades CRUD)</h2>
              <p class="text-xs text-gray-500 font-medium">Superadmin dapat menimpa/memasukkan nilai pembinaan mutarabbi secara manual.</p>
            </div>
            <button 
              @click="openAddGrade"
              class="bg-emerald-700 hover:bg-emerald-800 text-white font-bold text-xs py-2.5 px-4 rounded-lg shadow-md transition-all flex items-center justify-center gap-1"
            >
              <span>+ Berikan Nilai Baru</span>
            </button>
          </div>

          <div class="mb-4">
            <input 
              type="text" 
              v-model="searchGradeQuery" 
              placeholder="Cari nilai berdasarkan nama anggota atau ustad..."
              class="w-full sm:max-w-md border border-gray-250 rounded-lg p-2.5 text-xs focus:ring-1 focus:ring-emerald-500 outline-none shadow-sm"
            />
          </div>

          <!-- Mobile view: cards list -->
          <div class="block sm:hidden space-y-3">
            <div v-for="gr in filteredGrades" :key="gr.id" class="p-4 rounded-xl border border-gray-150 bg-[#FAFAF9]/40 space-y-3">
              <div class="flex justify-between items-start gap-2">
                <div>
                  <span class="font-bold text-gray-900 text-sm block leading-snug">{{ gr.user_name }}</span>
                  <span class="text-[10px] text-gray-500 block mt-0.5">Periode: Bulan {{ gr.month }}, {{ gr.year }}</span>
                </div>
                <div class="flex flex-col items-end flex-shrink-0">
                  <span class="text-lg font-extrabold text-emerald-800 leading-none">{{ gr.score }}</span>
                  <span class="text-[9px] text-gray-400 mt-0.5">Skor</span>
                </div>
              </div>
              <div class="text-xs">
                <div class="text-gray-400">Penilai: <strong class="text-emerald-700">{{ gr.ustad_name }}</strong></div>
                <div class="mt-1 bg-gray-50 p-2 rounded-lg border border-gray-100 text-gray-650 italic">
                  &ldquo;{{ gr.notes || 'Tidak ada catatan' }}&rdquo;
                </div>
              </div>
              <div class="pt-2 border-t border-dashed border-gray-200 flex justify-end items-center">
                <button @click="deleteGrade(gr.id)" class="text-[10px] text-red-600 hover:text-red-900 font-bold border border-red-200 bg-red-50 py-1 px-2.5 rounded">Hapus</button>
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
                  <td class="py-3 px-6 text-gray-600 font-semibold">Bulan {{ gr.month }}, {{ gr.year }}</td>
                  <td class="py-3 px-6 text-center font-bold text-emerald-700 text-sm">{{ gr.score }}</td>
                  <td class="py-3 px-6 text-gray-500 max-w-xs truncate">{{ gr.notes || '—' }}</td>
                  <td class="py-3 px-6 text-center">
                    <button @click="deleteGrade(gr.id)" class="text-[10px] text-red-600 hover:text-red-900 font-bold">Hapus</button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- 8. PENDING REGISTRATIONS -->
        <div v-if="activeTab === 'pending'" class="bg-white rounded-xl shadow-sm border border-emerald-100 p-6">
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <div>
              <h2 class="text-lg font-bold text-emerald-950">Pendaftaran Pending</h2>
              <p class="text-xs text-gray-500 font-medium">Daftar pengguna yang mendaftar sendiri dan menunggu persetujuan admin.</p>
            </div>
            <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200">
              {{ (pendingUsers || []).length }} menunggu
            </span>
          </div>

          <div v-if="!pendingUsers || pendingUsers.length === 0" class="text-center py-12">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-emerald-50 mb-4">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <p class="text-sm text-gray-500 font-medium">Tidak ada pendaftaran pending.</p>
          </div>

          <!-- Mobile view: cards -->
          <div v-else class="block sm:hidden space-y-3">
            <div v-for="pu in pendingUsers" :key="pu.id" class="p-4 rounded-xl border border-amber-100 bg-amber-50/50 space-y-3">
              <div class="flex justify-between items-start">
                <div>
                  <span class="font-bold text-gray-900 text-sm block">{{ pu.name }}</span>
                  <span class="text-[10px] text-gray-500 block mt-0.5">{{ pu.email }}</span>
                </div>
                <span class="text-[9px] font-bold bg-amber-100 text-amber-700 px-2 py-0.5 rounded-full border border-amber-200">PENDING</span>
              </div>
              <div class="grid grid-cols-2 gap-2 text-xs">
                <div><span class="text-gray-400">WhatsApp:</span> <strong class="text-gray-700">{{ pu.phone }}</strong></div>
                <div><span class="text-gray-400">Gender:</span> <strong class="text-gray-700">{{ pu.gender === 'ikhwan' ? 'Ikhwan' : 'Akhwat' }}</strong></div>
                <div class="col-span-2"><span class="text-gray-400">Departemen:</span> <strong class="text-gray-700">{{ pu.department }}</strong></div>
                <div class="col-span-2"><span class="text-gray-400">Daftar:</span> <strong class="text-gray-700">{{ pu.created_at }}</strong></div>
              </div>
              <div class="pt-2 border-t border-dashed border-amber-200 flex gap-2">
                <button @click="openApproveModal(pu)" class="flex-1 text-[10px] font-bold text-white bg-emerald-700 hover:bg-emerald-800 py-1.5 px-3 rounded-lg">Approve</button>
                <button @click="rejectUser(pu)" class="flex-1 text-[10px] font-bold text-red-700 bg-red-50 hover:bg-red-100 border border-red-200 py-1.5 px-3 rounded-lg">Reject</button>
              </div>
            </div>
          </div>

          <!-- Desktop view: table -->
          <div v-if="pendingUsers && pendingUsers.length > 0" class="hidden sm:block overflow-x-auto rounded-lg border border-amber-100">
            <table class="w-full text-left border-collapse text-xs">
              <thead>
                <tr class="bg-amber-50 font-bold text-gray-500 uppercase tracking-wider border-b border-amber-100">
                  <th class="py-3 px-6">Nama</th>
                  <th class="py-3 px-6">Email</th>
                  <th class="py-3 px-6">WhatsApp</th>
                  <th class="py-3 px-6">Gender</th>
                  <th class="py-3 px-6">Departemen</th>
                  <th class="py-3 px-6">Tanggal Daftar</th>
                  <th class="py-3 px-6 text-center">Status</th>
                  <th class="py-3 px-6 text-center font-bold">Aksi</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100">
                <tr v-for="pu in pendingUsers" :key="pu.id" class="hover:bg-amber-50/40">
                  <td class="py-3 px-6 font-bold text-gray-900">{{ pu.name }}</td>
                  <td class="py-3 px-6 text-gray-600">{{ pu.email }}</td>
                  <td class="py-3 px-6 text-gray-600">{{ pu.phone }}</td>
                  <td class="py-3 px-6 text-gray-600">{{ pu.gender === 'ikhwan' ? 'Ikhwan' : 'Akhwat' }}</td>
                  <td class="py-3 px-6 text-gray-600">{{ pu.department }}</td>
                  <td class="py-3 px-6 text-gray-500">{{ pu.created_at }}</td>
                  <td class="py-3 px-6 text-center">
                    <span class="text-[9px] font-bold bg-amber-100 text-amber-700 px-2 py-0.5 rounded-full border border-amber-200">PENDING</span>
                  </td>
                  <td class="py-3 px-6 text-center">
                    <div class="flex items-center justify-center gap-2">
                      <button @click="openApproveModal(pu)" class="text-[10px] font-bold text-white bg-emerald-700 hover:bg-emerald-800 py-1 px-3 rounded">Approve</button>
                      <button @click="rejectUser(pu)" class="text-[10px] font-bold text-red-700 bg-red-50 hover:bg-red-100 border border-red-200 py-1 px-3 rounded">Reject</button>
                    </div>
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
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
        </svg>
      </div>
      <span class="text-xs sm:text-sm font-semibold">{{ notificationMessage }}</span>
    </div>

    <!-- MODAL: USER FORM (ADD/EDIT) -->
    <div v-if="showUserModal" class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4" role="dialog" aria-modal="true">
      <div class="fixed inset-0 bg-emerald-950/40 backdrop-blur-sm transition-opacity" @click="showUserModal = false"></div>

      <div class="relative bg-white rounded-t-2xl sm:rounded-2xl w-full sm:max-w-md max-h-[90vh] overflow-y-auto shadow-2xl border border-emerald-50 animate-slide-up">
        <div class="bg-emerald-800 text-white px-5 py-4 flex items-center justify-between sticky top-0 z-10">
          <h3 class="text-base sm:text-lg font-bold">{{ isEditingUser ? 'Edit Data Pengguna' : 'Tambah Pengguna Baru' }}</h3>
          <button @click="showUserModal = false" class="text-emerald-200 hover:text-white transition-colors p-1">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <form @submit.prevent="submitUserForm" class="p-6 space-y-4">
          <div class="space-y-1">
            <label for="usr_name" class="text-xs font-bold text-gray-500 uppercase block">Nama Lengkap</label>
            <input type="text" id="usr_name" v-model="userForm.name" required class="w-full border border-gray-200 rounded-lg p-2.5 text-sm focus:ring-1 focus:ring-emerald-500 outline-none" />
          </div>

          <div class="space-y-1">
            <label for="usr_email" class="text-xs font-bold text-gray-500 uppercase block">Email Aktif</label>
            <input type="email" id="usr_email" v-model="userForm.email" required class="w-full border border-gray-200 rounded-lg p-2.5 text-sm focus:ring-1 focus:ring-emerald-500 outline-none" />
          </div>

          <div class="space-y-1">
            <label for="usr_password" class="text-xs font-bold text-gray-500 uppercase block">Kata Sandi (Password)</label>
            <input type="password" id="usr_password" v-model="userForm.password" :placeholder="isEditingUser ? 'Kosongkan jika tidak diubah' : 'Min. 6 Karakter'" :required="!isEditingUser" class="w-full border border-gray-200 rounded-lg p-2.5 text-sm focus:ring-1 focus:ring-emerald-500 outline-none" />
          </div>

          <div class="space-y-1">
            <label for="usr_role" class="text-xs font-bold text-gray-500 uppercase block">Peran (Role)</label>
            <select id="usr_role" v-model="userForm.role_id" required class="w-full border border-gray-200 rounded-lg p-2.5 text-sm focus:ring-1 focus:ring-emerald-500 outline-none">
              <option value="" disabled selected>Pilih hak akses role...</option>
              <option v-for="role in roles" :key="role.id" :value="role.id">{{ role.name }}</option>
            </select>
          </div>

          <div class="pt-4 border-t border-gray-100 flex gap-3">
            <button type="button" @click="showUserModal = false" class="flex-1 bg-white border border-gray-200 text-gray-700 px-4 py-2.5 rounded-lg text-sm font-medium hover:bg-gray-50 transition-colors">Batal</button>
            <button type="submit" class="flex-1 bg-emerald-700 hover:bg-emerald-800 text-white px-5 py-2.5 rounded-lg text-sm font-bold shadow-sm transition-colors">Simpan</button>
          </div>
        </form>
      </div>
    </div>

    <!-- MODAL: GROUP FORM (ADD/EDIT) -->
    <div v-if="showGroupModal" class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4" role="dialog" aria-modal="true">
      <div class="fixed inset-0 bg-emerald-950/40 backdrop-blur-sm transition-opacity" @click="showGroupModal = false"></div>

      <div class="relative bg-white rounded-t-2xl sm:rounded-2xl w-full sm:max-w-md max-h-[90vh] overflow-y-auto shadow-2xl border border-emerald-50 animate-slide-up">
        <div class="bg-emerald-800 text-white px-5 py-4 flex items-center justify-between sticky top-0 z-10">
          <h3 class="text-base sm:text-lg font-bold">{{ isEditingGroup ? 'Edit Kelompok Halaqah' : 'Tambah Kelompok Halaqah' }}</h3>
          <button @click="showGroupModal = false" class="text-emerald-200 hover:text-white transition-colors p-1">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <form @submit.prevent="submitGroupForm" class="p-6 space-y-4">
          <div class="space-y-1">
            <label for="grp_name" class="text-xs font-bold text-gray-500 uppercase block">Nama Kelompok</label>
            <input type="text" id="grp_name" v-model="groupForm.name" required placeholder="e.g. Halaqah Abu Ubaidah" class="w-full border border-gray-200 rounded-lg p-2.5 text-sm focus:ring-1 focus:ring-emerald-500 outline-none" />
          </div>

          <div class="space-y-1">
            <label for="grp_ustad" class="text-xs font-bold text-gray-500 uppercase block">Ustad Pembina</label>
            <select id="grp_ustad" v-model="groupForm.ustad_id" required class="w-full border border-gray-200 rounded-lg p-2.5 text-sm focus:ring-1 focus:ring-emerald-500 outline-none">
              <option value="" disabled selected>Pilih Ustad Pembina...</option>
              <option v-for="u in ustads" :key="u.id" :value="u.id">{{ u.name }}</option>
            </select>
          </div>

          <div class="space-y-1">
            <label for="grp_leader" class="text-xs font-bold text-gray-500 uppercase block">Ketua Kelompok (Leader)</label>
            <select id="grp_leader" v-model="groupForm.leader_id" required class="w-full border border-gray-200 rounded-lg p-2.5 text-sm focus:ring-1 focus:ring-emerald-500 outline-none">
              <option value="" disabled selected>Pilih Ketua Kelompok...</option>
              <option v-for="l in potentialLeaders" :key="l.id" :value="l.id">{{ l.name }}</option>
            </select>
          </div>

          <div class="pt-4 border-t border-gray-100 flex gap-3">
            <button type="button" @click="showGroupModal = false" class="flex-1 bg-white border border-gray-200 text-gray-700 px-4 py-2.5 rounded-lg text-sm font-medium hover:bg-gray-50 transition-colors">Batal</button>
            <button type="submit" class="flex-1 bg-emerald-700 hover:bg-emerald-800 text-white px-5 py-2.5 rounded-lg text-sm font-bold shadow-sm transition-colors">Simpan</button>
          </div>
        </form>
      </div>
    </div>

    <!-- MODAL: PLOTTING MEMBERS -->
    <div v-if="showPlottingModal" class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4" role="dialog" aria-modal="true">
      <div class="fixed inset-0 bg-emerald-950/40 backdrop-blur-sm transition-opacity" @click="showPlottingModal = false"></div>

      <div class="relative bg-white rounded-t-2xl sm:rounded-2xl w-full sm:max-w-md max-h-[90vh] overflow-y-auto shadow-2xl border border-emerald-50 animate-slide-up">
        <div class="bg-emerald-800 text-white px-6 py-4 flex items-center justify-between sticky top-0 z-10">
          <h3 class="text-base sm:text-lg font-bold truncate mr-2">Plotting Anggota: {{ plottingGroup?.name }}</h3>
          <button @click="showPlottingModal = false" class="text-emerald-200 hover:text-white transition-colors p-1">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <form @submit.prevent="submitPlottingForm" class="p-6 space-y-4">
          <p class="text-xs text-gray-500 font-medium">Pilih mutarabbi/anggota yang akan bergabung ke dalam kelompok ini:</p>
          
          <div class="max-h-60 overflow-y-auto space-y-2 border border-gray-150 p-3 rounded-lg">
            <div v-for="member in potentialMembers" :key="member.id" class="flex items-center space-x-2 py-1">
              <input 
                type="checkbox" 
                :id="'chk-' + member.id" 
                :value="member.id" 
                v-model="plottingForm.user_ids" 
                class="rounded border-gray-300 text-emerald-700 focus:ring-emerald-500" 
              />
              <label :for="'chk-' + member.id" class="text-xs font-semibold text-gray-700 cursor-pointer">{{ member.name }}</label>
            </div>
          </div>

          <div class="pt-4 border-t border-gray-100 flex gap-3">
            <button type="button" @click="showPlottingModal = false" class="flex-1 bg-white border border-gray-200 text-gray-700 px-4 py-2.5 rounded-lg text-sm font-medium hover:bg-gray-50 transition-colors">Batal</button>
            <button type="submit" class="flex-1 bg-emerald-700 hover:bg-emerald-800 text-white px-5 py-2.5 rounded-lg text-sm font-bold shadow-sm transition-colors">Simpan Plotting</button>
          </div>
        </form>
      </div>
    </div>

    <!-- MODAL: MATERIAL FORM -->
    <div v-if="showMaterialModal" class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4" role="dialog" aria-modal="true">
      <div class="fixed inset-0 bg-emerald-950/40 backdrop-blur-sm transition-opacity" @click="showMaterialModal = false"></div>

      <div class="relative bg-white rounded-t-2xl sm:rounded-2xl w-full sm:max-w-md max-h-[90vh] overflow-y-auto shadow-2xl border border-emerald-50 animate-slide-up">
        <div class="bg-emerald-800 text-white px-5 py-4 flex items-center justify-between sticky top-0 z-10">
          <h3 class="text-base sm:text-lg font-bold">{{ isEditingMaterial ? 'Edit Materi Kajian' : 'Unggah Materi Kajian Baru' }}</h3>
          <button @click="showMaterialModal = false" class="text-emerald-200 hover:text-white transition-colors p-1">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <form @submit.prevent="submitMaterialForm" class="p-6 space-y-4">
          <input v-if="isEditingMaterial" type="hidden" name="_method" value="PUT" />
          <div class="space-y-1">
            <label for="mat_title" class="text-xs font-bold text-gray-500 uppercase block">Judul Materi</label>
            <input type="text" id="mat_title" v-model="materialForm.title" required class="w-full border border-gray-200 rounded-lg p-2.5 text-sm focus:ring-1 focus:ring-emerald-500 outline-none" />
          </div>

          <div class="space-y-1">
            <label for="mat_content" class="text-xs font-bold text-gray-500 uppercase block">Ringkasan / Konten</label>
            <textarea id="mat_content" v-model="materialForm.content" rows="4" class="w-full border border-gray-200 rounded-lg p-2.5 text-sm focus:ring-1 focus:ring-emerald-500 outline-none"></textarea>
          </div>

          <div class="space-y-1">
            <label for="mat_ustad" class="text-xs font-bold text-gray-500 uppercase block">Penerbit (Ustad Pembina)</label>
            <select id="mat_ustad" v-model="materialForm.ustad_id" required class="w-full border border-gray-200 rounded-lg p-2.5 text-sm focus:ring-1 focus:ring-emerald-500 outline-none">
              <option value="" disabled selected>Pilih Ustad...</option>
              <option v-for="u in ustads" :key="u.id" :value="u.id">{{ u.name }}</option>
            </select>
          </div>

          <div class="space-y-1">
            <label for="mat_file" class="text-xs font-bold text-gray-500 uppercase block">Dokumen Lampiran (PDF, Word, PPT)</label>
            <input 
              type="file" 
              id="mat_file" 
              @input="materialForm.file = $event.target.files[0]" 
              class="w-full border border-gray-250 p-2 text-xs focus:ring-1 focus:ring-emerald-500 outline-none" 
            />
            <p v-if="isEditingMaterial" class="text-[10px] text-gray-400 mt-1">Kosongkan jika tidak ingin mengganti file.</p>
          </div>

          <div class="pt-4 border-t border-gray-100 flex gap-3">
            <button type="button" @click="showMaterialModal = false" class="flex-1 bg-white border border-gray-200 text-gray-700 px-4 py-2.5 rounded-lg text-sm font-medium hover:bg-gray-50 transition-colors">Batal</button>
            <button type="submit" class="flex-1 bg-emerald-700 hover:bg-emerald-800 text-white px-5 py-2.5 rounded-lg text-sm font-bold shadow-sm transition-colors">{{ isEditingMaterial ? 'Simpan Perubahan' : 'Unggah' }}</button>
          </div>
        </form>
      </div>
    </div>

    <!-- MODAL: ACTIVITY FORM (ADD/EDIT) -->
    <div v-if="showActivityModal" class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4" role="dialog" aria-modal="true">
      <div class="fixed inset-0 bg-emerald-950/40 backdrop-blur-sm transition-opacity" @click="showActivityModal = false"></div>

      <div class="relative bg-white rounded-t-2xl sm:rounded-2xl w-full sm:max-w-md max-h-[90vh] overflow-y-auto shadow-2xl border border-emerald-50 animate-slide-up">
        <div class="bg-emerald-800 text-white px-5 py-4 flex items-center justify-between sticky top-0 z-10">
          <h3 class="text-base sm:text-lg font-bold">{{ isEditingActivity ? 'Edit Sesi Kajian' : 'Buat Sesi Kajian Baru' }}</h3>
          <button @click="showActivityModal = false" class="text-emerald-200 hover:text-white transition-colors p-1">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <form @submit.prevent="submitActivityForm" class="p-6 space-y-4">
          <div v-if="!isEditingActivity" class="space-y-1">
            <label for="act_group" class="text-xs font-bold text-gray-500 uppercase block">Kelompok Halaqah</label>
            <select id="act_group" v-model="activityForm.group_id" required class="w-full border border-gray-200 rounded-lg p-2.5 text-sm focus:ring-1 focus:ring-emerald-500 outline-none">
              <option value="" disabled selected>Pilih Kelompok...</option>
              <option v-for="g in groups" :key="g.id" :value="g.id">{{ g.name }}</option>
            </select>
          </div>

          <div class="space-y-1">
            <label for="act_topic" class="text-xs font-bold text-gray-500 uppercase block">Topik Pembahasan</label>
            <input type="text" id="act_topic" v-model="activityForm.topic" required class="w-full border border-gray-200 rounded-lg p-2.5 text-sm focus:ring-1 focus:ring-emerald-500 outline-none" />
          </div>

          <div class="space-y-1">
            <label for="act_date" class="text-xs font-bold text-gray-500 uppercase block">Jadwal Pertemuan</label>
            <input type="datetime-local" id="act_date" v-model="activityForm.date" required class="w-full border border-gray-200 rounded-lg p-2.5 text-sm focus:ring-1 focus:ring-emerald-500 outline-none" />
          </div>

          <div class="space-y-1">
            <label for="act_desc" class="text-xs font-bold text-gray-500 uppercase block">Deskripsi / Catatan Sesi</label>
            <textarea id="act_desc" v-model="activityForm.description" rows="3" class="w-full border border-gray-200 rounded-lg p-2.5 text-sm focus:ring-1 focus:ring-emerald-500 outline-none"></textarea>
          </div>

          <div class="pt-4 border-t border-gray-100 flex gap-3">
            <button type="button" @click="showActivityModal = false" class="flex-1 bg-white border border-gray-200 text-gray-700 px-4 py-2.5 rounded-lg text-sm font-medium hover:bg-gray-50 transition-colors">Batal</button>
            <button type="submit" class="flex-1 bg-emerald-700 hover:bg-emerald-800 text-white px-5 py-2.5 rounded-lg text-sm font-bold shadow-sm transition-colors">Simpan Sesi</button>
          </div>
        </form>
      </div>
    </div>

    <!-- MODAL: GRADE FORM -->
    <div v-if="showGradeModal" class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4" role="dialog" aria-modal="true">
      <div class="fixed inset-0 bg-emerald-950/40 backdrop-blur-sm transition-opacity" @click="showGradeModal = false"></div>

      <div class="relative bg-white rounded-t-2xl sm:rounded-2xl w-full sm:max-w-md max-h-[90vh] overflow-y-auto shadow-2xl border border-emerald-50 animate-slide-up">
        <div class="bg-emerald-800 text-white px-5 py-4 flex items-center justify-between sticky top-0 z-10">
          <h3 class="text-base sm:text-lg font-bold">Input Nilai Pembinaan Bulanan</h3>
          <button @click="showGradeModal = false" class="text-emerald-200 hover:text-white transition-colors p-1">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <form @submit.prevent="submitGradeForm" class="p-6 space-y-4">
          <div class="space-y-1">
            <label for="grd_member" class="text-xs font-bold text-gray-500 uppercase block">Anggota (Mutarabbi)</label>
            <select id="grd_member" v-model="gradeForm.user_id" required class="w-full border border-gray-200 rounded-lg p-2.5 text-sm focus:ring-1 focus:ring-emerald-500 outline-none">
              <option value="" disabled selected>Pilih Anggota...</option>
              <option v-for="m in potentialMembers" :key="m.id" :value="m.id">{{ m.name }}</option>
            </select>
          </div>

          <div class="space-y-1">
            <label for="grd_ustad" class="text-xs font-bold text-gray-500 uppercase block">Penilai (Ustad Pembina)</label>
            <select id="grd_ustad" v-model="gradeForm.ustad_id" required class="w-full border border-gray-200 rounded-lg p-2.5 text-sm focus:ring-1 focus:ring-emerald-500 outline-none">
              <option value="" disabled selected>Pilih Ustad...</option>
              <option v-for="u in ustads" :key="u.id" :value="u.id">{{ u.name }}</option>
            </select>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div class="space-y-1">
              <label for="grd_month" class="text-xs font-bold text-gray-500 uppercase block">Bulan</label>
              <select id="grd_month" v-model="gradeForm.month" required class="w-full border border-gray-200 rounded-lg p-2 text-sm focus:ring-1 focus:ring-emerald-500 outline-none">
                <option v-for="m in 12" :key="m" :value="m">Bulan {{ m }}</option>
              </select>
            </div>
            <div class="space-y-1">
              <label for="grd_year" class="text-xs font-bold text-gray-500 uppercase block">Tahun</label>
              <input type="number" id="grd_year" v-model="gradeForm.year" required min="2020" max="2100" class="w-full border border-gray-200 rounded-lg p-2 text-sm focus:ring-1 focus:ring-emerald-500 outline-none" />
            </div>
          </div>

          <!-- Breakdown of Scores -->
          <div class="bg-gray-50 p-3 rounded-lg border border-gray-250 space-y-3">
            <span class="text-xs font-bold text-emerald-900 block mb-1">Rincian Penilaian Bulanan</span>
            <div class="grid grid-cols-2 gap-3">
              <div class="space-y-1">
                <label for="grd_kehadiran" class="text-[10px] font-bold text-gray-500 uppercase block">Kehadiran (Max 25)</label>
                <input type="number" id="grd_kehadiran" v-model.number="singleGradeKehadiran" min="0" max="25" class="w-full border border-gray-200 rounded-lg p-2 text-xs focus:ring-1 focus:ring-emerald-500 outline-none text-center" />
              </div>
              <div class="space-y-1">
                <label for="grd_kedisiplinan" class="text-[10px] font-bold text-gray-500 uppercase block">Kedisiplinan (Max 10)</label>
                <input type="number" id="grd_kedisiplinan" v-model.number="singleGradeKedisiplinan" min="0" max="10" class="w-full border border-gray-200 rounded-lg p-2 text-xs focus:ring-1 focus:ring-emerald-500 outline-none text-center" />
              </div>
              <div class="space-y-1">
                <label for="grd_partisipasi" class="text-[10px] font-bold text-gray-500 uppercase block">Partisipasi (Max 10)</label>
                <input type="number" id="grd_partisipasi" v-model.number="singleGradePartisipasi" min="0" max="10" class="w-full border border-gray-200 rounded-lg p-2 text-xs focus:ring-1 focus:ring-emerald-500 outline-none text-center" />
              </div>
              <div class="space-y-1">
                <label for="grd_sikap" class="text-[10px] font-bold text-gray-500 uppercase block">Sikap & Etika (Max 10)</label>
                <input type="number" id="grd_sikap" v-model.number="singleGradeSikap" min="0" max="10" class="w-full border border-gray-200 rounded-lg p-2 text-xs focus:ring-1 focus:ring-emerald-500 outline-none text-center" />
              </div>
              <div class="space-y-1">
                <label for="grd_pemahaman" class="text-[10px] font-bold text-gray-500 uppercase block">Pemahaman (Max 20)</label>
                <input type="number" id="grd_pemahaman" v-model.number="singleGradePemahaman" min="0" max="20" class="w-full border border-gray-200 rounded-lg p-2 text-xs focus:ring-1 focus:ring-emerald-500 outline-none text-center" />
              </div>
              <div class="space-y-1">
                <label for="grd_alquran" class="text-[10px] font-bold text-gray-500 uppercase block">Al-Quran (Max 25)</label>
                <input type="number" id="grd_alquran" v-model.number="singleGradeAlQuran" min="0" max="25" class="w-full border border-gray-200 rounded-lg p-2 text-xs focus:ring-1 focus:ring-emerald-500 outline-none text-center" />
              </div>
            </div>
            <div class="pt-2 border-t border-dashed border-gray-200 flex justify-between items-center text-xs font-bold text-emerald-950">
              <span>Total Nilai Terhitung:</span>
              <span class="text-sm bg-emerald-100 px-3 py-1 rounded-lg">{{ computedSingleScore }}/100</span>
            </div>
          </div>

          <div class="space-y-1">
            <label for="grd_notes" class="text-xs font-bold text-gray-500 uppercase block">Catatan Tambahan / Perkembangan</label>
            <textarea id="grd_notes" v-model="singleGradeNotesText" rows="2" placeholder="Pencapaian, hafalan quran, keaktifan, dll." class="w-full border border-gray-200 rounded-lg p-2.5 text-sm focus:ring-1 focus:ring-emerald-500 outline-none"></textarea>
          </div>

          <div class="pt-4 border-t border-gray-100 flex gap-3">
            <button type="button" @click="showGradeModal = false" class="flex-1 bg-white border border-gray-200 text-gray-700 px-4 py-2.5 rounded-lg text-sm font-medium hover:bg-gray-50 transition-colors">Batal</button>
            <button type="submit" class="flex-1 bg-emerald-700 hover:bg-emerald-800 text-white px-5 py-2.5 rounded-lg text-sm font-bold shadow-sm transition-colors">Simpan Nilai</button>
          </div>
        </form>
      </div>
    </div>

    <!-- MODAL: ATTENDANCE FORM (INPUT ABSEN) -->
    <div v-if="showAttendanceModal" class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4" role="dialog" aria-modal="true">
      <div class="fixed inset-0 bg-emerald-950/40 backdrop-blur-sm transition-opacity" @click="showAttendanceModal = false"></div>

      <div class="relative bg-white rounded-t-2xl sm:rounded-2xl w-full sm:max-w-lg max-h-[90vh] overflow-y-auto shadow-2xl border border-emerald-50 animate-slide-up">
        <div class="bg-emerald-800 text-white px-5 py-4 flex items-center justify-between sticky top-0 z-10">
          <h3 class="text-base sm:text-lg font-bold">Input/Edit Kehadiran Sesi</h3>
          <button @click="showAttendanceModal = false" class="text-emerald-200 hover:text-white transition-colors p-1">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <form @submit.prevent="submitAttendanceForm" class="p-6 space-y-4">
          <p class="text-xs text-gray-500 font-medium">Verifikasi langsung presensi untuk topik: <strong class="text-emerald-950">{{ selectedActivity?.topic }}</strong></p>
          
          <div class="max-h-80 overflow-y-auto divide-y divide-gray-100">
            <div v-for="(att, idx) in attendanceForm.attendances" :key="att.user_id" class="py-3 flex items-center justify-between">
              <span class="text-xs font-semibold text-gray-800">{{ att.name }}</span>
              <select v-model="attendanceForm.attendances[idx].status" class="border border-gray-200 rounded p-1 text-xs focus:ring-1 focus:ring-emerald-500 outline-none">
                <option value="present">Hadir</option>
                <option value="late">Terlambat</option>
                <option value="sick">Sakit</option>
                <option value="permission">Izin</option>
                <option value="absent">Alpa</option>
              </select>
            </div>
          </div>

          <div class="pt-4 border-t border-gray-100 flex gap-3">
            <button type="button" @click="showAttendanceModal = false" class="flex-1 bg-white border border-gray-200 text-gray-700 px-4 py-2.5 rounded-lg text-sm font-medium hover:bg-gray-50 transition-colors">Batal</button>
            <button type="submit" class="flex-1 bg-emerald-700 hover:bg-emerald-800 text-white px-5 py-2.5 rounded-lg text-sm font-bold shadow-sm transition-colors">Simpan Kehadiran</button>
          </div>
        </form>
      </div>
    </div>

    <!-- MODAL: APPROVE USER REGISTRATION -->
    <div v-if="showApproveModal" class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4" role="dialog" aria-modal="true">
      <div class="fixed inset-0 bg-emerald-950/40 backdrop-blur-sm transition-opacity" @click="showApproveModal = false"></div>

      <div class="relative bg-white rounded-t-2xl sm:rounded-2xl w-full sm:max-w-md max-h-[90vh] overflow-y-auto shadow-2xl border border-emerald-50 animate-slide-up">
        <div class="bg-emerald-800 text-white px-5 py-4 flex items-center justify-between sticky top-0 z-10">
          <h3 class="text-base sm:text-lg font-bold">Approve Pendaftaran</h3>
          <button @click="showApproveModal = false" class="text-emerald-200 hover:text-white transition-colors p-1">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <form @submit.prevent="submitApprove" class="p-6 space-y-4">
          <div class="bg-amber-50 p-4 rounded-lg border border-amber-100">
            <p class="text-xs text-gray-500 font-medium mb-2">Data Pendaftar:</p>
            <p class="text-sm font-bold text-gray-900">{{ approvingUser?.name }}</p>
            <p class="text-xs text-gray-600">{{ approvingUser?.email }}</p>
            <div class="grid grid-cols-2 gap-2 mt-2 text-xs">
              <div><span class="text-gray-400">WhatsApp:</span> <strong>{{ approvingUser?.phone }}</strong></div>
              <div><span class="text-gray-400">Gender:</span> <strong>{{ approvingUser?.gender === 'ikhwan' ? 'Ikhwan' : 'Akhwat' }}</strong></div>
              <div class="col-span-2"><span class="text-gray-400">Departemen:</span> <strong>{{ approvingUser?.department }}</strong></div>
            </div>
          </div>

          <div class="space-y-1">
            <label for="approve_role" class="text-xs font-bold text-gray-500 uppercase block">Tentukan Role (Hak Akses)</label>
            <select id="approve_role" v-model="approveForm.role_id" required class="w-full border border-gray-200 rounded-lg p-2.5 text-sm focus:ring-1 focus:ring-emerald-500 outline-none">
              <option value="" disabled selected>Pilih role untuk user ini...</option>
              <option v-for="role in roles" :key="role.id" :value="role.id">{{ role.name }} — {{ role.desc }}</option>
            </select>
          </div>

          <div class="pt-4 border-t border-gray-100 flex gap-3">
            <button type="button" @click="showApproveModal = false" class="flex-1 bg-white border border-gray-200 text-gray-700 px-4 py-2.5 rounded-lg text-sm font-medium hover:bg-gray-50 transition-colors">Batal</button>
            <button type="submit" class="flex-1 bg-emerald-700 hover:bg-emerald-800 text-white px-5 py-2.5 rounded-lg text-sm font-bold shadow-sm transition-colors">Approve</button>
          </div>
        </form>
      </div>
    </div>

    <!-- MODAL: IMPORT USER FROM CSV/EXCEL -->
    <div v-if="showImportModal" class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4" role="dialog" aria-modal="true">
      <div class="fixed inset-0 bg-emerald-950/40 backdrop-blur-sm transition-opacity" @click="showImportModal = false"></div>

      <div class="relative bg-white rounded-t-2xl sm:rounded-2xl w-full sm:max-w-md max-h-[90vh] overflow-y-auto shadow-2xl border border-emerald-50 animate-slide-up">
        <div class="bg-emerald-800 text-white px-5 py-4 flex items-center justify-between sticky top-0 z-10">
          <h3 class="text-base sm:text-lg font-bold">Impor Akun Pengguna</h3>
          <button @click="showImportModal = false" class="text-emerald-200 hover:text-white transition-colors p-1">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <form @submit.prevent="submitImport" class="p-6 space-y-5">
          <div class="bg-emerald-50/50 p-4 rounded-xl border border-emerald-100 space-y-2">
            <h4 class="text-xs font-bold text-emerald-850 uppercase">Panduan Impor Data</h4>
            <ul class="text-[11px] text-gray-600 list-disc list-inside space-y-1">
              <li>Unggah file berformat <strong>.csv</strong> saja.</li>
              <li>Pastikan kolom pertama adalah <strong>Nama Lengkap</strong> dan kolom kedua adalah <strong>Email</strong> (wajib).</li>
              <li>No Telepon, Gender, Departemen, Peran, dan Nama Kelompok bersifat opsional.</li>
              <li>Format Gender: <strong>ikhwan</strong> atau <strong>akhwat</strong>.</li>
              <li>Peran yang valid: <strong>member</strong>, <strong>leader</strong>, atau <strong>ustad</strong>.</li>
              <li>Nama Kelompok harus persis sama dengan yang ada di database.</li>
              <li>Password akun baru otomatis diset ke <strong>password123</strong>.</li>
            </ul>
            <div class="pt-2">
              <button 
                type="button" 
                @click="downloadCSVTemplate" 
                class="text-[11px] text-emerald-700 hover:text-emerald-950 font-bold underline flex items-center gap-1"
              >
                📥 Unduh Template CSV Impor
              </button>
            </div>
          </div>

          <div class="space-y-1.5">
            <label for="import_file" class="text-xs font-bold text-gray-500 uppercase block">Pilih File CSV</label>
            <input 
              id="import_file" 
              type="file" 
              accept=".csv" 
              required 
              @change="handleImportFileUpload" 
              class="w-full border border-gray-200 rounded-lg p-2 text-sm focus:ring-1 focus:ring-emerald-500 outline-none file:mr-3 file:py-1 file:px-2.5 file:rounded file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100"
            />
          </div>

          <div class="pt-4 border-t border-gray-100 flex gap-3">
            <button type="button" @click="showImportModal = false" class="flex-1 bg-white border border-gray-200 text-gray-700 px-4 py-2.5 rounded-lg text-sm font-medium hover:bg-gray-50 transition-colors">Batal</button>
            <button 
              type="submit" 
              :disabled="importForm.processing"
              class="flex-1 bg-emerald-700 hover:bg-emerald-800 disabled:bg-gray-300 text-white px-5 py-2.5 rounded-lg text-sm font-bold shadow-sm transition-colors flex items-center justify-center gap-1.5"
            >
              <svg v-if="importForm.processing" class="animate-spin h-3.5 w-3.5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              <span>Mulai Impor</span>
            </button>
          </div>
        </form>
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
