<script setup>
import { ref, computed, nextTick } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import axios from 'axios';

const props = defineProps({
  auth: Object,
  surahs: Array,
  bookmarks: Array,
});

// ── State ─────────────────────────────────────────────────────────────────────
const activeTab       = ref('surahs'); // 'surahs' | 'bookmarks'
const searchQuery     = ref('');
const selectedSurah   = ref(null);
const surahData       = ref(null);    // loaded surah detail
const loadingSurah    = ref(false);
const tafsirData      = ref({});      // { ayahNumber: tafsirText }
const loadingTafsir   = ref({});      // { ayahNumber: true/false }
const openTafsirAyah  = ref(null);    // currently open tafsir ayah number
const playingAudio    = ref(null);    // currently playing ayah number
const audioRef        = ref(null);
const userBookmarks   = ref([...props.bookmarks]);
const bookmarkIds     = ref({});      // { `${surahNum}-${ayahNum}`: bookmarkId }

// ── Computed ──────────────────────────────────────────────────────────────────
const filteredSurahs = computed(() => {
  if (!props.surahs) return [];
  const q = searchQuery.value.toLowerCase().trim();
  if (!q) return props.surahs;
  return props.surahs.filter(s =>
    s.englishName.toLowerCase().includes(q) ||
    s.name.includes(q) ||
    s.englishNameTranslation.toLowerCase().includes(q) ||
    s.number.toString() === q
  );
});

// ── Surah Reader ──────────────────────────────────────────────────────────────
const openSurah = async (surah) => {
  selectedSurah.value = surah;
  surahData.value     = null;
  tafsirData.value    = {};
  openTafsirAyah.value = null;
  stopAudio();
  loadingSurah.value  = true;

  try {
    const res = await axios.get(`/quran/surah/${surah.number}`);
    surahData.value = res.data;

    // Map bookmark IDs
    res.data.ayahs.forEach(a => {
      if (a.bookmark_id) {
        bookmarkIds.value[`${surah.number}-${a.number}`] = a.bookmark_id;
      }
    });
  } catch (e) {
    alert('Gagal memuat surah. Coba lagi nanti.');
  } finally {
    loadingSurah.value = false;
  }
};

const closeSurah = () => {
  selectedSurah.value = null;
  surahData.value     = null;
  tafsirData.value    = {};
  openTafsirAyah.value = null;
  stopAudio();
};

// ── Audio ─────────────────────────────────────────────────────────────────────
const playAudio = (ayahNumber, audioUrl) => {
  if (!audioUrl) return;
  stopAudio();
  playingAudio.value = ayahNumber;
  audioRef.value = new Audio(audioUrl);
  audioRef.value.play();
  audioRef.value.onended = () => { playingAudio.value = null; };
};

const stopAudio = () => {
  if (audioRef.value) {
    audioRef.value.pause();
    audioRef.value.currentTime = 0;
    audioRef.value = null;
  }
  playingAudio.value = null;
};

// ── Tafsir ────────────────────────────────────────────────────────────────────
const toggleTafsir = async (ayahNumber) => {
  if (openTafsirAyah.value === ayahNumber) {
    openTafsirAyah.value = null;
    return;
  }
  openTafsirAyah.value = ayahNumber;

  if (!tafsirData.value[ayahNumber] && selectedSurah.value) {
    // Load tafsir for whole surah once
    if (!tafsirData.value._loaded) {
      loadingTafsir.value[ayahNumber] = true;
      try {
        const res = await axios.get(`/quran/tafsir/${selectedSurah.value.number}`);
        const map = {};
        res.data.ayahs.forEach(a => { map[a.number] = a.tafsir; });
        tafsirData.value = { ...map, _loaded: true };
      } catch (e) {
        tafsirData.value[ayahNumber] = 'Gagal memuat tafsir.';
      } finally {
        loadingTafsir.value[ayahNumber] = false;
      }
    }
  }
};

// ── Bookmarks ─────────────────────────────────────────────────────────────────
const isBookmarked = (surahNum, ayahNum) => {
  return !!bookmarkIds.value[`${surahNum}-${ayahNum}`];
};

