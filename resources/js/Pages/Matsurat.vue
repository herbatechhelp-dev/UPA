<script setup>
import { ref, computed } from 'vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
  auth: Object,
});

// ── State ─────────────────────────────────────────────────────────────────────
const activeTimeMode  = ref('pagi');   // 'pagi' | 'petang'
const searchQuery     = ref('');

// ── Audio Recitation Player State ──────────────────────────────────────────────
const audioPlayerRef  = ref(null);
const isAudioPlaying  = ref(false);
const audioUrl = computed(() => {
  return activeTimeMode.value === 'pagi'
    ? 'https://archive.org/download/AlMathuratImamShaheedHasanAlBanna/Al-mathuratSugraDzikirPagi.mp3'
    : 'https://archive.org/download/AlMathuratImamShaheedHasanAlBanna/Al-mathuratSugraDzikirPetang.mp3';
});

const togglePlayRecitation = () => {
  if (!audioPlayerRef.value) return;
  if (isAudioPlaying.value) {
    audioPlayerRef.value.pause();
    isAudioPlaying.value = false;
  } else {
    audioPlayerRef.value.play().then(() => {
      isAudioPlaying.value = true;
    }).catch(() => {
      alert('Gagal memutar audio. Pastikan koneksi internet aktif.');
    });
  }
};

const handleAudioEnded = () => {
  isAudioPlaying.value = false;
};

