<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Group;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ActivityController extends Controller
{
    /**
     * Store a new activity session for a group (Ustad only).
     */
    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();

        if (!$user->isUstad() && !$user->isSuperadmin()) {
            abort(403, 'Hanya Ustad atau Superadmin yang dapat membuat sesi kajian.');
        }

        $validated = $request->validate([
            'group_id'    => ['required', 'exists:groups,id'],
            'date'        => ['required', 'date'],
            'topic'       => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);

        // Verify the Ustad manages this group
        $group = Group::findOrFail($validated['group_id']);
        if (!$user->isSuperadmin() && $group->ustad_id !== $user->id) {
            abort(403, 'Anda tidak berwenang mengelola kelompok ini.');
        }

        Activity::create($validated);

        return redirect()->back()->with('success', 'Sesi kajian baru berhasil ditambahkan.');
    }

    /**
     * Update an existing activity session.
     */
    public function update(Request $request, Activity $activity): RedirectResponse
    {
        $user = Auth::user();

        if (!$user->isSuperadmin() && (!$user->isUstad() || $activity->group->ustad_id !== $user->id)) {
            abort(403, 'Anda tidak berhak mengubah sesi kajian ini.');
        }

        $validated = $request->validate([
            'date'        => ['sometimes', 'date'],
            'topic'       => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);

        $activity->update($validated);

        return redirect()->back()->with('success', 'Sesi kajian berhasil diperbarui.');
    }

    /**
     * Delete an activity session.
     */
    public function destroy(Activity $activity): RedirectResponse
    {
        $user = Auth::user();

        if (!$user->isSuperadmin() && (!$user->isUstad() || $activity->group->ustad_id !== $user->id)) {
            abort(403, 'Anda tidak berhak menghapus sesi kajian ini.');
        }

        $activity->delete();

        return redirect()->back()->with('success', 'Sesi kajian berhasil dihapus.');
    }
}
