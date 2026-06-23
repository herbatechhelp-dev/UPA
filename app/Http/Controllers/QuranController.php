<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\QuranBookmark;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class QuranController extends Controller
{
    private const API_BASE = 'https://api.alquran.cloud/v1';

    /**
     * Display the Al-Quran page with surah list and user bookmarks.
     */
    public function index(): Response
    {
        $surahs = Cache::remember('quran_surahs', 86400, function () {
            $response = Http::timeout(10)->get(self::API_BASE . '/surah');

            if ($response->successful() && $response->json('code') === 200) {
                return collect($response->json('data'))->map(fn ($s) => [
                    'number'          => $s['number'],
                    'name'            => $s['name'],
                    'englishName'     => $s['englishName'],
                    'englishNameTranslation' => $s['englishNameTranslation'],
                    'numberOfAyahs'   => $s['numberOfAyahs'],
                    'revelationType'  => $s['revelationType'],
                ])->toArray();
            }

            return [];
        });

        $bookmarks = [];
        if (Auth::check()) {
            $bookmarks = QuranBookmark::where('user_id', Auth::id())
                ->orderByDesc('created_at')
                ->get()
                ->map(fn (QuranBookmark $b) => [
                    'id'           => $b->id,
                    'surah_number' => $b->surah_number,
                    'ayah_number'  => $b->ayah_number,
                    'surah_name'   => $b->surah_name,
                    'ayah_text'    => $b->ayah_text,
                    'created_at'   => $b->created_at->diffForHumans(),
                ]);
        }

        return Inertia::render('Quran', [
            'surahs'    => $surahs,
            'bookmarks' => $bookmarks,
            'auth'      => Auth::check() ? Auth::user()->load('role') : null,
        ]);
    }

    /**
     * Fetch surah detail: Arabic text + Indonesian + English translation + audio URLs.
     */
    public function surah(int $number): JsonResponse
    {
        if ($number < 1 || $number > 114) {
            return response()->json(['error' => 'Nomor surah tidak valid.'], 422);
        }

        $data = Cache::remember("quran_surah_{$number}", 3600, function () use ($number) {
            // Arabic (Uthmani) + Indonesian translation + English translation
            $editions = 'quran-uthmani,id.indonesian,en.sahih';
            $textResponse = Http::timeout(15)->get(self::API_BASE . "/surah/{$number}/editions/{$editions}");

            if (!$textResponse->successful() || $textResponse->json('code') !== 200) {
                return null;
            }

            $editionsData = $textResponse->json('data');
            $arabic  = $editionsData[0]['ayahs'] ?? [];
            $indonesian = $editionsData[1]['ayahs'] ?? [];
            $english = $editionsData[2]['ayahs'] ?? [];

            // Audio (Mishary Alafasy)
            $audioResponse = Http::timeout(15)->get(self::API_BASE . "/surah/{$number}/ar.alafasy");
            $audioAyahs = [];
            if ($audioResponse->successful() && $audioResponse->json('code') === 200) {
                $audioAyahs = $audioResponse->json('data.ayahs', []);
            }

            $ayahs = [];
            foreach ($arabic as $i => $ayah) {
                $ayahs[] = [
                    'number'        => $ayah['numberInSurah'],
                    'text_arabic'   => $ayah['text'],
                    'text_indonesian' => $indonesian[$i]['text'] ?? '',
                    'text_english'  => $english[$i]['text'] ?? '',
                    'audio_url'     => $audioAyahs[$i]['audio'] ?? null,
                    'juz'           => $ayah['juz'],
                    'page'          => $ayah['page'],
                ];
            }

            return [
                'number'          => $editionsData[0]['number'],
                'name'            => $editionsData[0]['name'],
                'englishName'     => $editionsData[0]['englishName'],
                'englishNameTranslation' => $editionsData[0]['englishNameTranslation'],
                'numberOfAyahs'   => $editionsData[0]['numberOfAyahs'],
                'revelationType'  => $editionsData[0]['revelationType'],
                'ayahs'           => $ayahs,
            ];
        });

        if (!$data) {
            return response()->json(['error' => 'Gagal memuat data surah. Coba lagi nanti.'], 500);
        }

        // Attach bookmark status for current user if logged in
        $userBookmarks = [];
        if (Auth::check()) {
            $userBookmarks = QuranBookmark::where('user_id', Auth::id())
                ->where('surah_number', $number)
                ->pluck('ayah_number')
                ->toArray();
        }

        foreach ($data['ayahs'] as &$ayah) {
            $ayah['is_bookmarked'] = in_array($ayah['number'], $userBookmarks);
            $ayah['bookmark_id'] = Auth::check()
                ? QuranBookmark::where('user_id', Auth::id())
                    ->where('surah_number', $number)
                    ->where('ayah_number', $ayah['number'])
                    ->value('id')
                : null;
        }

        return response()->json($data);
    }

    /**
     * Fetch tafsir (Ibn Kathir - English) for a surah.
     */
    public function tafsir(int $number): JsonResponse
    {
        if ($number < 1 || $number > 114) {
            return response()->json(['error' => 'Nomor surah tidak valid.'], 422);
        }

        $data = Cache::remember("quran_tafsir_{$number}", 3600, function () use ($number) {
            // Try Ibn Kathir tafsir (English)
            $response = Http::timeout(15)->get(self::API_BASE . "/surah/{$number}/en.ibnkathir");

            if ($response->successful() && $response->json('code') === 200) {
                return collect($response->json('data.ayahs', []))->map(fn ($ayah) => [
                    'number' => $ayah['numberInSurah'],
                    'tafsir' => $ayah['text'],
                ])->toArray();
            }

            // Fallback: en.jalalayn (Jalalayn tafsir - English)
            $fallback = Http::timeout(15)->get(self::API_BASE . "/surah/{$number}/en.jalalayn");

            if ($fallback->successful() && $fallback->json('code') === 200) {
                return collect($fallback->json('data.ayahs', []))->map(fn ($ayah) => [
                    'number' => $ayah['numberInSurah'],
                    'tafsir' => $ayah['text'],
                ])->toArray();
            }

            return [];
        });

        return response()->json(['ayahs' => $data]);
    }

    /**
     * Store a new bookmark.
     */
    public function bookmark(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'surah_number' => ['required', 'integer', 'min:1', 'max:114'],
            'ayah_number'  => ['required', 'integer', 'min:1'],
            'surah_name'   => ['required', 'string', 'max:50'],
            'ayah_text'    => ['nullable', 'string'],
        ]);

        $bookmark = QuranBookmark::updateOrCreate(
            [
                'user_id'      => Auth::id(),
                'surah_number' => $validated['surah_number'],
                'ayah_number'  => $validated['ayah_number'],
            ],
            [
                'surah_name' => $validated['surah_name'],
                'ayah_text'  => $validated['ayah_text'] ?? null,
            ]
        );

        return response()->json([
            'id'           => $bookmark->id,
            'is_bookmarked' => true,
        ]);
    }

    /**
     * Remove a bookmark.
     */
    public function unbookmark(QuranBookmark $bookmark): JsonResponse
    {
        if ($bookmark->user_id !== Auth::id()) {
            abort(403, 'Anda tidak berhak menghapus bookmark ini.');
        }

        $bookmark->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Get all bookmarks for the authenticated user.
     */
    public function bookmarks(): JsonResponse
    {
        $bookmarks = QuranBookmark::where('user_id', Auth::id())
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (QuranBookmark $b) => [
                'id'           => $b->id,
                'surah_number' => $b->surah_number,
                'ayah_number'  => $b->ayah_number,
                'surah_name'   => $b->surah_name,
                'ayah_text'    => $b->ayah_text,
                'created_at'   => $b->created_at->diffForHumans(),
            ]);

        return response()->json(['bookmarks' => $bookmarks]);
    }

    /**
     * Display the Al-Ma'tsurat page.
     */
    public function matsurat(): Response
    {
        return Inertia::render('Matsurat', [
            'auth' => Auth::check() ? Auth::user()->load('role') : null,
        ]);
    }
}