// ── Curated Dhikr Data (Wazifah Sugra Lengkap) ──────────────────────────────────
const dhikrs = ref([
  {
    id: 1,
    title: 'Ta\'awwudh Khusus (Perlindungan Awal)',
    arabic: 'أَعُوذُ بِاللَّهِ السَّمِيعِ الْعَلِيمِ مِنَ الشَّيْطَانِ الرَّجِيمِ',
    latin: 'A\'udzu billahis-sami\'il-\'alimi minash-shaytanir-rajim.',
    translation: 'Aku berlindung kepada Allah Yang Maha Mendengar lagi Maha Mengetahui dari godaan syaitan yang terkutuk.',
    target: 3,
    count: 0,
    fadilah: 'Melindungi pembacanya dari gangguan bisikan syetan sejak awal memulai dzikir.'
  },
  {
    id: 2,
    title: 'Surah Al-Fatihah (Pembuka)',
    arabic: 'بِسْمِ اللَّهِ الرَّحْمَٰنِ الرَّحِيمِ ۝ الْحَمْدُ Lِلَّهِ رَبِّ الْعَالَمِينَ ۝ الرَّحْمَٰنِ الرَّحِيمِ ۝ مَالِكِ يَوْمِ الدِّينِ ۝ إِيَّاكَ نَعْبُدُ وَإِيَّاكَ نَسْتَعِينُ ۝ اهْدِنَا الصِّرَاطَ الْمُسْتَقِيمَ ۝ صِرَاطَ الَّذِينَ أَنْعَمْتَ عَلَيْهِمْ غَيْرِ الْمَغْضُوبِ عَلَيْهِمْ وَلَا الضَّالِّينَ',
    latin: 'Bismillahir-rahmanir-rahim. Al-hamdu lillahi rabbil-\'alamin. Ar-rahmanir-rahim. Maliki yawmid-din. Iyyaka na\'budu wa iyyaka nasta\'in. Ihdinas-siratal-mustaqim. Siratal-ladhina an\'amta \'alayhim ghayril-maghdubi \'alayhim walad-dallin.',
    translation: 'Dengan nama Allah Yang Maha Pengasih, Maha Penyayang. Segala puji bagi Allah, Tuhan seluruh alam. Yang Maha Pengasih, Maha Penyayang. Pemilik hari pembalasan. Hanya kepada Engkaulah kami menyembah dan hanya kepada Engkaulah kami memohon pertolongan. Tunjukilah kami jalan yang lurus. (yaitu) jalan orang-orang yang telah Engkau beri nikmat kepadanya; bukan (jalan) mereka yang dimurkai, dan bukan (pula jalan) mereka yang sesat.',
    target: 1,
    count: 0,
    fadilah: 'Sebagai penawar segala racun dan penyakit hati, serta rukun utama dalam bermunajat.'
  },
  {
    id: 3,
    title: 'Surah Al-Baqarah (Ayat 1-5)',
    arabic: 'الم ۝ ذَٰلِكَ الْكِتَابُ لَا رَيْبَ ۛ فِيهِ ۛ هُدًى لِّلْمُتَّقِينَ ۝ الَّذِينَ يُؤْمِنُونَ بِالْغَيْبِ وَيُقِيمُونَ الصَّلَاةَ وَمِمَّا رَزَقْنَاهُمْ يُنفِقُونَ ۝ وَالَّذِينَ يُؤْمِنُونَ بِمَا أُنزِلَ إِلَيْكَ وَمَا أُنزِلَ مِن قَبْلِكَ وَبِالْآخِرَةِ هُمْ يُوقِنُونَ ۝ أُولَٰئِكَ عَلَىٰ هُدًى مِّن رَّبِّهِمْ ۖ وَأُولَٰئِكَ هُمُ الْمُفْلِحُونَ',
    latin: 'Alif-lam-mim. Dzalikal-kitabu la rayba fih, hudan lil-muttaqin. Al-ladhina yu\'minuna bil-ghaybi wa yuqimunas-salata wa mimma razaqnahum yunfiqun. Wal-ladhina yu\'minuna bima unzila ilayka wa ma unzila min qablik, wa bil-akhirati hum yuqinun. Ula\'ika \'ala hudam mir rabbihim wa ula\'ika humul-muflihun.',
    translation: 'Alif Lam Mim. Kitab (Al-Quran) ini tidak ada keraguan padanya; petunjuk bagi mereka yang bertakwa. (yaitu) mereka yang beriman kepada yang ghaib, yang mendirikan shalat, dan menafkahkan sebagian rezeki yang Kami anugerahkan kepada mereka. Dan mereka yang beriman kepada Kitab (Al-Quran) yang telah diturunkan kepadamu dan Kitab-kitab yang telah diturunkan sebelummu, serta mereka yakin akan adanya (kehidupan) akhirat. Mereka itulah yang tetap mendapat petunjuk dari Tuhannya, dan merekalah orang-orang yang beruntung.',
    target: 1,
    count: 0,
    fadilah: 'Syaitan tidak akan sanggup memasuki atau mendekati rumah yang dibacakan ayat-ayat ini.'
  },
  {
    id: 4,
    title: 'Ayat Kursi (Surah Al-Baqarah Ayat 255)',
    arabic: 'اللَّهُ لَا إِلَٰهَ إِلَّا هُوَ الْحَيُّ الْقَيُّومُ ۚ لَا تَأْخُذُهُ سِنَةٌ وَلَا نَوْمٌ ۚ لَّهُ مَا فِي السَّمَاوَاتِ وَمَا فِي الْأَرْضِ ۗ مَن ذَا الَّذِي يَشْفَعُ عِندَهُ إِلَّا بِإِذْنِهِ ۚ يَعْلَمُ مَا بَيْنَ أَيْدِيهِمْ وَمَا خَلْفَهُمْ ۖ وَلَا يُحِيطُونَ بِشَيْءٍ مِّنْ عِلْمِهِ إِلَّا بِمَا شَاءَ ۚ وَسِعَ كُرْسِيُّهُ السَّمَاوَاتِ وَالْأَرْضَ ۖ وَلَا يَئُودُهُ hِفْظُهُمَا ۚ وَهُوَ الْعَلِيُّ الْعَظِيمُ',
    latin: 'Allahu la ilaha illa huwal-hayyul-qayyum, la ta\'khudhuhu sinatuw-wa la nawm, lahu ma fis-samawati wa ma fil-ardh, man dhal-ladhi yashfa\'u \'indahu illa bi-idhnih, ya\'lamu ma bayna aydihim wa ma khalfahum, wa la yuhituna bi-shay\'im min \'ilmihi illa bima sha\', wasi\'a kursiyyuhus-samawati wal-ardh, wa la ya\'uduhu hifzhuhuma, wa huwal-\'aliyyul-\'azhim.',
    translation: 'Allah, tidak ada Tuhan (yang berhak disembah) melainkan Dia Yang Hidup kekal lagi terus-menerus mengurus (makhluk-Nya); tidak mengantuk dan tidak tidur. Kepunyaan-Nya apa yang di langit dan di bumi. Tiada yang dapat memberi syafa\'at di sisi Allah tanpa izin-Nya? Allah mengetahui apa-apa yang di hadapan mereka dan di belakang mereka, dan mereka tidak mengetahui apa-apa dari ilmu Allah melainkan apa yang dikehendaki-Nya. Kursi Allah meliputi langit dan bumi. Dan Allah tidak merasa berat memelihara keduanya, dan Allah Maha Tinggi lagi Maha Besar.',
    target: 1,
    count: 0,
    fadilah: 'Allah senantiasa mengirimkan malaikat penjaga, menghindari kejahatan jin/syaitan sepanjang hari.'
  },
  {
    id: 5,
    title: 'Surah Al-Baqarah (Ayat 256-257)',
    arabic: 'لَا إِكْرَاهَ فِي الدِّينِ ۖ قَد تَّبَيَّنَ الرُّشْدُ مِنَ الْغَيِّ ۚ فَمَن يَكْفُرْ بِالطَّاغُوتِ وَيُؤْمِن بِاللَّهِ فَقَدِ اسْتَمْسَكَ بِالْعُرْوَةِ الْوُثْقَىٰ لَا انفِصَامَ لَهَا ۗ وَاللَّهُ سَمِيعٌ عَلِيمٌ ۝ اللَّهُ وَلِيُّ الَّذِينَ آمَنُوا يُخْرِجُهُم مِّنَ الظُّلُمَاتِ إِلَى النُّورِ ۖ وَالَّذِينَ كَفَرُوا أَوْلِيَاؤُهُمُ الطَّاغُوتُ يُخْرِجُونَهُم مِّنَ النُّورِ إِلَى الظُّلُمَاتِ ۗ أُولَٰئِكَ أَصْحَابُ النَّارِ ۖ هُمْ فِيهَا خَالِدُونَ',
    latin: 'La ikraha fid-dini qad tabayyanar-rushdu minal-ghayy. Faman yakfur bit-taghuti wa yu\'min billahi faqadistamsaka bil-\'urwatil-wuthqa lanfisama laha. Wallahu sami\'un \'alim. Allahu waliyyul-ladhina amanu yukhrijuhum minadh-dhulumati ilan-nur. Wal-ladhina kafaru awliya\'uhumut-taghutu yukhrijunahum minan-nuri iladh-dhulumat. Ula\'ika as-habun-nari hum fiha khalidun.',
    translation: 'Tidak ada paksaan dalam menganut agama (Islam), sesungguhnya telah jelas perbedaan antara jalan yang benar dengan jalan yang sesat. Barangsiapa ingkar kepada Thaghut dan beriman kepada Allah, maka sungguh dia telah berpegang pada tali yang sangat kuat yang tidak akan putus. Allah Maha Mendengar, Maha Mengetahui. Allah pelindung orang-orang yang beriman. Dia mengeluarkan mereka dari kegelapan kepada cahaya. Dan orang-orang yang kafir, pelindung-pelindungnya adalah Thaghut, yang mengeluarkan mereka dari cahaya kepada kegelapan. Mereka adalah penghuni neraka, mereka kekal di dalamnya.',
    target: 1,
    count: 0,
    fadilah: 'Memperteguh iman tauhid dan mengingatkan jaminan Allah untuk menolong orang beriman.'
  },
  {
    id: 6,
    title: 'Surah Al-Baqarah (Ayat 284-286)',
    arabic: 'لِّلَّهِ مَا فِي السَّمَاوَاتِ وَمَا فِي الْأَرْضِ ۗ وَإِن تُبْدُوا مَا فِي أَنفُسِكُمْ أَوْ تُخْفُوهُ يُحَاسِبْكُم بِهِ اللَّهُ ۖ فَيَغْفِرُ لِمَن يَشَاءُ وَيُعَذِّبُ مَن يَشَاءُ ۗ وَاللَّهُ عَلَىٰ كُلِّ شَيْءٍ قَدِيرٌ ۝ آمَنَ الرَّسُولُ بِمَا أُنزِلَ إِلَيْهِ مِن رَّبِّهِ وَالْمُؤْمِنُونَ ۚ كُلٌّ آمَنَ بِاللَّهِ وَمَلَائِكَتِهِ وَكُtُبِهِ وَرُسُلِهِ لَا نُفَرِّقُ بَيْنَ أَحَدٍ مِّن رُwسُلِهِ ۚ وَقَالُوا سَمِعْنَا وَأَطَعْنَا ۖ غُفْرَانَكَ رَبَّنَا وَإِلَيْكَ الْمَصِيرُ ۝ لَا يُكَلِّفُ اللَّهُ نَفْسًا إِلَّا وُسْعَهَا ۚ لَهَا مَا كَسَبَتْ وَعَلَيْهَا مَا اكْتَسَبَتْ ۗ رَبَّنَا لَا تُؤَاخِذْنَا إِن نَّسِينَا أَوْ أَخْطَأْنَا ۚ رَبَّنَا وَلَا تَحْمِلْ عَلَيْنَا إِصْرًا كَمَا حَمَلْتَهُ عَلَى الَّذِينَ مِن قَبْلِكَ ۚ رَبَّنَا وَلَا تُحَمِّلْنَا مَا لَا طَاقَةَ لَنَا بِهِ ۖ وَاعْفُ عَنَّا وَاغْفِرْ لَنَا وَارْحَمْنَا ۚ أَنتَ مَوْلَانَا فَانصُرْنَا عَلَى الْقَوْمِ الْكَافِرِينَ',
    latin: 'Lillahi ma fis-samawati wa ma fil-ardh. Wa in tubdu ma fi anfusikum aw tukhfuhu yuhasibkum bihilla. Fa-yaghfiru limay-yasha\'u wa yu\'adhibu may-yasha\', wallahu \'ala kulli shay\'in qadir. Amanar-rasulu bima unzila ilayhi mir-rabbihi wal-mu\'minun. Kullun amana billahi wa mala\'ikatihi wa kutubihi wa rusulih, la nufarriqu bayna ahadim-mir-rusulih. Wa qalu sami\'na wa ata\'na ghufranaka rabbana wa ilaykal-masir. La yukallifullahu nafsan illa wus\'aha, laha ma kasabat wa \'alayha maktasabat. Rabbana la tu\'akhidhna in-nasina aw akhta\'na. Rabbana wala tahmil \'alayna isran kama hamaltahu \'alal-ladhina min qablik. Rabbana wala tuhammilna ma la taqata lana bih. Wa\'fu \'anna waghfir lana warhamna. Anta mawlana fansurna \'alal-qawmil-kafirin.',
    translation: 'Kepunyaan Allah-lah segala apa yang ada di langit dan di bumi. Dan jika kamu melahirkan apa yang ada di dalam hatimu atau kamu menyembunyikannya, niscaya Allah akan membuat perhitungan dengan kamu tentang perbuatanmu itu... Rasul telah beriman kepada Al-Quran yang diturunkan kepadanya dari Tuhannya, demikian pula orang-orang yang beriman... Allah tidak membebani seseorang melainkan sesuai dengan kesanggupannya...',
    target: 1,
    count: 0,
    fadilah: 'Diberikan perlindungan dan kecukupan dari segala keburukan sepanjang hari/malam.'
  },
  {
    id: 7,
    title: 'Surah Ali Imran (Ayat 1-2)',
    arabic: 'الم ۝ اللَّهُ لَا إِلَٰهَ إِلَّا هُوَ الْحَيُّ الْقَيُّومُ',
    latin: 'Alif-laam-miim. Allahu la ilaha illa huwal-hayyul-qayyum.',
    translation: 'Alif Lam Mim. Allah, tidak ada Tuhan (yang berhak disembah) selain Dia. Yang Maha Hidup, Yang terus-menerus mengurus (makhluk-Nya).',
    target: 1,
    count: 0,
    fadilah: 'Merupakan penegasan tauhid yang agung bersumber langsung dari Al-Quran.'
  },
  {
    id: 8,
    title: 'Surah Al-Ikhlas',
    arabic: 'قُلْ هُوَ اللَّهُ أَحَدٌ ۝ اللَّهُ الصَّمَدُ ۝ لَمْ يَلِدْ وَلَمْ يُولَدْ ۝ وَلَمْ يَكُن لَّهُ كُفُوًا أَحَدٌ',
    latin: 'Qul huwallahu ahad. Allahus-samad. Lam yalid wa lam yulad. Wa lam yakul lahu kufuwan ahad.',
    translation: 'Katakanlah: Dialah Allah, Yang Maha Esa. Allah adalah Tuhan yang bergantung kepada-Nya segala sesuatu. Dia tiada beranak dan tidak pula diperanakkan, dan tidak ada seorang pun yang setara dengan Dia.',
    target: 3,
    count: 0,
    fadilah: 'Menyamai pahala membaca sepertiga isi Al-Quran jika dibaca dengan ikhlas.'
  },
  {
    id: 9,
    title: 'Surah Al-Falaq',
    arabic: 'قُلْ أَعُوذُ بِرَبِّ الْفَلَقِ ۝ مِن شَرِّ مَا خَلَقَ ۝ وَمِن شَرِّ غَاسِقٍ إِذَا وَقَبَ ۝ وَمِن شَرِّ النَّفَّاثَاتِ فِي الْعُقَدِ ۝ وَمِن شَرِّ حَاسِدٍ إِذَا حَسَدَ',
    latin: 'Qul a\'udhu bi rabbil-falaq. Min sharri ma khalaq. Wa min sharri ghasiqin idha waqab. Wa min sharri-naffathati fil-\'uqad. Wa min sharri hasidin idha hasad.',
    translation: 'Katakanlah: Aku berlindung kepada Tuhan Yang Menguasai subuh, dari kejahatan makhluk-Nya, dan dari kejahatan malam apabila telah gelap gulita, dan dari kejahatan wanita-wanita penyihir yang meniup pada buhul-buhul, dan dari kejahatan pendengki bila ia dengki.',
    target: 3,
    count: 0,
    fadilah: 'Perlindungan terbaik terhadap sihir, hasad dengki, serta makhluk jahat di malam hari.'
  },
  {
    id: 10,
    title: 'Surah An-Nas',
    arabic: 'قُلْ أَعُوذُ بِرَبِّ النَّاسِ ۝ مَلِكِ النَّاسِ ۝ إِلَٰهِ النَّاسِ ۝ مِن شَرِّ الْوَسْوَاسِ الْخَنَّاسِ ۝ الَّذِي يُوَسْوِسُ فِي صُدُورِ النَّاسِ ۝ مِنَ الْجِنَّةِ وَالنَّاسِ',
    latin: 'Qul a\'udhu bi rabbin-nas. Malikin-nas. Ilahin-nas. Min sharril-waswasil-khannas. Al-ladhi yuwaswisu fi sudurin-nas. Minal-jinnati wan-nas.',
    translation: 'Katakanlah: Aku berlindung kepada Tuhan (yang memelihara dan menguasai) manusia. Raja manusia. Sembahan manusia. Dari kejahatan (bisikan) syaitan yang biasa bersembunyi, yang membisikkan (kejahatan) ke dalam dada manusia, dari (golongan) jin dan manusia.',
    target: 3,
    count: 0,
    fadilah: 'Melindungi jiwa dari bisikan jahat syaitan dari golongan jin maupun manusia.'
  },
  {
    id: 11,
    title: 'Dzikir Keadaan Waktu (Pagi / Petang)',
    isTimeDependent: true,
    // Morning
    morningArabic: 'أَصْبَحْنَا وَأَصْبَحَ الْمُلْكُ لِلَّهِ، وَالْحَمْدُ لِلَّهِ، لَا إِلَٰهَ إِلَّا اللَّهُ وَحْدَهُ لَا شَرِيكَ لَهُ، لَهُ الْمُلْكُ وَلَهُ الْحَمْدُ وَهُوَ عَلَىٰ كُلِّ شَيْءٍ قَدِيرٌ. رَبِّ أَسْأَلُكَ خَيْرَ مَا فِي هَٰذَا الْيَوْمِ وَخَيْرَ مَا بَعْدَهُ، وَأَعُوذُ بِكَ مِنْ شَرِّ مَا فِي هَٰذَا الْيَوْمِ وَشَرِّ مَا بَعْدَهُ، رَبِّ أَعُوذُ بِكَ مِنَ الْكَسَلِ وَسُوءِ الْكِبَرِ، رَبِّ أَعُوذُ بِكَ مِنْ عَذَابٍ فِي النَّارِ وَعَذَابٍ فِي الْقَبْرِ',
    morningLatin: 'Asbahna wa asbahal-mulku lillah, wal-hamdu lillah, la ilaha illallahu wahdahu la sharika lah, lahul-mulku wa lahul-hamdu wa huwa \'ala kulli shay\'in qadir. Rabbi as\'aluka khayra ma fi hadhal-yawmi wa khayra ma ba\'dah, wa a\'udhu bika min sharri ma fi hadhal-yawmi wa sharri ma ba\'dah. Rabbi a\'udhu bika minal-kasali wa su\'il-kibar, rabbi a\'udhu bika min \'adhabin fin-nari wa \'adhabin fil-qabr.',
    morningTranslation: 'Kami memasuki waktu pagi dan segala kekuasaan milik Allah, segala puji bagi Allah. Tiada Tuhan selain Allah Yang Maha Esa, tiada sekutu bagi-Nya. Bagi-Nya kekuasaan dan pujian, dan Dia Maha Kuasa atas segala sesuatu. Ya Tuhanku, aku mohon kebaikan hari ini dan kebaikan sesudahnya, dan berlindung dari kejahatan hari ini dan kejahatan sesudahnya. Ya Tuhanku, aku berlindung dari malas dan keburukan masa tua, serta azab neraka dan kubur.',
    // Evening
    eveningArabic: 'أَمْسَيْنَا وَأَمْسَى الْمُلْكُ لِلَّهِ، وَالْحَمْدُ لِلَّهِ، لَا إِلَٰهَ إِلَّا اللَّهُ وَحْدَهُ لَا شَرِيكَ لَهُ، لَهُ الْمُلْكُ وَلَهُ الْحَمْدُ وَهُوَ عَلَىٰ كُلِّ شَيْءٍ قَدِيرٌ. رَبِّ أَسْأَلُكَ خَيْرَ مَا فِي هَٰذِهِ اللَّيْلَةِ وَخَيْرَ مَا بَعْدَهَا، وَأَعُوذُ بِكَ مِنْ شَرِّ مَا فِي هَٰذِهِ اللَّيْلَةِ وَشَرِّ مَا بَعْدَهَا، رَبِّ أَعُوذُ بِكَ مِنَ الْكَسَلِ وَسُوءِ الْكِبَرِ، رَبِّ أَعُوذُ بِكَ مِنْ عَذَابٍ فِي النَّارِ وَعَذَابٍ فِي الْقَبْرِ',
    eveningLatin: 'Amsayna wa amsal-mulku lillah, wal-hamdu lillah, la ilaha illallahu wahdahu la sharika lah, lahul-mulku wa lahul-hamdu wa huwa \'ala kulli shay\'in qadir. Rabbi as\'aluka khayra ma fi hadhihil-laylati wa khayra ma ba\'daha, wa a\'udhu bika min sharri ma fi hadhihil-laylati wa sharri ma ba\'daha. Rabbi a\'udhu bika minal-kasali wa su\'il-kibar, rabbi a\'udhu bika min \'adhabin fin-nari wa \'adhabin fil-qabr.',
    eveningTranslation: 'Kami memasuki waktu petang dan segala kekuasaan milik Allah, segala puji bagi Allah. Tiada Tuhan selain Allah Yang Maha Esa, tiada sekutu bagi-Nya. Bagi-Nya kekuasaan dan pujian, dan Dia Maha Kuasa atas segala sesuatu. Ya Tuhanku, aku mohon kebaikan malam ini dan kebaikan sesudahnya, dan berlindung dari kejahatan malam ini dan kejahatan sesudahnya. Ya Tuhanku, aku berlindung dari malas dan keburukan masa tua, serta azab neraka dan kubur.',
    target: 1,
    count: 0,
    fadilah: 'Menyerahkan perlindungan diri dan memohon kebaikan hari/malam ini kepada Allah.'
  },
  {
    id: 12,
    title: 'Dzikir Pengakuan Nikmat (Pagi / Petang)',
    isTimeDependent: true,
    // Morning
    morningArabic: 'اللَّهُمَّ مَا أَصْبَحَ بِي مِنْ نِعْمَةٍ أَوْ بِأَحَدٍ مِنْ خَلْقِكَ، فَمِنْكَ وَحْدَكَ لَا شَرِيكَ لَكَ، فَلَكَ الْحَمْدُ وَلَكَ الشُّكْرُ',
    morningLatin: 'Allahumma ma ashbaha bi min ni\'matin aw bi-ahadim min khalqika faminka wahdaka la sharika lak, falakal-hamdu wa lakash-shukr.',
    morningTranslation: 'Ya Allah, nikmat yang kuterima di pagi ini atau yang diterima oleh salah seorang dari makhluk-Mu adalah dari-Mu semata, tiada sekutu bagi-Mu. Bagi-Mu segala puji dan syukur.',
    // Evening
    eveningArabic: 'اللَّهُمَّ مَا أَمْسَىٰ بِي مِنْ نِعْمَةٍ أَوْ بِأَحَدٍ مِنْ خَلْقِكَ، فَمِنْكَ وَحْدَكَ لَا شَرِيكَ لَكَ، فَلَكَ الْحَمْدُ وَلَكَ الشُّكْرُ',
    eveningLatin: 'Allahumma ma amsa bi min ni\'matin aw bi-ahadim min khalqika faminka wahdaka la sharika lak, falakal-hamdu wa lakash-shukr.',
    eveningTranslation: 'Ya Allah, nikmat yang kuterima di petang ini atau yang diterima oleh salah seorang dari makhluk-Mu adalah dari-Mu semata, tiada sekutu bagi-Mu. Bagi-Mu segala puji dan syukur.',
    target: 1,
    count: 0,
    fadilah: 'Barangsiapa membacanya di pagi/petang hari, ia telah menunaikan rasa syukurnya pada hari/malam itu.'
  },
  {
    id: 13,
    title: 'Dzikir Kesehatan & Keselamatan Panca Indra',
    arabic: 'اللَّهُمَّ عَافِنِي فِي بَدَنِي، اللَّهُمَّ عَافِنِي فِي سَمْعِي، اللَّهُمَّ عَافِنِي فِي بَصَرِي، لَا إِلَٰهَ إِلَّا أَنْتَ',
    latin: 'Allahumma \'afini fi badani, Allahumma \'afini fi sam\'i, Allahumma \'afini fi basari, la ilaha illa ant.',
    translation: 'Ya Allah, sehatkanlah badanku. Ya Allah, sehatkanlah pendengaranku. Ya Allah, sehatkanlah penglihatanku. Tiada Tuhan selain Engkau.',
    target: 3,
    count: 0,
    fadilah: 'Memohon kesehatan jasmani serta perlindungan panca indra agar selalu digunakan untuk ketaatan.'
  },
  {
    id: 14,
    title: 'Perlindungan dari Kekafiran, Kefakiran & Azab Kubur',
    arabic: 'اللَّهُمَّ إِنِّي أَعُوذُ بِكَ مِنَ الْكُفْرِ وَالْفَقْرِ، اللَّهُمَّ إِنِّي أَعُوذُ بِكَ مِنْ عَذَابِ الْقَبْرِ، لَا إِلَٰهَ إِلَّا أَنْتَ',
    latin: 'Allahumma inni a\'udhu bika minal-kufri wal-faqri, Allahumma inni a\'udhu bika min \'adhabil-qabri, la ilaha illa ant.',
    translation: 'Ya Allah, aku berlindung kepada-Mu dari kekafiran dan kefakiran. Ya Allah, aku berlindung kepada-Mu dari azab kubur. Tiada Tuhan selain Engkau.',
    target: 3,
    count: 0,
    fadilah: 'Menjaga keistiqomahan iman, menghindari kesulitan hidup, serta memohon keselamatan di alam kubur.'
  },
  {
    id: 15,
    title: 'Dzikir Hasbiyallahu (Kecukupan Hidup)',
    arabic: 'حَسْبِيَ اللَّهُ لَا إِلَٰهَ إِلَّا هُوَ ۖ عَلَيْهِ تَوَكَّلْتُ ۖ وَهُوَ رَبُّ الْعَرْشِ الْعَظِيمِ',
    latin: 'Hasbiyallahu la ilaha illa huwa \'alayhi tawakkaltu wa huwa rabbul-\'arshil-\'azhim.',
    translation: 'Cukuplah Allah bagiku; tidak ada Tuhan selain Dia. Hanya kepada-Nya aku bertawakal, dan Dia adalah Tuhan yang memiliki Arasy yang agung.',
    target: 7,
    count: 0,
    fadilah: 'Barangsiapa membacanya 7 kali di pagi/petang hari, Allah akan mencukupi segala kepentingan dunia dan akhiratnya.'
  },
  {
    id: 16,
    title: 'Dzikir Perlindungan Bahaya (Bismillahilladzi...)',
    arabic: 'بِسْمِ اللَّهِ الَّذِي لَا يَضُرُّ مَعَ اسْمِهِ شَيْءٌ فِي الْأَرْضِ وَلَا فِي السَّمَاءِ وَهُوَ السَّمِيعُ الْعَلِيمُ',
    latin: 'Bismillahil-ladhi la yadhurru ma\'as-mihi shay\'un fil-ardhi wa la fis-samai wa huwas-sami\'ul-\'alim.',
    translation: 'Dengan nama Allah yang bila disebut nama-Nya, segala sesuatu di bumi dan langit tidak akan berbahaya, dan Dia Maha Mendengar lagi Maha Mengetahui.',
    target: 3,
    count: 0,
    fadilah: 'Tidak ada suatu musibah atau bahaya racun dan binatang yang dapat mencelakakannya sepanjang hari/malam.'
  },
  {
    id: 17,
    title: 'Dzikir Keridhaan (Tauhid Pengakuan)',
    arabic: 'رَضِيتُ بِاللَّهِ رَبًّا، وَبِالْإِسْلَامِ دِينًا، وَبِمُحَمَّدٍ نَبِيًّا وَرَسُولًا',
    latin: 'Radhitu billahi rabba, wa bil-islami dina, wa bi-muhammadin nabiyya wa rasula.',
    translation: 'Aku ridha Allah sebagai Tuhanku, Islam sebagai agamaku, dan Muhammad sebagai Nabi dan Rasul-Ku.',
    target: 3,
    count: 0,
    fadilah: 'Allah menjamin akan memberikan keridhaan-Nya kepada hamba tersebut kelak di hari kiamat.'
  },
  {
    id: 18,
    title: 'Dzikir Tasbih Pujian Kreator',
    arabic: 'سُبْحَانَ اللَّهِ وَبِحَمْدِهِ: عَدَدَ خَلْقِهِ، وَرِضَا نَفْسِهِ، وَزِنَةَ عَرْشِهِ، وَمِدَادَ كَلِمَاتِهِ',
    latin: 'Subhanallahi wa bihamdihi: \'adada khalqih, wa ridha nafsih, wa zinata \'arshih, wa midada kalimatih.',
    translation: 'Maha Suci Allah dan segala puji bagi-Nya sebanyak jumlah makhluk-Nya, seridha diri-Nya, seberat timbangan Arasy-Nya, dan sebanyak tinta kalimat-kalimat-Nya.',
    target: 3,
    count: 0,
    fadilah: 'Memiliki timbangan pahala dzikir yang amat agung, melampaui dzikir berulang-ulang lainnya.'
  },
  {
    id: 19,
    title: 'Dzikir Istighosah (Ya Hayyu Ya Qayyum...)',
    arabic: 'يَا حَيُّ يَا قَيُّومُ بِرَحْمَتِكَ أَسْتَغِيثُ، أَصْلِحْ لِي شَأْنِي كُلَّهُ وَلَا تَكِلْنِي إِلَىٰ نَفْسِي طَرْفَةَ عَيْنٍ',
    latin: 'Ya hayyu ya qayyumu bi-rahmatika astaghith, aslih li sha\'ni kullahu wa la takilni ila nafsi tarfata \'ayn.',
    translation: 'Wahai Yang Maha Hidup, Yang terus-menerus mengurus makhluk-Nya, dengan rahmat-Mu aku memohon pertolongan. Perbaikilah seluruh urusanku dan janganlah Engkau serahkan aku kepada diriku sendiri walau sekejap mata.',
    target: 1,
    count: 0,
    fadilah: 'Memohon perbaikan urusan hidup secara menyeluruh dan perlindungan dari kesombongan diri.'
  },
  {
    id: 20,
    title: 'Sayyidul Istighfar (Rajanya Taubat)',
    arabic: 'اللَّهُمَّ أَنْتَ رَبِّي لَا إِلَٰهَ إِلَّا أَنْتَ، خَلَقْتَنِي وَأَنَا عَبْدُكَ، وَأَنَا عَلَىٰ عَهْدِكَ وَوَعْدِكَ مَا اسْتَطَعْتُ، أَعُوذُ بِكَ مِنْ شَرِّ مَا صَنَعْتُ، أَبُوءُ لَكَ بِنِعْمَتِكَ عَلَيَّ، وَأَبُوءُ بِذَنْبِي فَاغْفِرْ لِي فَإِنَّهُ لَا يَغْفِرُ الذُّنُوبَ إِلَّا أَنْتَ',
    latin: 'Allahumma anta rabbi la ilaha illa ant, khalaqtani wa ana \'abduk, wa ana \'ala \'ahdika wa wa\'dika mastata\'t. A\'udhu bika min sharri ma sana\'t, abu\'u laka bi-ni\'matika \'alayya, wa abu\'u bi-dhanbi faghfir li, fa-innahu la yaghfirudh-dhunuba illa ant.',
    translation: 'Ya Allah, Engkau adalah Tuhanku, tiada Tuhan selain Engkau. Engkau telah menciptakanku dan aku adalah hamba-Mu. Aku menetapi perjanjian-Mu dan janji-Mu sesuai kemampuanku. Aku berlindung kepada-Mu dari keburukan perbuatanku. Aku mengakui nikmat-Mu kepadaku dan mengakui dosaku, maka ampunilah aku. Karena sesungguhnya tiada yang mengampuni dosa selain Engkau.',
    target: 1,
    count: 0,
    fadilah: 'Barangsiapa membacanya di siang/malam hari dengan penuh keimanan lalu wafat, maka ia termasuk ahli syurga.'
  },
  {
    id: 21,
    title: 'Istighfar Taubat & Pembersih Hati',
    arabic: 'أَسْتَغْفِرُ اللَّهَ وَأَتُوبُ إِلَيْهِ',
    latin: 'Astaghfirullaha wa atubu ilayh.',
    translation: 'Aku memohon ampunan kepada Allah dan aku bertaubat kepada-Nya.',
    target: 100,
    count: 0,
    fadilah: 'Menghilangkan kesempitan hati, melapangkan urusan rezeki, dan menghapuskan noda hitam dosa.'
  },
  {
    id: 22,
    title: 'Shalawat atas Nabi Muhammad SAW',
    arabic: 'اللَّهُمَّ صَلِّ عَلَىٰ مُحَمَّدٍ وَعَلَىٰ آلِ مُحَمَّدٍ',
    latin: 'Allahumma shalli \'ala Muhammadin wa \'ala ali Muhammad.',
    translation: 'Ya Allah, limpahkanlah rahmat kepada Nabi Muhammad dan kepada keluarga Nabi Muhammad.',
    target: 10,
    count: 0,
    fadilah: 'Mendapatkan syafaat istimewa dari Rasulullah SAW di yaumil akhir kelak.'
  }
]);

