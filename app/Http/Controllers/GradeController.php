<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Grade;
use App\Models\Group;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class GradeController extends Controller
{
    /**
     * Store or update monthly grade for a member (Ustad only).
     */
    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();

        if (!$user->isUstad() && !$user->isSuperadmin()) {
            abort(403, 'Hanya Ustad atau Superadmin yang dapat memberikan nilai.');
        }

        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'month'   => ['required', 'integer', 'min:1', 'max:12'],
            'year'    => ['required', 'integer', 'min:2020', 'max:2100'],
            'score'   => ['required', 'numeric', 'min:0', 'max:100'],
            'notes'   => ['nullable', 'string', 'max:2000'],
        ]);

        // Verify that the Ustad is the pembina of a group that contains this member
        if (!$user->isSuperadmin()) {
            $managedGroupIds = Group::where('ustad_id', $user->id)->pluck('id');
            $isManaged = \DB::table('group_user')
                ->whereIn('group_id', $managedGroupIds)
                ->where('user_id', $validated['user_id'])
                ->exists();

            if (!$isManaged) {
                abort(403, 'Anggota ini tidak berada dalam kelompok binaan Anda.');
            }
        }

        Grade::updateOrCreate(
            [
                'user_id' => $validated['user_id'],
                'month'   => $validated['month'],
                'year'    => $validated['year'],
            ],
            [
                'ustad_id' => $user->id,
                'score'    => $validated['score'],
                'notes'    => $validated['notes'] ?? null,
            ]
        );

        return redirect()->back()->with('success', 'Nilai bulanan berhasil disimpan.');
    }

    /**
     * Bulk store grades for multiple members at once (Rekap Bulanan).
     */
    public function bulkStore(Request $request): RedirectResponse
    {
        $user = Auth::user();

        if (!$user->isUstad() && !$user->isSuperadmin()) {
            abort(403, 'Hanya Ustad atau Superadmin yang dapat membuat rekap nilai.');
        }

        $validated = $request->validate([
            'month'            => ['required', 'integer', 'min:1', 'max:12'],
            'year'             => ['required', 'integer', 'min:2020', 'max:2100'],
            'grades'           => ['required', 'array'],
            'grades.*.user_id' => ['required', 'exists:users,id'],
            'grades.*.score'   => ['required', 'numeric', 'min:0', 'max:100'],
            'grades.*.notes'   => ['nullable', 'string', 'max:2000'],
        ]);

        $managedGroupIds = !$user->isSuperadmin() ? Group::where('ustad_id', $user->id)->pluck('id') : collect();

        foreach ($validated['grades'] as $gradeData) {
            if (!$user->isSuperadmin()) {
                $isManaged = \DB::table('group_user')
                    ->whereIn('group_id', $managedGroupIds)
                    ->where('user_id', $gradeData['user_id'])
                    ->exists();

                if (!$isManaged) {
                    continue;
                }
            }

            Grade::updateOrCreate(
                [
                    'user_id' => $gradeData['user_id'],
                    'month'   => $validated['month'],
                    'year'    => $validated['year'],
                ],
                [
                    'ustad_id' => $user->id,
                    'score'    => $gradeData['score'],
                    'notes'    => $gradeData['notes'] ?? null,
                ]
            );
        }

        return redirect()->back()->with('success', 'Rekap nilai bulanan berhasil disimpan untuk semua anggota binaan.');
    }

    /**
     * Delete a grade record.
     */
    public function destroy(Grade $grade): RedirectResponse
    {
        $user = Auth::user();

        if (!$user->isSuperadmin()) {
            if ($grade->ustad_id !== $user->id) {
                abort(403, 'Anda tidak berhak menghapus rekam nilai ini.');
            }
        }

        $grade->delete();

        return redirect()->back()->with('success', 'Data nilai bulanan berhasil dihapus.');
    }
}

