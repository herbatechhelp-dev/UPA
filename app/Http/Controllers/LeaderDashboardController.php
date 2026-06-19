<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Attendance;
use App\Models\Group;
use App\Models\Material;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class LeaderDashboardController extends Controller
{
    /**
     * Display the Ketua Kelompok dashboard with group details, materials, and delegation status.
     */
    public function index(): Response
    {
        $user = Auth::user();

        // Find the group where this user is the designated leader
        $group = Group::where('leader_id', $user->id)
            ->with(['ustad:id,name', 'members:id,name'])
            ->first();

        if (!$group) {
            return Inertia::render('Leader/Dashboard', [
                'auth'           => $user->load('role'),
                'group'          => null,
                'activeActivity' => null,
                'materials'      => [],
                'hasCheckedIn'   => false,
            ]);
        }

        // Get latest activity for the group
        $latestActivity = $group->activities()->latest('date')->first();

        $hasCheckedIn = false;
        if ($latestActivity) {
            $hasCheckedIn = Attendance::where('user_id', $user->id)
                ->where('activity_id', $latestActivity->id)
                ->exists();
        }

        // Members with attendance status from latest activity
        $membersWithStatus = $group->members->map(function ($member) use ($latestActivity) {
            $attendance = $latestActivity
                ? Attendance::where('activity_id', $latestActivity->id)
                    ->where('user_id', $member->id)
                    ->first()
                : null;

            return [
                'id'            => $member->id,
                'name'          => $member->name,
                'status'        => $attendance?->status, // null means not checked in yet
                'attendance_id' => $attendance?->id,
                'is_approved'   => $attendance ? ($attendance->approved_by !== null) : false,
            ];
        });

        // Fetch published materials (from any Ustad who manages groups)
        $materials = Material::published()
            ->latest('published_at')
            ->take(10)
            ->get(['id', 'title', 'file_path', 'published_at'])
            ->map(fn (Material $m) => [
                'id'        => $m->id,
                'title'     => $m->title,
                'file_path' => $m->file_path ? route('materials.download', $m->id) : null,
                'date'      => $m->published_at?->format('d M Y') ?? '—',
            ]);

        return Inertia::render('Leader/Dashboard', [
            'auth' => $user->load('role'),
            'group' => [
                'id'              => $group->id,
                'name'            => $group->name,
                'ustad'           => ['id' => $group->ustad?->id, 'name' => $group->ustad?->name],
                'is_delegated'    => $group->is_delegated,
                'delegated_until' => $group->delegated_until?->toISOString(),
                'members'         => $membersWithStatus,
            ],
            'activeActivity' => $latestActivity ? [
                'id'    => $latestActivity->id,
                'topic' => $latestActivity->topic,
                'date'  => $latestActivity->date->toISOString(),
            ] : null,
            'materials'    => $materials,
            'hasCheckedIn' => $hasCheckedIn,
        ]);
    }
}
