<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Material;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class MaterialController extends Controller
{
    /**
     * Store a newly uploaded material (Ustad only).
     */
    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();

        if (!$user->isUstad() && !$user->isSuperadmin()) {
            abort(403, 'Hanya Ustad atau Superadmin yang dapat mengunggah materi kajian.');
        }

        $validated = $request->validate([
            'title'   => ['required', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
            'file'    => ['nullable', 'file', 'mimes:pdf,doc,docx,ppt,pptx,zip', 'max:25600'], // max 25MB
        ]);

        $filePath = null;

        if ($request->hasFile('file')) {
            try {
                // Ensure the target directory exists
                if (!Storage::disk('public')->exists('materials')) {
                    Storage::disk('public')->makeDirectory('materials');
                }
                $filePath = $request->file('file')->store('materials', 'public');
            } catch (\Exception $e) {
                Log::error('Material upload failed: ' . $e->getMessage(), [
                    'path' => storage_path('app/public'),
                    'writable' => is_writable(storage_path('app/public')) ? 'yes' : 'no',
                ]);
                return redirect()->back()->withErrors(['file' => 'Gagal mengunggah file: ' . $e->getMessage()])->withInput();
            }
        }

        Material::create([
            'ustad_id'     => $user->id,
            'title'        => $validated['title'],
            'content'      => $validated['content'] ?? null,
            'file_path'    => $filePath,
            'published_at' => Carbon::now(),
        ]);

        return redirect()->back()->with('success', 'Materi kajian berhasil diunggah dan dipublikasikan.');
    }

    /**
     * Display the specified material / trigger download.
     */
    public function download(Material $material)
    {
        if (!$material->file_path) {
            abort(404, 'File materi tidak ditemukan.');
        }

        if (!Storage::disk('public')->exists($material->file_path)) {
            abort(404, 'File materi tidak tersedia di server.');
        }

        return Storage::disk('public')->download($material->file_path, $material->title . '.' . pathinfo($material->file_path, PATHINFO_EXTENSION));
    }

    /**
     * Delete a material (Ustad owner only).
     */
    public function destroy(Material $material): RedirectResponse
    {
        $user = Auth::user();

        if ($material->ustad_id !== $user->id && !$user->isSuperadmin()) {
            abort(403, 'Anda tidak berhak menghapus materi ini.');
        }

        if ($material->file_path) {
            Storage::disk('public')->delete($material->file_path);
        }

        $material->delete();

        return redirect()->back()->with('success', 'Materi berhasil dihapus.');
    }
}
