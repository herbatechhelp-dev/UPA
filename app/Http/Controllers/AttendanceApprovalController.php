<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Attendance;
use App\Models\Group;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class AttendanceApprovalController extends Controller
{
    /**
     * Helper to verify if current user is allowed to approve/reject attendance for the activity.
     */
    private function checkAuthorization(Activity $activity): void
    {
        $group = $activity->group;
        $currentUser = Auth::user();
        $isAuthorized = false;

        // Condition A: Logged-in user is a Superadmin (full bypass)
        if ($currentUser->isSuperadmin()) {
            $isAuthorized = true;
        }
        // Condition B: Logged-in user is the Ustad (Pembina) of this mentoring group
        elseif ($group->ustad_id === $currentUser->id && $currentUser->isUstad()) {
            $isAuthorized = true;
        } 
        // Condition C: Logged-in user is the Ketua Kelompok (Leader)
        elseif ($group->leader_id === $currentUser->id && $currentUser->isLeader()) {
            $isAuthorized = true;
        }

        if (!$isAuthorized) {
            abort(403, 'Anda tidak memiliki hak akses untuk menyetujui absensi kelompok ini.');
        }
    }

    /**
     * Approve attendance list for a specific activity (bulk).
     * Supports bulk status updates and logs the approver.
     *
     * @param Request $request
     * @param Activity $activity
     * @return RedirectResponse
     */
    public function approve(Request $request, Activity $activity): RedirectResponse
    {
        $this->checkAuthorization($activity);
        $currentUser = Auth::user();

        // 2. Validation of incoming payload
        $validated = $request->validate([
            'attendances' => ['required', 'array'],
            'attendances.*.user_id' => ['required', 'exists:users,id'],
            'attendances.*.status' => ['required', 'in:present,late,absent,sick,permission'],
        ]);

        // Check if delegation is currently active for this leader (if leader is performing the action)
        $isDelegated = false;
        if ($currentUser->isLeader()) {
            $group = $activity->group;
            if ($group->is_delegated && ($group->delegated_until === null || Carbon::parse($group->delegated_until)->isFuture())) {
                $isDelegated = true;
            }
        }

        // 3. Process each attendance update
        foreach ($validated['attendances'] as $attendanceData) {
            // If the current user is Ustad or Superadmin, or if they are a delegated Leader, auto-approve.
            // If they are a non-delegated Leader, save as pending (approved_by = null).
            $shouldApprove = $currentUser->isSuperadmin() || $currentUser->isUstad() || $isDelegated;

            Attendance::updateOrCreate(
                [
                    'activity_id' => $activity->id,
                    'user_id' => $attendanceData['user_id'],
                ],
                [
                    'status' => $attendanceData['status'],
                    'approved_by' => $shouldApprove ? $currentUser->id : null,
                    'approved_at' => $shouldApprove ? Carbon::now() : null,
                ]
            );
        }

        $message = ($currentUser->isLeader() && !$isDelegated)
            ? 'Draf absensi kelompok berhasil diajukan untuk approval.'
            : 'Absensi berhasil diverifikasi dan disimpan.';

        return redirect()->back()->with('success', $message);
    }

    /**
     * Approve a single user's check-in for a specific activity.
     */
    public function approveSingle(Request $request, Activity $activity, \App\Models\User $user): RedirectResponse
    {
        $this->checkAuthorization($activity);
        $currentUser = Auth::user();

        // Enforce delegation check for leaders
        if ($currentUser->isLeader()) {
            $group = $activity->group;
            $isDelegated = $group->is_delegated && ($group->delegated_until === null || Carbon::parse($group->delegated_until)->isFuture());
            if (!$isDelegated) {
                abort(403, 'Anda tidak memiliki hak akses untuk menyetujui absensi tanpa delegasi.');
            }
        }

        $attendance = Attendance::where('activity_id', $activity->id)
            ->where('user_id', $user->id)
            ->first();

        if (!$attendance) {
            // If they haven't checked in, let's create a present check-in
            $attendance = Attendance::create([
                'activity_id' => $activity->id,
                'user_id'     => $user->id,
                'status'      => 'present',
            ]);
        }

        $attendance->update([
            'approved_by' => $currentUser->id,
            'approved_at' => Carbon::now(),
        ]);

        return redirect()->back()->with('success', 'Kehadiran ' . $user->name . ' berhasil disetujui.');
    }

    /**
     * Reject/delete a single user's check-in for a specific activity.
     */
    public function rejectSingle(Request $request, Activity $activity, \App\Models\User $user): RedirectResponse
    {
        $this->checkAuthorization($activity);
        $currentUser = Auth::user();

        // Enforce delegation check for leaders
        if ($currentUser->isLeader()) {
            $group = $activity->group;
            $isDelegated = $group->is_delegated && ($group->delegated_until === null || Carbon::parse($group->delegated_until)->isFuture());
            if (!$isDelegated) {
                abort(403, 'Anda tidak memiliki hak akses untuk menolak absensi tanpa delegasi.');
            }
        }

        $attendance = Attendance::where('activity_id', $activity->id)
            ->where('user_id', $user->id)
            ->first();

        if ($attendance) {
            $attendance->delete();
        }

        return redirect()->back()->with('success', 'Kehadiran ' . $user->name . ' berhasil ditolak.');
    }

    /**
     * Delegate or revoke attendance approval authority to the group's Ketua Kelompok.
     * Accessible only by the Ustad (Pembina) of the group.
     *
     * @param Request $request
     * @param Group $group
     * @return RedirectResponse
     */
    public function toggleDelegation(Request $request, Group $group): RedirectResponse
    {
        $currentUser = Auth::user();

        // Verify that the logged-in user is the designated Ustad, Admin, or a Superadmin
        if (!$currentUser->isSuperadmin() && !$currentUser->isAdmin() && ($group->ustad_id !== $currentUser->id || !$currentUser->isUstad())) {
            abort(403, 'Hanya Ustad pembina kelompok ini, Admin, atau Superadmin yang dapat mengubah kebijakan delegasi.');
        }

        $validated = $request->validate([
            'is_delegated' => ['required', 'boolean'],
            'delegated_until' => ['nullable', 'date', 'after:now'],
        ]);

        $group->update([
            'is_delegated' => $validated['is_delegated'],
            'delegated_until' => $validated['is_delegated'] ? $validated['delegated_until'] : null,
        ]);

        $statusMessage = $validated['is_delegated'] 
            ? 'Delegasi approval absensi berhasil diaktifkan untuk Ketua Kelompok.'
            : 'Delegasi approval absensi telah dinonaktifkan.';

        return redirect()->back()->with('success', $statusMessage);
    }

    /**
     * Delete a specific attendance record.
     */
    public function destroy(Attendance $attendance): RedirectResponse
    {
        $currentUser = Auth::user();
        $group = $attendance->activity->group;

        if (!$currentUser->isSuperadmin()) {
            if ($group->ustad_id !== $currentUser->id || !$currentUser->isUstad()) {
                abort(403, 'Anda tidak berwenang menghapus log presensi ini.');
            }
        }

        $attendance->delete();

        return redirect()->back()->with('success', 'Catatan absensi berhasil dihapus.');
    }
}

