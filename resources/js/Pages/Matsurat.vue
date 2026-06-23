<script setup>
import { ref, computed } from 'vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
  auth: Object,
});

// ── State ─────────────────────────────────────────────────────────────────────
const activeTimeMode  = ref('pagi');   // 'pagi' | 'petang'
const activeDhikrMode = ref('sugra');  // 'sugra' | 'kubra'
const searchQuery     = ref('');

// ── Curated Dhikr Data ────────────────────────────────────────────────────────
const dhikrs = ref([
  {
    id: 1,
    title: 'Ta\'awwudh & Surah Al-Fatihah',
    arabic: 'أَعُوذُ بِاللَّهِ مِنَ الشَّيْطَانِ الرَّجِيمِ ۝ بِسْمِ اللَّهِ الرَّحْمَٰنِ الرَّحِيمِ ۝ الْحَمْدُ لِلَّهِ رَبِّ الْعَالَمِينَ ۝ الرَّحْمَٰنِ الرَّحِيمِ ۝ مَالِكِ يَوْمِ الدِّينِ ۝ إِيَّاكَ نَعْبُدُ وَإِيَّاكَ نَسْتَعِينُ ۝ اهْدِنَا الصِّرَاطَ الْمُسْتَقِيمَ ۝ صِرَاطَ الَّذِينَ أَنْعَمْتَ عَلَيْهِمْ غَيْرِ الْمَغْضُوبِ عَلَيْهِمْ وَلَا الضَّالِّينَ',
    latin: 'A\'udhu billahi minash-shaytanir-rajim. Bismillahir-rahmanir-rahim. Al-hamdu lillahi rabbil-\'alamin. Ar-rahmanir-rahim. Maliki yawmid-din. Iyyaka na\'budu wa iyyaka nasta\'in. Ihdinas-siratal-mustaqim. Siratal-ladhina an\'amta \'alayhim ghayril-maghdubi \'alayhim walad-dallin.',
    translation: 'Aku berlindung kepada Allah dari godaan syetan yang terkutuk. Dengan nama Allah Yang Maha Pengasih, Maha Penyayang. Segala puji bagi Allah, Tuhan seluruh alam. Yang Maha Pengasih, Maha Penyayang. Pemilik hari pembalasan. Hanya kepada Engkaulah kami menyembah dan hanya kepada Engkaulah kami memohon pertolongan. Tunjukilah kami jalan yang lurus. (yaitu) jalan orang-orang yang telah Engkau beri nikmat kepadanya; bukan (jalan) mereka yang dimurkai, dan bukan (pula jalan) mereka yang sesat.',
    target: 1,
    count: 0,
    isSugra: true,
    fadilah: 'Merupakan rukun shalat, pembuka Al-Quran, dan perlindungan utama bagi mukmin.'
  },
  {
    id: 2,
    title: 'Surah Al-Baqarah (Ayat 1-5)',
    arabic: 'بِسْمِ اللَّهِ الرَّحْمَٰنِ الرَّحِيمِ ۝ الم ۝ ذَٰلِكَ الْكِتَابُ لَا رَيْبَ ۛ فِيهِ ۛ هُدًى لِّلْمُتَّقِينَ ۝ الَّذِينَ يُؤْمِنُونَ بِالْغَيْبِ وَيُقِيمُونَ الصَّلَاةَ وَمِمَّا رَزَقْنَاهُمْ يُنفِقُونَ ۝ وَالَّذِينَ يُؤْمِنُونَ bِمَا أُنزِلَ إِلَيْكَ وَمَا أُنزِلَ مِن قَبْلِكَ وَبِالْآخِرَةِ هُمْ يُوقِنُونَ ۝ أُولَٰئِكَ عَلَىٰ هُdًى مِّن رَّبِّهِمْ ۖ وَأُولَٰئِكَ هُمُ الْمُفْلِحُونَ',
    latin: 'Alif-lam-mim. Dzalikal-kitabu la rayba fih, hudan lil-muttaqin. Al-ladhina yu\'minuna bil-ghaybi wa yuqimunas-salata wa mimma razaqnahum yunfiqun. Wal-ladhina yu\'minuna bima unzila ilayka wa ma unzila min qablik, wa bil-akhirati hum yuqinun. Ula\'ika \'ala hudam mir rabbihim wa ula\'ika humul-muflihun.',
    translation: 'Alif Lam Mim. Kitab (Al-Quran) ini tidak ada keraguan padanya; petunjuk bagi mereka yang bertakwa. (yaitu) mereka yang beriman kepada yang ghaib, yang mendirikan shalat, dan menafkahkan sebagian rezeki yang Kami anugerahkan kepada mereka. Dan mereka yang beriman kepada Kitab (Al-Quran) yang telah diturunkan kepadamu dan Kitab-kitab yang telah diturunkan sebelummu, serta mereka yakin akan adanya (kehidupan) akhirat. Mereka itulah yang tetap mendapat petunjuk dari Tuhannya, dan merekalah orang-orang yang beruntung.',
    target: 1,
    count: 0,
    isSugra: true,
    fadilah: 'Melindungi rumah dari gangguan syaitan sepanjang hari.'
  },
  {
    id: 3,
    title: 'Ayat Kursi (Surah Al-Baqarah Ayat 255)',
    arabic: 'اللَّهُ لَا إِلَٰهَ إِلَّا هُوَ الْحَيُّ الْقَيُwمُ ۚ لَا تَأْخُذُهُ سِنَةٌ وَلَا نَوْمٌ ۚ لَّهُ مَا فِي السَّمَاوَاتِ وَمَا فِي الْأَرْضِ ۗ مَن ذَا الَّذِي يَشْفَعُ عِندَهُ إِلَّا بِإِذْنِهِ ۚ يَعْلَمُ مَا بَيْنَ أَيْدِيهِمْ وَمَا خَلْفَهُمْ ۖ وَلَا يُحِيطُونَ بِشَيْءٍ مِّنْ عِلْمِهِ إِلَّا بِمَا شَاءَ ۚ وَسِعَ كُرْسِيُّهُ السَّمَاوَاتِ وَالْأَرْضَ ۖ وَلَا يَئُودُهُ حِفْظُهُمَا ۚ وَهُوَ الْعَلِيُّ الْعَظِيمُ',
    latin: 'Allahu la ilaha illa huwal-hayyul-qayyum, la ta\'khudhuhu sinatuw-wa la nawm, lahu ma fis-samawati wa ma fil-ardh, man dhal-ladhi yashfa\'u \'indahu illa bi-idhnih, ya\'lamu ma bayna aydihim wa ma khalfahum, wa la yuhituna bi-shay\'im min \'ilmihi illa bima sha\', wasi\'a kursiyyuhus-samawati wal-ardh, wa la ya\'uduhu hifzhuhuma, wa huwal-\'aliyyul-\'azhim.',
    translation: 'Allah, tidak ada Tuhan (yang berhak disembah) melainkan Dia Yang Hidup kekal lagi terus-menerus mengurus (makhluk-Nya); tidak mengantuk dan tidak tidur. Kepunyaan-Nya apa yang di langit dan di bumi. Tiada yang dapat memberi syafa\'at di sisi Allah tanpa izin-Nya? Allah mengetahui apa-apa yang di hadapan mereka dan di belakang mereka, dan mereka tidak mengetahui apa-apa dari ilmu Allah melainkan apa yang dikehendaki-Nya. Kursi Allah meliputi langit dan bumi. Dan Allah tidak merasa berat memelihara keduanya, dan Allah Maha Tinggi lagi Maha Besar.',
    target: 1,
    count: 0,
    isSugra: true,
    fadilah: 'Sentiasa berada dalam pemeliharaan Allah dan dijauhkan dari gangguan syaitan.'
  },
  {
    id: 4,
    title: 'Surah Al-Baqarah (Ayat 284-286)',
    arabic: 'لِّلَّهِ مَا فِي السَّمَاوَاتِ وَمَا فِي الْأَرْضِ ۗ وَإِن تُبْدُوا مَا فِي أَنفُسِكُمْ أَوْ تُخْفُوهُ يُحَاسِبْكُم بِهِ اللَّهُ ۖ فَيَغْفِرُ لِمَن يَشَاءُ وَيُعَذِّبُ مَن يَشَاءُ ۗ وَاللَّهُ عَلَىٰ كُلِّ شَيْءٍ قَدِيرٌ ۝ آمَنَ الرَّسُولُ بِمَا أُنزِلَ إِلَيْهِ مِن رَّبِّهِ وَالْمُؤْمِنُونَ ۚ كُلٌّ آمَنَ بِاللَّهِ وَمَلَائِكَتِهِ وَكُتُبِهِ وَرُسُلِهِ لَا نُفَرِّقُ بَيْنَ أَحَدٍ مِّن رُّسُلِهِ ۚ وَقَالُوا سَمِعْنَا وَأَطَعْنَا ۖ غُفْرَانَكَ رَبَّنَا وَإِلَيْكَ الْمَصِيرُ ۝ لَا يُكَلِّفُ اللَّهُ نَفْسًا إِلَّا وُسْعَهَا ۚ لَهَا مَا كَسَبَتْ وَعَلَيْهَا مَا اكْتَسَبَتْ ۗ رَبَّنَا لَا تُؤَاخِذْنَا إِن نَّسِينَا أَوْ أَخْطَأْنَا ۚ رَبَّنَا وَلَا تَحْمِلْ عَلَيْنَا إِصْرًا كَمَا حَمَلْتَهُ عَلَى الَّذِينَ مِن قَبْلِكَ ۚ رَبَّنَا وَلَا تُحَمِّلْنَا مَا لَا طَاقَةَ لَنَا بِهِ ۖ وَاعْفُ عَنَّا وَاغْفِرْ لَنَا وَارْحَمْنَا ۚ أَنتَ مَوْلَانَا فَانصُرْنَا عَلَى الْقَوْمِ الْكَافِرِينَ',
    latin: 'Lillahi ma fis-samawati wa ma fil-ardh. Wa in tubdu ma fi anfusikum aw tukhfuhu yuhasibkum bihilla. Fa-yaghfiru limay-yasha\'u wa yu\'adhibu may-yasha\', wallahu \'ala kulli shay\'in qadir. Amanar-rasulu bima unzila ilayhi mir-rabbihi wal-mu\'minun. Kullun amana billahi wa mala\'ikatihi wa kutubihi wa rusulih, la nufarriqu bayna ahadim-mir-rusulih. Wa qalu sami\'na wa ata\'na ghufranaka rabbana wa ilaykal-masir. La yukallifullahu nafsan illa wus\'aha, laha ma kasabat wa \'alayha maktasabat. Rabbana la tu\'akhidhna in-nasina aw akhta\'na. Rabbana wala tahmil \'alayna isran kama hamaltahu \'alal-ladhina min qablik. Rabbana wala tuhammilna ma la taqata lana bih. Wa\'fu \'anna waghfir lana warhamna. Anta mawlana fansurna \'alal-qawmil-kafirin.',
    translation: 'Kepunyaan Allah-lah segala apa yang ada di langit dan di bumi. Dan jika kamu melahirkan apa yang ada di dalam hatimu atau kamu menyembunyikannya, niscaya Allah akan membuat perhitungan dengan kamu tentang perbuatanmu itu... Allah tidak membebani seseorang melainkan sesuai dengan kesanggupannya. Ia mendapat pahala (dari kebaikan) yang diusahakannya dan ia mendapat siksa (dari kejahatan) yang dikerjakannya...',
    target: 1,
    count: 0,
    isSugra: true,
    fadilah: 'Diberikan kecukupan dalam segala hal dan dilindungi dari mara bahaya di malam hari.'
  },
  {
    id: 5,
    title: 'Surah Al-Ikhlas',
    arabic: 'قُلْ هُوَ اللَّهُ أَحَدٌ ۝ اللَّهُ الصَّمَدُ ۝ لَمْ يَلِدْ وَلَمْ يُولَدْ ۝ وَلَمْ يَكُن لَّهُ كُفُوًا أَحَدٌ',
    latin: 'Qul huwallahu ahad. Allahus-samad. Lam yalid wa lam yulad. Wa lam yakul lahu kufuwan ahad.',
    translation: 'Katakanlah: Dialah Allah, Yang Maha Esa. Allah adalah Tuhan yang bergantung kepada-Nya segala sesuatu. Dia tiada beranak dan tidak pula diperanakkan, dan tidak ada seorang pun yang setara dengan Dia.',
    target: 3,
    count: 0,
    isSugra: true,
    fadilah: 'Membacanya 3 kali menyamai pahala membaca seluruh isi Al-Quran.'
  },
  {
    id: 6,
    title: 'Surah Al-Falaq',
    arabic: 'قُلْ أَعُوذُ بِرَبِّ الْفَلَقِ ۝ مِن شَرِّ مَا خَلَقَ ۝ وَمِن شَرِّ غَاسِقٍ إِذَا وَقَبَ ۝ وَمِن شَرِّ النَّفَّاثَاتِ فِي الْعُقَدِ ۝ وَمِن شَرِّ حَاسِدٍ إِذَا حَسَدَ',
    latin: 'Qul a\'udhu bi rabbil-falaq. Min sharri ma khalaq. Wa min sharri ghasiqin idha waqab. Wa min sharri-naffathati fil-\'uqad. Wa min sharri hasidin idha hasad.',
    translation: 'Katakanlah: Aku berlindung kepada Tuhan Yang Menguasai subuh, dari kejahatan makhluk-Nya, dan dari kejahatan malam apabila telah gelap gulita, dan dari kejahatan wanita-wanita penyihir yang meniup pada buhul-buhul, dan dari kejahatan pendengki bila ia dengki.',
    target: 3,
    count: 0,
    isSugra: true,
    fadilah: 'Melindungi diri dari bahaya hasad dengki, ilmu hitam, dan makhluk malam.'
  },
  {
    id: 7,
    title: 'Surah An-Nas',
    arabic: 'قُلْ أَعُوذُ بِرَبِّ النَّاسِ ۝ مَلِكِ النَّاسِ ۝ إِلَٰهِ النَّاسِ ۝ مِن شَرِّ الْوَسْوَاسِ الْخَنَّاسِ ۝ الَّذِي يُوَسْوِسُ فِي صُدُورِ النَّاسِ ۝ مِنَ الْجِنَّةِ وَالنَّاسِ',
    latin: 'Qul a\'udhu bi rabbin-nas. Malikin-nas. Ilahin-nas. Min sharril-waswasil-khannas. Al-ladhi yuwaswisu fi sudurin-nas. Minal-jinnati wan-nas.',
    translation: 'Katakanlah: Aku berlindung kepada Tuhan (yang memelihara dan menguasai) manusia. Raja manusia. Sembahan manusia. Dari kejahatan (bisikan) syaitan yang biasa bersembunyi, yang membisikkan (kejahatan) ke dalam dada manusia, dari (golongan) jin dan manusia.',
    target: 3,
    count: 0,
    isSugra: true,
    fadilah: 'Melindungi hati dari bisikan jahat bisikan syaitan jin dan manusia.'
  },
  {
    id: 8,
    title: 'Dzikir Waktu (Pagi / Petang)',
    isTimeDependent: true,
    // Morning
    morningArabic: 'أَصْبَحْنَا وَأَصْبَحَ الْمُلْكُ لِلَّهِ، وَالْحَمْدُ لِلَّهِ، لَا إِلَٰهَ إِلَّا اللَّهُ وَحْدَهُ Lَا شَرِيكَ لَهُ، لَهُ الْمُلْكُ وَلَهُ الْحَمْدُ وَهُوَ عَلَىٰ كُلِّ شَيْءٍ قَدِيرٌ. رَبِّ أَسْأَلُكَ خَيْرَ مَا فِي هَٰذَا الْيَوْمِ وَخَيْرَ مَا بَعْدَهُ، وَأَعُوذُ بِكَ مِنْ شَرِّ مَا فِي هَٰذَا الْيَوْمِ وَشَرِّ مَا بَعْدَهُ، رَبِّ أَعُوذُ بِكَ مِنَ الْكَسَلِ وَسُوءِ الْكِبَرِ، رَبِّ أَعُوذُ بِكَ مِنْ عَذَابٍ فِي النَّارِ وَعَذَابٍ فِي الْقَبْرِ',
    morningLatin: 'Asbahna wa asbahal-mulku lillah, wal-hamdu lillah, la ilaha illallahu wahdahu la sharika lah, lahul-mulku wa lahul-hamdu wa huwa \'ala kulli shay\'in qadir. Rabbi as\'aluka khayra ma fi hadhal-yawmi wa khayra ma ba\'dah, wa a\'udhu bika min sharri ma fi hadhal-yawmi wa sharri ma ba\'dah. Rabbi a\'udhu bika minal-kasali wa su\'il-kibar, rabbi a\'udhu bika min \'adhabin fin-nari wa \'adhabin fil-qabr.',
    morningTranslation: 'Kami memasuki waktu pagi dan segala kekuasaan milik Allah, segala puji bagi Allah. Tiada Tuhan selain Allah Yang Maha Esa, tiada sekutu bagi-Nya. Bagi-Nya kekuasaan dan pujian, dan Dia Maha Kuasa atas segala sesuatu. Ya Tuhanku, aku mohon kebaikan hari ini dan kebaikan sesudahnya, dan berlindung dari kejahatan hari ini dan kejahatan sesudahnya. Ya Tuhanku, aku berlindung dari malas dan keburukan masa tua, serta azab neraka dan kubur.',
    // Evening
    eveningArabic: 'أَمْسَيْنَا وَأَمْسَى الْمُلْكُ لِلَّهِ، وَالْحَمْدُ لِلَّهِ، لَا إِلَٰهَ إِلَّا اللَّهُ وَحْدَهُ لَا شَرِيكَ لَهُ، لَهُ الْمُلْكُ وَلَهُ الْحَمْدُ وَهُوَ عَلَىٰ كُلِّ شَيْءٍ قَدِيرٌ. رَبِّ أَسْأَلُكَ خَيْرَ مَا فِي هَٰذِهِ اللَّيْلَةِ وَخَيْرَ مَا بَعْدَهَا، وَأَعُوذُ بِكَ مِنْ شَرِّ مَا فِي هَٰذِهِ اللَّيْلَةِ وَشَرِّ مَا بَعْدَهَا، رَبِّ أَعُوذُ بِكَ مِنَ الْكَسَلِ وَسُوءِ الْكِبَرِ، رَبِّ أَعُوذُ بِكَ مِنْ عَذَابٍ فِي النَّارِ وَعَذَابٍ فِي الْقَبْرِ',
    eveningLatin: 'Amsayna wa amsal-mulku lillah, wal-hamdu lillah, la ilaha illallahu wahdahu la sharika lah, lahul-mulku wa lahul-hamdu wa huwa \'ala kulli shay\'in qadir. Rabbi as\'aluka khayra ma fi hadhihil-laylati wa khayra ma ba\'daha, wa a\'udhu bika min sharri ma fi hadhihil-laylati wa sharri ma ba\'daha. Rabbi a\'udhu bika minal-kasali wa su\'il-kibar, rabbi a\'udhu bika min \'adhabin fin-nari wa \'adhabin fil-qabr.',
    eveningTranslation: 'Kami memasuki waktu petang dan segala kekuasaan milik Allah, segala puji bagi Allah. Tiada Tuhan selain Allah Yang Maha Esa, tiada sekutu bagi-Nya. Bagi-Nya kekuasaan dan pujian, dan Dia Maha Kuasa atas segala sesuatu. Ya Tuhanku, aku mohon kebaikan malam ini dan kebaikan sesudahnya, dan berlindung dari kejahatan malam ini dan kejahatan sesudahnya...',
    target: 1,
    count: 0,
    isSugra: true,
    fadilah: 'Menyerahkan hari dan urusan kita sepenuhnya di bawah perlindungan dan kasih sayang Allah.'
  },
  {
    id: 9,
    title: 'Sayyidul Istighfar (Induk Istighfar)',
    arabic: 'اللَّهُمَّ أَنْتَ رَبِّي لَا إِلَٰهَ إِلَّا أَنْتَ، خَلَقْتَنِي وَأَنَا عَبْدُكَ، وَأَنَا عَلَىٰ عَهْدِكَ وَوَعْدِكَ مَا اسْتَطَعْتُ، أَعُوذُ بِكَ مِنْ شَرِّ مَا صَنَعْتُ، أَبُوءُ لَكَ بِنِعْمَتِكَ عَلَيَّ، وَأَبُوءُ بِذَنْبِي فَاغْفِرْ لِي فَإِنَّهُ لَا يَغْفِرُ الذُّنُوبَ إِلَّا أَنْتَ',
    latin: 'Allahumma anta rabbi la ilaha illa ant, khalaqtani wa ana \'abduk, wa ana \'ala \'ahdika wa wa\'dika mastata\'t. A\'udhu bika min sharri ma sana\'t, abu\'u laka bi-ni\'matika \'alayya, wa abu\'u bi-dhanbi faghfir li, fa-innahu la yaghfirudh-dhunuba illa ant.',
    translation: 'Ya Allah, Engkau adalah Tuhanku, tiada Tuhan selain Engkau. Engkau telah menciptakanku dan aku adalah hamba-Mu. Aku menetapi perjanjian-Mu dan janji-Mu sesuai kemampuanku. Aku berlindung kepada-Mu dari keburukan perbuatanku. Aku mengakui nikmat-Mu kepadaku dan mengakui dosaku, maka ampunilah aku. Karena sesungguhnya tiada yang mengampuni dosa selain Engkau.',
    target: 1,
    count: 0,
    isSugra: true,
    fadilah: 'Barangsiapa membacanya dengan penuh keyakinan dan meninggal pada hari itu, niscaya ia menjadi penghuni syurga.'
  },
  {
    id: 10,
    title: 'Keridhaan kepada Allah, Islam, & Rasul',
    arabic: 'رَضِيتُ بِاللَّهِ رَبًّا، وَبِالْإِسْلَامِ دِينًا، وَبِمُحَمَّدٍ نَبِيًّا وَرَسُولًا',
    latin: 'Radhitu billahi rabba, wa bil-islami dina, wa bi-muhammadin nabiyya wa rasula.',
    translation: 'Aku ridha Allah sebagai Tuhanku, Islam sebagai agamaku, dan Muhammad sebagai Nabi dan Rasul-Ku.',
    target: 3,
    count: 0,
    isSugra: true,
    fadilah: 'Allah menjamin akan meridhai hamba tersebut pada hari kiamat kelak.'
  },
  {
    id: 11,
    title: 'Perlindungan dari Musibah & Bahaya',
    arabic: 'بِسْمِ اللَّهِ الَّذِي لَا يَضُرُّ مَعَ اسْمِهِ شَيْءٌ فِي الْأَرْضِ وَلَا فِي السَّمَاءِ وَهُوَ السَّمِيعُ الْعَلِيمُ',
    latin: 'Bismillahil-ladhi la yadhurru ma\'as-mihi shay\'un fil-ardhi wa la fis-samai wa huwas-sami\'ul-\'alim.',
    translation: 'Dengan nama Allah yang bila disebut nama-Nya, segala sesuatu di bumi dan langit tidak akan berbahaya, dan Dia Maha Mendengar lagi Maha Mengetahui.',
    target: 3,
    count: 0,
    isSugra: true,
    fadilah: 'Tidak akan diganggu oleh bahaya racun, binatang buas, sihir, maupun musibah tiba-tiba.'
  },
  {
    id: 12,
    title: 'Tasbih Pujian Keagungan Allah',
    arabic: 'سُبْحَانَ اللَّهِ وَبِحَمْدِهِ: عَدَدَ خَلْقِهِ، وَرِضَا نَفْسِهِ، وَزِنَةَ عَرْشِهِ، وَمِدَادَ كَلِمَاتِهِ',
    latin: 'Subhanallahi wa bihamdihi: \'adada khalqih, wa ridha nafsih, wa zinata \'arshih, wa midada kalimatih.',
    translation: 'Maha Suci Allah dan segala puji bagi-Nya sebanyak jumlah makhluk-Nya, seridha diri-Nya, seberat timbangan Arasy-Nya, dan sebanyak tinta kalimat-kalimat-Nya.',
    target: 3,
    count: 0,
    isSugra: true,
    fadilah: 'Memiliki bobot pahala dzikir yang sangat berat melebihi dzikir biasa sepanjang pagi.'
  },
  {
    id: 13,
    title: 'Istighfar Taubat Harian',
    arabic: 'أَسْتَغْفِرُ اللَّهَ وَأَتُوبُ إِلَيْهِ',
    latin: 'Astaghfirullaha wa atubu ilayh.',
    translation: 'Aku memohon ampunan kepada Allah dan aku bertaubat kepada-Nya.',
    target: 100,
    count: 0,
    isSugra: true,
    fadilah: 'Membuka kelapangan rezeki, menghapus kepenatan hati, dan membersihkan dosa-dosa.'
  },
  {
    id: 14,
    title: 'Shalawat atas Nabi Muhammad',
    arabic: 'اللَّهُمَّ صَلِّ عَلَىٰ مُحَمَّدٍ وَعَلَىٰ آلِ مُحَمَّدٍ',
    latin: 'Allahumma shalli \'ala Muhammadin wa \'ala ali Muhammad.',
    translation: 'Ya Allah, limpahkanlah rahmat dan kesejahteraan kepada Nabi Muhammad dan kepada keluarga Nabi Muhammad.',
    target: 10,
    count: 0,
    isSugra: true,
    fadilah: 'Mendapat syafaat dari Rasulullah SAW di akhirat kelak serta diganjar 10 kali rahmat oleh Allah.'
  },
  {
    id: 15,
    title: 'Asmaul Husna (Pembuka Dzikir Lengkap)',
    arabic: 'هُوَ اللَّهُ الَّذِي لَا إِلَٰهَ إِلَّا هُوَ الرَّحْمَٰنُ الرَّحِيمُ الْمَلِكُ الْقُدُّوسُ السَّلَامُ الْمُؤْمِنُ الْمُهَيْمِنُ الْعَزِيزُ الْجَبَّارُ الْمُتَكَبِّرُ',
    latin: 'Huwallahulladhi la ilaha illa huwar-rahmanur-rahimul-malikul-quddusus-salamul-mu\'minul-muhayminul-\'azizul-jabbarul-mutakabbir.',
    translation: 'Dialah Allah yang tidak ada Tuhan selain Dia. Yang Maha Pengasih, Maha Penyayang, Maha Raja, Maha Suci, Maha Sejahtera, Maha Menjaga Keamanan, Maha Mengawasi, Maha Perkasa, Maha Kuasa, Maha Memiliki Segala Keagungan.',
    target: 1,
    count: 0,
    isSugra: false,
    fadilah: 'Menenangkan jiwa dan merupakan tawasul asmaul husna yang sangat dianjurkan.'
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
    osc.frequency.setValueAtTime(800, ctx.currentTime);
    gain.gain.setValueAtTime(0.04, ctx.currentTime);
    osc.start();
    gain.gain.exponentialRampToValueAtTime(0.00001, ctx.currentTime + 0.1);
    osc.stop(ctx.currentTime + 0.1);
  } catch (e) {}
};

const playSuccessBeep = () => {
  try {
    const ctx = new (window.AudioContext || window.webkitAudioContext)();
    const osc = ctx.createOscillator();
    const gain = ctx.createGain();
    osc.connect(gain);
    gain.connect(ctx.destination);
    osc.frequency.setValueAtTime(1100, ctx.currentTime);
    gain.gain.setValueAtTime(0.07, ctx.currentTime);
    osc.start();
    gain.gain.exponentialRampToValueAtTime(0.00001, ctx.currentTime + 0.25);
    osc.stop(ctx.currentTime + 0.25);
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
  
  // Sugra vs Kubra filter
  if (activeDhikrMode.value === 'sugra') {
    list = list.filter(d => d.isSugra);
  }

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
  <Head title="Al-Ma'tsurat Dzikir Pagi & Petang" />

  <div class="min-h-screen bg-emerald-50/60 relative pb-16">
    <!-- Decorative background elements -->
    <div class="fixed inset-0 pointer-events-none overflow-hidden">
      <div class="absolute -top-32 -right-32 w-96 h-96 bg-emerald-100/50 rounded-full blur-3xl"></div>
      <div class="absolute -bottom-32 -left-32 w-96 h-96 bg-amber-50/60 rounded-full blur-3xl"></div>
    </div>

    <!-- Navigation Header -->
    <nav class="sticky top-0 z-30 bg-white border-b border-emerald-100 shadow-sm">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-14 sm:h-16">
          
          <div class="flex items-center space-x-2 sm:space-x-3">
            <div class="bg-emerald-700 text-amber-300 p-1.5 rounded-xl">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 sm:h-6 sm:w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
              </svg>
            </div>
            <div class="flex flex-col">
              <span class="text-sm sm:text-base font-bold text-emerald-900 leading-tight">Al-Ma'tsurat Interaktif</span>
              <span class="text-xs text-emerald-500 hidden sm:block font-medium">Dzikir pagi & petang harian Anda</span>
            </div>
          </div>

          <div class="flex items-center space-x-2">
            <a
              href="/quran"
              class="flex items-center space-x-1 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-semibold rounded-lg px-3 py-2 text-xs transition-colors shadow-sm"
              title="Baca Al-Quran"
            >
              <span>Al-Quran</span>
            </a>
            
            <a
              v-if="auth"
              :href="dashboardUrl"
              class="flex items-center space-x-1 bg-emerald-700 hover:bg-emerald-800 text-white font-semibold rounded-lg px-3 py-2 text-xs transition-colors shadow-sm"
              title="Kembali ke Dashboard"
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
    <main class="max-w-4xl mx-auto px-4 mt-6 relative z-10">
      
      <!-- Hero Header Banner -->
      <div class="bg-gradient-to-r from-emerald-800 to-emerald-950 text-white rounded-2xl p-6 sm:p-8 shadow-xl mb-6 border border-emerald-700 relative overflow-hidden">
        <div class="absolute inset-0 opacity-[0.03]">
          <div class="absolute top-4 right-8 text-8xl font-arabic select-none leading-none">أذكار</div>
        </div>
        <div class="relative">
          <p class="text-amber-300 text-xs font-bold uppercase tracking-widest mb-1">Dzikir Pagi & Petang</p>
          <h1 class="text-2xl sm:text-3xl font-bold mb-1">Al-Ma'tsurat Wazifah</h1>
          <p class="text-emerald-200 text-xs sm:text-sm max-w-lg leading-relaxed">
            Menghidupkan sunnah Rasulullah SAW di awal pagi dan petang. Gunakan tasbih counter interaktif di setiap dzikir untuk menghitung bacaan Anda.
          </p>
        </div>
      </div>

      <!-- Controls & Filter Toolbar -->
      <div class="bg-white border border-emerald-100 rounded-2xl p-4 shadow-sm space-y-4 mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
          
          <!-- Morning/Evening Selector -->
          <div class="flex bg-gray-100 p-0.5 rounded-lg border border-gray-250/20 w-fit">
            <button
              @click="activeTimeMode = 'pagi'"
              :class="activeTimeMode === 'pagi' ? 'bg-amber-400 text-emerald-950 font-bold shadow-sm' : 'text-gray-500 hover:text-emerald-950'"
              class="px-4 py-1.5 rounded-md text-xs font-semibold transition-all flex items-center gap-1"
            >
              <span>☀️</span> Dzikir Pagi
            </button>
            <button
              @click="activeTimeMode = 'petang'"
              :class="activeTimeMode === 'petang' ? 'bg-emerald-800 text-white font-bold shadow-sm' : 'text-gray-500 hover:text-emerald-950'"
              class="px-4 py-1.5 rounded-md text-xs font-semibold transition-all flex items-center gap-1"
            >
              <span>🌙</span> Dzikir Petang
            </button>
          </div>

          <!-- Sugra/Kubra Mode -->
          <div class="flex bg-gray-100 p-0.5 rounded-lg border border-gray-250/20 w-fit">
            <button
              @click="activeDhikrMode = 'sugra'"
              :class="activeDhikrMode === 'sugra' ? 'bg-emerald-700 text-white font-bold shadow-sm' : 'text-gray-500 hover:text-emerald-950'"
              class="px-4 py-1.5 rounded-md text-xs font-semibold transition-all"
            >
              Mode Ringkas (Sugra)
            </button>
            <button
              @click="activeDhikrMode = 'kubra'"
              :class="activeDhikrMode === 'kubra' ? 'bg-emerald-700 text-white font-bold shadow-sm' : 'text-gray-500 hover:text-emerald-950'"
              class="px-4 py-1.5 rounded-md text-xs font-semibold transition-all"
            >
              Mode Lengkap (Kubra)
            </button>
          </div>
          
        </div>

        <!-- Search Bar and Reset Controls -->
        <div class="flex flex-col sm:flex-row items-center gap-3 pt-3 border-t border-emerald-50">
          <div class="relative w-full sm:flex-1">
            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-emerald-400">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
            </span>
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Cari dzikir harian..."
              class="w-full pl-9 pr-4 py-2 border border-emerald-100 rounded-xl text-xs outline-none focus:ring-1 focus:ring-emerald-500 shadow-sm"
            />
          </div>
          <div class="flex items-center gap-2 w-full sm:w-auto justify-end">
            <button
              @click="resetAll"
              class="px-3.5 py-2 text-xs bg-red-50 hover:bg-red-100 text-red-700 border border-red-200 font-bold rounded-xl shadow-sm transition-all"
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

      <!-- Dhikr Cards List -->
      <div class="space-y-4">
        <div
          v-for="(dhikr, index) in filteredDhikrs"
          :key="dhikr.id"
          class="bg-white border rounded-2xl p-5 shadow-sm transition-all relative overflow-hidden"
          :class="dhikr.count === dhikr.target 
            ? 'border-emerald-500 shadow-emerald-500/10 ring-1 ring-emerald-500/30' 
            : 'border-emerald-100 hover:border-emerald-200'"
        >
          <!-- Corner indicator for completed item -->
          <div 
            v-if="dhikr.count === dhikr.target"
            class="absolute top-0 right-0 w-12 h-12 bg-emerald-500 text-white rounded-bl-3xl flex items-center justify-center pt-2.5 pr-2.5"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
            </svg>
          </div>

          <!-- Header info -->
          <div class="flex items-center gap-2 mb-3">
            <span class="bg-emerald-100 text-emerald-700 text-xs font-bold rounded-lg w-7 h-7 flex items-center justify-center flex-shrink-0">
              {{ index + 1 }}
            </span>
            <div>
              <h3 class="text-sm font-bold text-emerald-950">{{ dhikr.title }}</h3>
              <p class="text-[10px] text-gray-400 font-semibold uppercase tracking-wider">
                Target: {{ dhikr.target }} kali
              </p>
            </div>
          </div>

          <!-- Arabic Script -->
          <div class="py-4">
            <p 
              class="font-arabic text-right text-2xl sm:text-3xl leading-[2.2] text-emerald-950 select-text" 
              dir="rtl"
            >
              <!-- Show morning or evening arabic if time dependent -->
              {{ dhikr.isTimeDependent 
                ? (activeTimeMode === 'pagi' ? dhikr.morningArabic : dhikr.eveningArabic) 
                : dhikr.arabic 
              }}
            </p>
          </div>

          <!-- Transliteration Latin -->
          <div class="border-t border-emerald-50 pt-3.5 space-y-2.5">
            <p class="text-xs text-emerald-700 italic leading-relaxed">
              {{ dhikr.isTimeDependent 
                ? (activeTimeMode === 'pagi' ? dhikr.morningLatin : dhikr.eveningLatin) 
                : dhikr.latin 
              }}
            </p>

            <!-- Translation -->
            <p class="text-xs text-gray-650 leading-relaxed">
              <span class="text-[10px] font-bold text-emerald-500 uppercase mr-1">Artinya:</span>
              {{ dhikr.isTimeDependent 
                ? (activeTimeMode === 'pagi' ? dhikr.morningTranslation : dhikr.eveningTranslation) 
                : dhikr.translation 
              }}
            </p>

            <!-- Fadilah / Reward description -->
            <div class="bg-[#F8FAF9] border border-emerald-50/50 p-2.5 rounded-xl text-[10px] text-gray-500 italic mt-2">
              <span class="font-bold text-amber-600 block mb-0.5">Keutamaan:</span>
              {{ dhikr.fadilah }}
            </div>
          </div>

          <!-- Interactive Counter Section -->
          <div class="mt-5 pt-4 border-t border-emerald-50/70 flex items-center justify-between gap-4">
            <!-- Progress Bar -->
            <div class="flex-1">
              <div class="flex justify-between text-[10px] text-gray-400 font-bold mb-1">
                <span>Progress</span>
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
                @click="resetDhikr(dhikr)"
                class="p-2 border border-red-200 text-red-500 hover:bg-red-50 rounded-xl transition-colors"
                title="Reset hitungan dzikir ini"
              >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 1121.21 6H16" />
                </svg>
              </button>

              <button
                @click="incrementDhikr(dhikr)"
                :disabled="dhikr.count === dhikr.target"
                class="px-4 py-2 font-bold text-xs rounded-xl shadow-sm border transition-all flex items-center gap-1.5"
                :class="dhikr.count === dhikr.target
                  ? 'bg-emerald-50 border-emerald-250 text-emerald-800 cursor-not-allowed shadow-none'
                  : 'bg-emerald-700 hover:bg-emerald-800 text-white border-emerald-700'"
              >
                <span>{{ dhikr.count === dhikr.target ? '✓ Selesai' : 'Hitung' }}</span>
                <span 
                  v-if="dhikr.count < dhikr.target"
                  class="bg-white/20 px-1.5 py-0.5 rounded text-[10px]"
                >
                  +1
                </span>
              </button>
            </div>
          </div>

        </div>

        <p v-if="filteredDhikrs.length === 0" class="text-center text-emerald-400 text-sm py-10">
          Tidak ada dzikir yang ditemukan sesuai pencarian.
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