const toggleBookmark = async (ayah) => {
  if (!selectedSurah.value) return;
  const key = `${selectedSurah.value.number}-${ayah.number}`;

  if (bookmarkIds.value[key]) {
    // Remove
    try {
      await axios.delete(`/quran/bookmarks/${bookmarkIds.value[key]}`);
      delete bookmarkIds.value[key];
      userBookmarks.value = userBookmarks.value.filter(b => !(b.surah_number === selectedSurah.value.number && b.ayah_number === ayah.number));
    } catch (e) {
      alert('Gagal menghapus bookmark.');
    }
  } else {
    // Add
    try {
      const res = await axios.post('/quran/bookmarks', {
        surah_number: selectedSurah.value.number,
        ayah_number:  ayah.number,
        surah_name:   selectedSurah.value.englishName,
        ayah_text:    ayah.text_indonesian?.slice(0, 200),
      });
      bookmarkIds.value[key] = res.data.id;
      userBookmarks.value.unshift({
        id: res.data.id,
        surah_number: selectedSurah.value.number,
        ayah_number: ayah.number,
        surah_name: selectedSurah.value.englishName,
        ayah_text: ayah.text_indonesian?.slice(0, 200),
        created_at: 'Baru saja',
      });
    } catch (e) {
      alert('Gagal menambah bookmark.');
    }
  }
};

const openBookmark = (bookmark) => {
  const surah = props.surahs.find(s => s.number === bookmark.surah_number);
  if (surah) openSurah(surah);
};

const removeBookmark = async (bookmark) => {
  try {
    await axios.delete(`/quran/bookmarks/${bookmark.id}`);
    userBookmarks.value = userBookmarks.value.filter(b => b.id !== bookmark.id);
    delete bookmarkIds.value[`${bookmark.surah_number}-${bookmark.ayah_number}`];
  } catch (e) {
    alert('Gagal menghapus bookmark.');
  }
};

// ── Logout ────────────────────────────────────────────────────────────────────
const handleLogout = () => { router.post('/logout'); };

// ── Dashboard URL based on role ─────────────────────────────────────────────
const dashboardUrl = computed(() => {
  const slug = props.auth?.role?.slug || props.auth?.user?.role?.slug || '';
  return slug ? `/${slug}/dashboard` : '/';
});

// ── Helpers ───────────────────────────────────────────────────────────────────
const revelationBadge = (type) => type === 'Meccan' ? 'Makkiyah' : 'Madaniyah';
</script>