// ── Audio Feedback Generator ──────────────────────────────────────────────────
const playBeep = () => {
  try {
    const ctx = new (window.AudioContext || window.webkitAudioContext)();
    const osc = ctx.createOscillator();
    const gain = ctx.createGain();
    osc.connect(gain);
    gain.connect(ctx.destination);
    osc.frequency.setValueAtTime(850, ctx.currentTime);
    gain.gain.setValueAtTime(0.04, ctx.currentTime);
    osc.start();
    gain.gain.exponentialRampToValueAtTime(0.00001, ctx.currentTime + 0.08);
    osc.stop(ctx.currentTime + 0.08);
  } catch (e) {}
};

const playSuccessBeep = () => {
  try {
    const ctx = new (window.AudioContext || window.webkitAudioContext)();
    const osc = ctx.createOscillator();
    const gain = ctx.createGain();
    osc.connect(gain);
    gain.connect(ctx.destination);
    osc.frequency.setValueAtTime(1150, ctx.currentTime);
    gain.gain.setValueAtTime(0.06, ctx.currentTime);
    osc.start();
    gain.gain.exponentialRampToValueAtTime(0.00001, ctx.currentTime + 0.22);
    osc.stop(ctx.currentTime + 0.22);
  } catch (e) {}
};

// ── Interactive Logic ─────────────────────────────────────────────────────────
const incrementDhikr = (dhikr) => {
  if (dhikr.count < dhikr.target) {
    dhikr.count++;
    if (dhikr.count === dhikr.target) {
      playSuccessBeep();
    } else {
      playBeep();
    }
  }
};

