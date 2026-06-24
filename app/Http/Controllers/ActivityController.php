<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Group;
use App\Models\Attendance;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class ActivityController extends Controller
{
    /**
     * Store a new activity session for a group (Ustad/Leader/Superadmin).
     */
    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();

        if (!$user->isUstad() && !$user->isSuperadmin() && !$user->isLeader()) {
            abort(403, 'Hanya Ustad, Ketua Kelompok, atau Superadmin yang dapat membuat sesi kajian.');
        }

        $validated = $request->validate([
            'group_id'    => ['required', 'exists:groups,id'],
            'date'        => ['required', 'date'],
            'topic'       => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'attendances' => ['nullable', 'array'],
            'attendances.*.user_id' => ['required', 'exists:users,id'],
            'attendances.*.status' => ['required', 'in:present,late,absent,sick,permission'],
        ]);

        // Verify the user manages/leads this group
        $group = Group::findOrFail($validated['group_id']);
        if (!$user->isSuperadmin()) {
            if ($user->isUstad() && $group->ustad_id !== $user->id) {
                abort(403, 'Anda tidak berwenang mengelola kelompok ini.');
            }
            if ($user->isLeader() && $group->leader_id !== $user->id) {
                abort(403, 'Anda tidak berwenang mengelola kelompok ini.');
            }
        }

        // Create the activity session
        $activity = Activity::create([
            'group_id'    => $validated['group_id'],
            'date'        => $validated['date'],
            'topic'       => $validated['topic'],
            'description' => $validated['description'] ?? null,
        ]);

        // Process bulk attendances if provided
        if (!empty($validated['attendances'])) {
            foreach ($validated['attendances'] as $attendanceData) {
                // If created by Ketua Kelompok (Leader), the attendance requires Pembina approval (approved_by = null).
                // If created by Ustad/Superadmin, it is automatically approved.
                $isLeader = $user->isLeader();

                Attendance::create([
                    'activity_id' => $activity->id,
                    'user_id'     => $attendanceData['user_id'],
                    'status'      => $attendanceData['status'],
                    'approved_by' => $isLeader ? null : $user->id,
                    'approved_at' => $isLeader ? null : Carbon::now(),
                ]);
            }
        }

        $successMessage = $user->isLeader()
            ? 'Sesi kajian berhasil diajukan. Menunggu approval Pembina.'
            : 'Sesi kajian baru berhasil ditambahkan.';

        return redirect()->back()->with('success', $successMessage);
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