<template>
  <Head title="Al-Quran Al-Karim" />

  <div class="min-h-screen bg-emerald-50/60 relative">
    <!-- Decorative background -->
    <div class="fixed inset-0 pointer-events-none overflow-hidden">
      <div class="absolute -top-32 -right-32 w-96 h-96 bg-emerald-100/50 rounded-full blur-3xl"></div>
      <div class="absolute -bottom-32 -left-32 w-96 h-96 bg-amber-50/60 rounded-full blur-3xl"></div>
    </div>

    <!-- Navigation Header -->
    <nav class="sticky top-0 z-30 bg-white border-b border-emerald-100 shadow-sm">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-14 sm:h-16">
          <div class="flex items-center space-x-2 sm:space-x-3">
            <div v-if="$page.props.settings?.logo_path" class="flex-shrink-0">
              <img :src="`/storage/${$page.props.settings.logo_path}`" class="h-8 w-8 sm:h-10 sm:w-10 object-contain rounded-xl" alt="Logo" />
            </div>
            <div v-else class="bg-emerald-700 text-amber-300 p-1.5 rounded-xl">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 sm:h-6 sm:w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
            </div>
            <div class="flex flex-col">
              <span class="text-sm sm:text-base font-bold text-emerald-900 leading-tight">Al-Quran Al-Karim</span>
              <span class="text-xs text-emerald-500 hidden sm:block">Baca, dengarkan, dan pelajari Al-Quran</span>
            </div>
          </div>
          <div class="flex items-center space-x-2">
            <!-- Back button when in reader -->
            <button v-if="selectedSurah" @click="closeSurah" class="flex items-center space-x-1 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-semibold rounded-lg px-3 py-2 text-xs transition-colors">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
              <span>Kembali</span>
            </button>
            <a
              :href="dashboardUrl"
              class="flex items-center space-x-1 bg-emerald-700 hover:bg-emerald-800 text-white font-semibold rounded-lg px-3 py-2 text-xs transition-colors shadow-sm"
              title="Kembali ke Dashboard"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-4 0a1 1 0 01-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 01-1 1" /></svg>
              <span class="hidden sm:inline">Dashboard</span>
            </a>
            <button @click="handleLogout" class="text-gray-500 hover:text-red-700 font-semibold transition-colors flex items-center space-x-1 p-1">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
              <span class="hidden sm:inline text-xs">Logout</span>
            </button>
          </div>
        </div>
      </div>
    </nav>

    <main class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 mt-4 sm:mt-6 relative z-10">

      <!-- ═══ SURAH LIST VIEW ═══ -->
      <template v-if="!selectedSurah">

        <!-- Hero Banner -->
        <div class="bg-gradient-to-r from-emerald-800 to-emerald-950 text-white rounded-2xl p-5 sm:p-7 shadow-xl mb-5 border border-emerald-700 relative overflow-hidden">
          <div class="absolute inset-0 opacity-5">
            <div class="absolute top-4 right-8 text-8xl font-arabic select-none leading-none">بِسْمِ اللَّهِ</div>
          </div>
          <div class="relative">
            <p class="text-amber-300 text-xs font-bold uppercase tracking-widest mb-1">Al-Quran Digital</p>
            <h1 class="text-2xl sm:text-3xl font-bold mb-1">Al-Quran Al-Karim</h1>
            <p class="text-emerald-200 text-xs sm:text-sm max-w-lg">Baca 114 surah dengan terjemahan Indonesia, audio murottal Syekh Mishary Alafasy, dan tafsir Ibn Kathir.</p>
          </div>
        </div>

        <!-- Tab Switcher -->
        <div class="flex items-center gap-2 mb-4">
          <button
            @click="activeTab = 'surahs'"
            :class="activeTab === 'surahs' ? 'bg-emerald-700 text-white shadow-md' : 'bg-white text-emerald-700 border border-emerald-200 hover:bg-emerald-50'"
            class="flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold transition-all"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
            Daftar Surah
          </button>
          <button
            @click="activeTab = 'bookmarks'"
            :class="activeTab === 'bookmarks' ? 'bg-emerald-700 text-white shadow-md' : 'bg-white text-emerald-700 border border-emerald-200 hover:bg-emerald-50'"
            class="flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold transition-all"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" /></svg>
            Bookmark
            <span v-if="userBookmarks.length" class="bg-amber-400 text-emerald-900 text-xs rounded-full px-1.5 py-0.5 font-bold">{{ userBookmarks.length }}</span>
          </button>
        </div>

        <!-- Surahs Tab -->
        <div v-show="activeTab === 'surahs'">
          <!-- Search -->
          <div class="relative mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Cari surah (nama, nomor, atau arti)..."
              class="w-full pl-10 pr-4 py-2.5 bg-white border border-emerald-200 rounded-xl text-sm text-emerald-900 placeholder-emerald-300 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent shadow-sm"
            />
          </div>

          <!-- Surah Grid -->
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
            <button
              v-for="surah in filteredSurahs"
              :key="surah.number"
              @click="openSurah(surah)"
              class="bg-white hover:bg-emerald-50/60 border border-emerald-100 hover:border-emerald-300 rounded-xl p-3.5 text-left transition-all group shadow-sm hover:shadow-md"
            >
              <div class="flex items-start justify-between">
                <div class="flex items-center gap-3">
                  <div class="w-9 h-9 bg-emerald-100 group-hover:bg-emerald-200 rounded-lg flex items-center justify-center transition-colors">
                    <span class="text-emerald-800 font-bold text-sm">{{ surah.number }}</span>
                  </div>
                  <div>
                    <p class="font-bold text-emerald-900 text-sm leading-tight">{{ surah.englishName }}</p>
                    <p class="text-xs text-emerald-500">{{ surah.englishNameTranslation }}</p>
                  </div>
                </div>
                <div class="text-right">
                  <p class="font-arabic text-xl text-emerald-800 leading-none">{{ surah.name }}</p>
                  <div class="flex items-center gap-1 mt-1 justify-end">
                    <span class="text-xs text-emerald-400">{{ surah.numberOfAyahs }} ayat</span>
                    <span :class="surah.revelationType === 'Meccan' ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-600'" class="text-xs rounded-full px-1.5 py-0.5 font-medium">{{ revelationBadge(surah.revelationType) }}</span>
                  </div>
                </div>
              </div>
            </button>
          </div>

          <p v-if="filteredSurahs.length === 0" class="text-center text-emerald-400 text-sm py-10">Tidak ada surah yang cocok dengan pencarian.</p>
        </div>

        <!-- Bookmarks Tab -->
        <div v-show="activeTab === 'bookmarks'">
          <p v-if="userBookmarks.length === 0" class="text-center text-emerald-400 text-sm py-10">
            Belum ada bookmark. Tandai ayat favorit saat membaca surah.
          </p>
          <div v-else class="space-y-2">
            <div
              v-for="bookmark in userBookmarks"
              :key="bookmark.id"
              class="bg-white border border-emerald-100 rounded-xl p-4 flex items-start justify-between group hover:border-emerald-300 transition-all shadow-sm"
            >
              <button @click="openBookmark(bookmark)" class="flex-1 text-left">
                <div class="flex items-center gap-2 mb-1">
                  <span class="bg-emerald-100 text-emerald-700 text-xs font-bold rounded-lg px-2 py-0.5">{{ bookmark.surah_name }}</span>
                  <span class="text-xs text-emerald-400">Ayat {{ bookmark.ayah_number }}</span>
                  <span class="text-xs text-emerald-300 ml-auto">{{ bookmark.created_at }}</span>
                </div>
                <p class="text-sm text-emerald-800 line-clamp-2">{{ bookmark.ayah_text }}</p>
              </button>
              <button @click="removeBookmark(bookmark)" class="ml-3 text-gray-300 hover:text-red-500 transition-colors p-1 flex-shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
              </button>
            </div>
          </div>
        </div>
      </template>

      <!-- ═══ SURAH READER VIEW ═══ -->
      <template v-else>
        <!-- Surah Header -->
        <div class="bg-gradient-to-r from-emerald-800 to-emerald-950 text-white rounded-2xl p-5 sm:p-7 shadow-xl mb-5 border border-emerald-700 relative overflow-hidden">
          <div class="absolute inset-0 opacity-5">
            <div class="absolute top-4 right-8 text-7xl font-arabic select-none leading-none">{{ surahData?.name }}</div>
          </div>
          <div class="relative">
            <p class="text-amber-300 text-xs font-bold uppercase tracking-widest mb-1">Surah ke-{{ selectedSurah.number }}</p>
            <h1 class="text-2xl sm:text-3xl font-bold mb-1">{{ surahData?.englishName || selectedSurah.englishName }}</h1>
            <p class="text-emerald-200 text-xs sm:text-sm">{{ surahData?.englishNameTranslation }} · {{ surahData?.numberOfAyahs || selectedSurah.numberOfAyahs }} ayat · {{ revelationBadge(surahData?.revelationType || selectedSurah.revelationType) }}</p>
          </div>
        </div>

        <!-- Loading state -->
        <div v-if="loadingSurah" class="text-center py-16">
          <div class="inline-block w-8 h-8 border-4 border-emerald-200 border-t-emerald-700 rounded-full animate-spin mb-3"></div>
          <p class="text-emerald-500 text-sm">Memuat surah...</p>
        </div>

        <!-- Ayahs list -->
        <div v-else-if="surahData?.ayahs" class="space-y-3 pb-8">

          <!-- Bismillah (except Al-Fatihah and At-Tawbah) -->
          <div v-if="selectedSurah.number !== 1 && selectedSurah.number !== 9" class="text-center py-6">
            <p class="font-arabic text-2xl sm:text-3xl text-emerald-900 leading-loose">بِسْمِ اللَّهِ الرَّحْمَٰنِ الرَّحِيمِ</p>
            <p class="text-xs text-emerald-500 mt-1">Dengan nama Allah Yang Maha Pengasih, Maha Penyayang</p>
          </div>

          <div
            v-for="ayah in surahData.ayahs"
            :key="ayah.number"
            class="bg-white border border-emerald-100 rounded-2xl p-4 sm:p-5 shadow-sm"
          >
            <!-- Ayah number badge + actions -->
            <div class="flex items-center justify-between mb-4">
              <div class="flex items-center gap-2">
                <span class="bg-emerald-100 text-emerald-700 text-xs font-bold rounded-lg w-8 h-8 flex items-center justify-center">{{ ayah.number }}</span>
                <span class="text-xs text-emerald-400">Juz {{ ayah.juz }} · Hal. {{ ayah.page }}</span>
              </div>
              <div class="flex items-center gap-1">
                <!-- Audio play/pause -->
                <button
                  @click="playingAudio === ayah.number ? stopAudio() : playAudio(ayah.number, ayah.audio_url)"
                  :class="playingAudio === ayah.number ? 'bg-emerald-700 text-white' : 'bg-emerald-50 hover:bg-emerald-100 text-emerald-600'"
                  class="p-2 rounded-lg transition-colors"
                  title="Putar audio"
                >
                  <svg v-if="playingAudio !== ayah.number" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                  <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/></svg>
                </button>
                <!-- Tafsir toggle -->
                <button
                  @click="toggleTafsir(ayah.number)"
                  :class="openTafsirAyah === ayah.number ? 'bg-amber-600 text-white' : 'bg-amber-50 hover:bg-amber-100 text-amber-600'"
                  class="p-2 rounded-lg transition-colors"
                  title="Tafsir"
                >
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                </button>
                <!-- Bookmark toggle -->
                <button
                  @click="toggleBookmark(ayah)"
                  :class="isBookmarked(selectedSurah.number, ayah.number) ? 'bg-rose-500 text-white' : 'bg-rose-50 hover:bg-rose-100 text-rose-400'"
                  class="p-2 rounded-lg transition-colors"
                  title="Bookmark"
                >
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" :fill="isBookmarked(selectedSurah.number, ayah.number) ? 'currentColor' : 'none'" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" /></svg>
                </button>
              </div>
            </div>

            <!-- Arabic text -->
            <p class="font-arabic text-right text-2xl sm:text-3xl leading-[2.2] text-emerald-950 mb-4 select-text" dir="rtl">{{ ayah.text_arabic }}</p>

            <!-- Indonesian translation -->
            <div class="border-t border-emerald-50 pt-3">
              <p class="text-sm text-emerald-800 leading-relaxed">
                <span class="text-xs font-bold text-emerald-500 uppercase mr-1">ID:</span>
                {{ ayah.text_indonesian }}
              </p>
            </div>

            <!-- English translation -->
            <p class="text-xs text-emerald-500 leading-relaxed mt-2">
              <span class="font-bold text-emerald-400 uppercase mr-1">EN:</span>
              {{ ayah.text_english }}
            </p>

            <!-- Tafsir panel -->
            <div v-if="openTafsirAyah === ayah.number" class="mt-4 border-t border-amber-100 pt-4">
              <div class="flex items-center gap-1.5 mb-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                <span class="text-xs font-bold text-amber-600 uppercase">Tafsir Ibn Kathir</span>
              </div>
              <div v-if="loadingTafsir[ayah.number]" class="text-xs text-emerald-400">Memuat tafsir...</div>
              <div v-else-if="tafsirData[ayah.number]" class="text-sm text-emerald-700 leading-relaxed bg-amber-50/50 border border-amber-100 rounded-xl p-3 max-h-80 overflow-y-auto">
                {{ tafsirData[ayah.number] }}
              </div>
              <div v-else class="text-xs text-emerald-400">Tafsir tidak tersedia untuk ayat ini.</div>
            </div>
          </div>
        </div>

        <!-- Error state -->
        <div v-else-if="!loadingSurah" class="text-center py-16">
          <p class="text-red-500 text-sm">Gagal memuat surah. Coba refresh halaman.</p>
        </div>
      </template>
    </main>
  </div>
</template>

<style scoped>
.font-arabic {
  font-family: 'Amiri', 'Traditional Arabic', serif;
}
</style>
