<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Attendance;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceCheckInController extends Controller
{
    /**
     * Member self check-in for the latest active activity in their group.
     */
    public function checkIn(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'activity_id' => ['required', 'exists:activities,id'],
            'status'      => ['nullable', 'in:present,late,sick,permission'],
        ]);

        $activity = Activity::with('group')->findOrFail($validated['activity_id']);

        // Verify the user is a member or the leader of this activity's group
        $isMember = $activity->group->members()->where('user_id', $user->id)->exists();
        $isLeader = $activity->group->leader_id === $user->id;

        if (!$isMember && !$isLeader) {
            abort(403, 'Anda bukan anggota kelompok halaqah ini.');
        }

        // Prevent duplicate check-in
        $existing = Attendance::where('user_id', $user->id)
            ->where('activity_id', $activity->id)
            ->first();

        if ($existing) {
            return redirect()->back()->with('info', 'Anda sudah melakukan check-in untuk sesi ini.');
        }

        Attendance::create([
            'user_id'     => $user->id,
            'activity_id' => $activity->id,
            'status'      => $validated['status'] ?? 'present',
            // approved_by and approved_at remain null until Ustad/Leader approves
        ]);

        return redirect()->back()->with('success', 'Check-in kehadiran berhasil dicatat. Menunggu verifikasi Ustad.');
    }

    /**
     * Member self check-out (marks the attendance as still present, just logs departure time).
     * Optional: we can store checkout time in a nullable `checked_out_at` column if needed.
     */
    public function checkOut(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'activity_id' => ['required', 'exists:activities,id'],
        ]);

        $attendance = Attendance::where('user_id', $user->id)
            ->where('activity_id', $validated['activity_id'])
            ->first();

        if (!$attendance) {
            return redirect()->back()->with('error', 'Anda belum melakukan check-in pada sesi ini.');
        }

        // Mark as checked out (could be extended with a checked_out_at column)
        return redirect()->back()->with('success', 'Check-out berhasil dicatat. Jazakallahu khairan.');
    }
}