const resetDhikr = (dhikr) => {
  dhikr.count = 0;
  playBeep();
};

const resetAll = () => {
  dhikrs.value.forEach(d => d.count = 0);
  playSuccessBeep();
};

const completeAll = () => {
  dhikrs.value.forEach(d => d.count = d.target);
  playSuccessBeep();
};

// ── Filters & Search ──────────────────────────────────────────────────────────
const filteredDhikrs = computed(() => {
  let list = dhikrs.value;
  
  // Search filter
  const q = searchQuery.value.toLowerCase().trim();
  if (q) {
    list = list.filter(d => 
      d.title.toLowerCase().includes(q) || 
      d.latin.toLowerCase().includes(q) || 
      d.translation.toLowerCase().includes(q)
    );
  }

  return list;
});

// ── Dashboard URL based on role ─────────────────────────────────────────────
const dashboardUrl = computed(() => {
  const slug = props.auth?.role?.slug || props.auth?.user?.role?.slug || '';
  return slug ? `/${slug}/dashboard` : '/';
});
</script>

<template>
  <Head title="Al-Ma'tsurat Wazifah Sugro" />

  <div class="min-h-screen bg-emerald-50/60 relative pb-20">
    <!-- Background Elements -->
    <div class="fixed inset-0 pointer-events-none overflow-hidden">
      <div class="absolute -top-32 -right-32 w-96 h-96 bg-emerald-100/50 rounded-full blur-3xl"></div>
      <div class="absolute -bottom-32 -left-32 w-96 h-96 bg-amber-50/60 rounded-full blur-3xl"></div>
    </div>

    <!-- Hidden Audio Player for Streamed Recitation -->
    <audio 
      ref="audioPlayerRef" 
      :src="audioUrl" 
      @ended="handleAudioEnded" 
      class="hidden"
    ></audio>

    <!-- Navigation Header -->
    <nav class="sticky top-0 z-30 bg-white border-b border-emerald-100 shadow-sm">
      <div class="max-w-4xl mx-auto px-4">
        <div class="flex justify-between h-14 sm:h-16">
          
          <div class="flex items-center space-x-2.5">
            <div class="bg-emerald-700 text-amber-300 p-1.5 rounded-xl">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
              </svg>
            </div>
            <div class="flex flex-col">
              <span class="text-sm sm:text-base font-bold text-emerald-900 leading-tight">Al-Ma'tsurat Sugro</span>
              <span class="text-xs text-emerald-500 font-medium">Dzikir Pagi & Petang Lengkap</span>
            </div>
          </div>

          <div class="flex items-center space-x-2">
            <a
              href="/quran"
              class="flex items-center space-x-1 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-semibold rounded-lg px-3 py-2 text-xs transition-colors shadow-sm"
            >
              <span>Al-Quran</span>
            </a>
            <a
              v-if="auth"
              :href="dashboardUrl"
              class="flex items-center space-x-1 bg-emerald-700 hover:bg-emerald-800 text-white font-semibold rounded-lg px-3 py-2 text-xs transition-colors shadow-sm"
            >
              <span>Dashboard</span>
            </a>
            <a
              v-else
              href="/login"
              class="flex items-center space-x-1 bg-emerald-700 hover:bg-emerald-800 text-white font-semibold rounded-lg px-3 py-2 text-xs transition-colors shadow-sm"
            >
              <span>Login</span>
            </a>
          </div>

        </div>
      </div>
    </nav>

    <!-- Main Container -->
    <main class="max-w-3xl mx-auto px-4 mt-6 relative z-10">
      
      <!-- Hero Banner -->
      <div class="bg-gradient-to-r from-emerald-800 to-emerald-950 text-white rounded-2xl p-6 sm:p-7 shadow-xl mb-6 border border-emerald-700 relative overflow-hidden">
        <div class="absolute inset-0 opacity-[0.03]">
          <div class="absolute top-4 right-8 text-8xl font-arabic select-none leading-none">وظيفة</div>
        </div>
        <div class="relative">
          <p class="text-amber-300 text-xs font-bold uppercase tracking-widest mb-1">Dzikir Harian Pagi & Petang</p>
          <h1 class="text-2xl sm:text-3xl font-bold mb-1">Wazifah Sugro Lengkap</h1>
          <p class="text-emerald-200 text-xs sm:text-sm max-w-xl leading-relaxed">
            Daftar lengkap wazifah sugro Imam Hasan al-Banna dengan transliterasi Latin per-ayat, counter tasbih sentuh, dan pemutar audio murottal.
          </p>
        </div>
      </div>

      <!-- Audio player widget + Mode selector -->
      <div class="bg-white border border-emerald-100 rounded-2xl p-4 shadow-sm space-y-4 mb-6">
        
        <!-- Top: Audio & Time selector -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
          
          <!-- Morning/Evening toggle -->
          <div class="flex bg-gray-100 p-0.5 rounded-lg border border-gray-250/20 w-fit">
            <button
              @click="activeTimeMode = 'pagi'"
              :class="activeTimeMode === 'pagi' ? 'bg-amber-400 text-emerald-950 font-bold shadow-sm' : 'text-gray-500 hover:text-emerald-950'"
              class="px-4 py-2 rounded-md text-xs font-semibold transition-all flex items-center gap-1"
            >
              <span>☀️</span> Dzikir Pagi
            </button>
            <button
              @click="activeTimeMode = 'petang'"
              :class="activeTimeMode === 'petang' ? 'bg-emerald-800 text-white font-bold shadow-sm' : 'text-gray-500 hover:text-emerald-955'"
              class="px-4 py-2 rounded-md text-xs font-semibold transition-all flex items-center gap-1"
            >
              <span>🌙</span> Dzikir Petang
            </button>
          </div>

          <!-- Murottal Audio Player Widget -->
          <div class="flex items-center gap-3 bg-emerald-50/70 border border-emerald-100 rounded-2xl px-4 py-2 flex-1 md:max-w-sm justify-between">
            <div class="flex items-center gap-2">
              <span class="text-lg">🎧</span>
              <div class="flex flex-col">
                <span class="text-[10px] font-bold text-emerald-800 uppercase tracking-wider">Murottal Al-Ma'tsurat</span>
                <span class="text-xs text-emerald-950 font-medium">
                  {{ activeTimeMode === 'pagi' ? 'Versi Dzikir Pagi' : 'Versi Dzikir Petang' }}
                </span>
              </div>
            </div>
            
            <button
              @click="togglePlayRecitation"
              :class="isAudioPlaying ? 'bg-red-650 hover:bg-red-700 text-white' : 'bg-emerald-700 hover:bg-emerald-800 text-white'"
              class="px-3.5 py-1.5 rounded-xl font-bold text-[10px] shadow-sm transition-all flex items-center gap-1"
            >
              <span>{{ isAudioPlaying ? 'Pause' : 'Putar Audio' }}</span>
            </button>
          </div>

        </div>

        <!-- Search Bar and Action Controls -->
        <div class="flex flex-col sm:flex-row items-center gap-3 pt-3.5 border-t border-emerald-50">
          <div class="relative w-full sm:flex-1">
            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-emerald-400">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
            </span>
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Cari kata/ayat dzikir..."
              class="w-full pl-9 pr-4 py-2 border border-emerald-100 rounded-xl text-xs outline-none focus:ring-1 focus:ring-emerald-500 shadow-sm"
            />
          </div>
          <div class="flex items-center gap-2 w-full sm:w-auto justify-end">
            <button
              @click="resetAll"
              class="px-3.5 py-2 text-xs bg-red-50 hover:bg-red-100 text-red-700 border border-red-150 font-bold rounded-xl shadow-sm transition-all"
            >
              Reset Hitungan
            </button>
            <button
              @click="completeAll"
              class="px-3.5 py-2 text-xs bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-250 font-bold rounded-xl shadow-sm transition-all"
            >
              Selesai Semua
            </button>
          </div>
        </div>

      </div>

      <!-- Interactive Instruction Helper -->
      <div class="bg-amber-50/70 border border-amber-200 rounded-2xl p-3.5 text-xs text-amber-900 flex items-start gap-2.5 mb-5 shadow-sm">
        <span>💡</span>
        <p class="leading-relaxed">
          <strong>Tip Kenyamanan:</strong> Anda dapat **mengetuk area mana saja di dalam kotak kartu dzikir** untuk menambah hitungan counter tasbih Anda dengan mudah.
        </p>
      </div>

      <!-- Dhikr Cards List -->
      <div class="space-y-4">
        <div
          v-for="(dhikr, index) in filteredDhikrs"
          :key="dhikr.id"
          @click="incrementDhikr(dhikr)"
          class="bg-white border rounded-3xl p-5 sm:p-6 shadow-sm transition-all relative overflow-hidden cursor-pointer select-none active:scale-[0.99] duration-150"
          :class="dhikr.count === dhikr.target 
            ? 'border-emerald-500 shadow-emerald-500/5 ring-1 ring-emerald-500/25 bg-emerald-50/10' 
            : 'border-emerald-100 hover:border-emerald-200 hover:shadow-md'"
        >
          <!-- Corner checkmark for completed item -->
          <div 
            v-if="dhikr.count === dhikr.target"
            class="absolute top-0 right-0 w-12 h-12 bg-emerald-500 text-white rounded-bl-3xl flex items-center justify-center pt-2 pr-2"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
            </svg>
          </div>

          <!-- Header info -->
          <div class="flex items-center gap-2.5 mb-3.5">
            <span class="bg-emerald-100 text-emerald-700 text-xs font-bold rounded-lg w-7 h-7 flex items-center justify-center flex-shrink-0">
              {{ index + 1 }}
            </span>
            <div>
              <h3 class="text-sm font-bold text-emerald-950">{{ dhikr.title }}</h3>
              <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider">
                Dibaca: {{ dhikr.target }} kali
              </p>
            </div>
          </div>

          <!-- Arabic Script -->
          <div class="py-4 select-text">
            <p 
              class="font-arabic text-right text-2xl sm:text-3xl leading-[2.3] text-emerald-950" 
              dir="rtl"
            >
              {{ dhikr.isTimeDependent 
                ? (activeTimeMode === 'pagi' ? dhikr.morningArabic : dhikr.eveningArabic) 
                : dhikr.arabic 
              }}
            </p>
          </div>

          <!-- Transliteration Latin -->
          <div class="border-t border-emerald-50 pt-4 space-y-3 select-text">
            <p class="text-xs text-emerald-800/90 font-medium italic leading-relaxed">
              {{ dhikr.isTimeDependent 
                ? (activeTimeMode === 'pagi' ? dhikr.morningLatin : dhikr.eveningLatin) 
                : dhikr.latin 
              }}
            </p>

            <!-- Translation -->
            <p class="text-xs text-gray-600 leading-relaxed">
              <span class="text-[10px] font-bold text-emerald-500 uppercase mr-1">Arti:</span>
              {{ dhikr.isTimeDependent 
                ? (activeTimeMode === 'pagi' ? dhikr.morningTranslation : dhikr.eveningTranslation) 
                : dhikr.translation 
              }}
            </p>

            <!-- Fadilah / Keutamaan -->
            <div class="bg-[#F9FAF9] border border-emerald-50/60 p-3 rounded-2xl text-[10px] text-gray-500 italic mt-2.5">
              <span class="font-bold text-amber-600 block mb-0.5">Keutamaan:</span>
              {{ dhikr.fadilah }}
            </div>
          </div>

          <!-- Interactive Counter Progress & Buttons -->
          <div class="mt-5 pt-4 border-t border-emerald-50 flex items-center justify-between gap-4">
            
            <!-- Progress Bar -->
            <div class="flex-1">
              <div class="flex justify-between text-[10px] text-gray-400 font-bold mb-1">
                <span>Progress Bacaan</span>
                <span>{{ dhikr.count }} / {{ dhikr.target }}</span>
              </div>
              <div class="w-full bg-gray-100 rounded-full h-1.5 overflow-hidden">
                <div 
                  class="bg-emerald-600 h-1.5 transition-all duration-300"
                  :style="{ width: (dhikr.count / dhikr.target * 100) + '%' }"
                ></div>
              </div>
            </div>

            <!-- Action buttons -->
            <div class="flex items-center gap-2">
              <button
                v-if="dhikr.count > 0"
                @click.stop="resetDhikr(dhikr)"
                class="p-2 border border-red-200 text-red-500 hover:bg-red-50 rounded-xl transition-colors shadow-sm"
                title="Reset hitungan dzikir ini"
              >
                <svg xmlns="http://www.w3.org/2500/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 1121.21 6H16" />
                </svg>
              </button>

              <button
                @click.stop="incrementDhikr(dhikr)"
                :disabled="dhikr.count === dhikr.target"
                class="px-4 py-2 font-bold text-xs rounded-xl shadow-sm border transition-all flex items-center gap-1.5"
                :class="dhikr.count === dhikr.target
                  ? 'bg-emerald-50 border-emerald-250 text-emerald-800 cursor-not-allowed shadow-none'
                  : 'bg-emerald-700 hover:bg-emerald-800 text-white border-emerald-700'"
              >
                <span>{{ dhikr.count === dhikr.target ? 'Selesai' : 'Hitung +1' }}</span>
              </button>
            </div>

          </div>

        </div>

        <p v-if="filteredDhikrs.length === 0" class="text-center text-emerald-450 text-sm py-10 font-medium">
          Dzikir tidak ditemukan. Coba ketikkan kata pencarian yang berbeda.
        </p>
      </div>

    </main>
  </div>
</template>

<style scoped>
.font-arabic {
  font-family: 'Amiri', 'Traditional Arabic', serif;
}
</style>
