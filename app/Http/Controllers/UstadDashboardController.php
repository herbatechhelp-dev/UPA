<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Attendance;
use App\Models\Grade;
use App\Models\Group;
use App\Models\Material;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class UstadDashboardController extends Controller
{
    /**
     * Display the Ustad (Pembina) dashboard with groups, attendance, and stats.
     */
    public function index(): Response
    {
        $user = Auth::user();

        // Fetch all groups managed by this Ustad
        $groups = Group::where('ustad_id', $user->id)
            ->with(['leader:id,name', 'members:id,name'])
            ->withCount('members')
            ->get()
            ->map(function (Group $group) {
                // Calculate attendance rate for the group (last 5 activities)
                $recentActivityIds = $group->activities()
                    ->latest('date')
                    ->take(5)
                    ->pluck('id');

                $totalPossible = $recentActivityIds->count() * $group->members_count;
                $presentCount  = Attendance::whereIn('activity_id', $recentActivityIds)
                    ->where('status', 'present')
                    ->count();

                $attendanceRate = $totalPossible > 0
                    ? round(($presentCount / $totalPossible) * 100) . '%'
                    : 'N/A';

                // Get latest activity attendance for members
                $latestActivity = $group->activities()->latest('date')->first();
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

                return [
                    'id'              => $group->id,
                    'name'            => $group->name,
                    'ustad_id'        => $group->ustad_id,
                    'leader_id'       => $group->leader_id,
                    'leader'          => ['id' => $group->leader?->id, 'name' => $group->leader?->name],
                    'is_delegated'    => $group->is_delegated,
                    'delegated_until' => $group->delegated_until?->toISOString(),
                    'members_count'   => $group->members_count,
                    'attendance_rate' => $attendanceRate,
                    'members'         => $membersWithStatus,
                ];
            });

        // Find the latest activity across all managed groups
        $activeActivity = Activity::whereIn('group_id', $groups->pluck('id'))
            ->latest('date')
            ->first();

        // 1. Fetch materials (indicating delete permissions)
        $materials = Material::with('ustad')
            ->get()
            ->map(fn (Material $m) => [
                'id'           => $m->id,
                'title'        => $m->title,
                'content'      => $m->content,
                'ustad_name'   => $m->ustad?->name ?? 'System',
                'file_path'    => $m->file_path,
                'published_at' => $m->published_at ? $m->published_at->format('d M Y H:i') : '—',
                'can_delete'   => $m->ustad_id === $user->id,
            ]);

        // 2. Fetch all activities for groups managed by this Ustad
        $activities = Activity::whereIn('group_id', $groups->pluck('id'))
            ->with('group')
            ->get()
            ->map(fn (Activity $a) => [
                'id'          => $a->id,
                'group_id'    => $a->group_id,
                'group_name'  => $a->group?->name ?? '—',
                'topic'       => $a->topic,
                'date'        => $a->date ? $a->date->format('Y-m-d H:i') : '',
                'date_human'  => $a->date ? $a->date->format('d M Y H:i') : '—',
                'description' => $a->description,
            ]);

        // 3. Fetch all grades given by this Ustad or for members in their groups
        $memberIds = \DB::table('group_user')
            ->whereIn('group_id', $groups->pluck('id'))
            ->pluck('user_id');

        $grades = Grade::where('ustad_id', $user->id)
            ->orWhereIn('user_id', $memberIds)
            ->with(['user', 'ustad'])
            ->get()
            ->map(fn (Grade $gr) => [
                'id'          => $gr->id,
                'user_id'     => $gr->user_id,
                'user_name'   => $gr->user?->name ?? '—',
                'ustad_id'    => $gr->ustad_id,
                'ustad_name'  => $gr->ustad?->name ?? '—',
                'month'       => $gr->month,
                'year'        => $gr->year,
                'score'       => $gr->score,
                'notes'       => $gr->notes,
                'can_delete'  => $gr->ustad_id === $user->id,
            ]);

        // 4. Fetch all attendance logs for activities in this Ustad's groups
        $attendances = Attendance::whereIn('activity_id', $activities->pluck('id'))
            ->with(['user', 'activity.group', 'approver'])
            ->get()
            ->map(fn (Attendance $at) => [
                'id'            => $at->id,
                'user_id'       => $at->user_id,
                'user_name'     => $at->user?->name ?? '—',
                'activity_id'   => $at->activity_id,
                'activity_topic'=> $at->activity?->topic ?? '—',
                'status'        => $at->status,
                'approved_by'   => $at->approver?->name ?? '—',
                'approved_at'   => $at->approved_at ? $at->approved_at->format('d M Y H:i') : '—',
            ]);

        // Statistics
        $totalMembers = $groups->sum('members_count');
        $pendingApprovals = Attendance::whereIn('activity_id', function ($q) use ($groups) {
            $q->select('id')
              ->from('activities')
              ->whereIn('group_id', $groups->pluck('id'));
        })->whereNull('approved_by')->count();

        $sharedMaterials = Material::where('ustad_id', $user->id)->count();
        $averageGrade    = Grade::where('ustad_id', $user->id)->avg('score');

        return Inertia::render('Ustad/Dashboard', [
            'auth'           => $user->load('role'),
            'groups'         => $groups,
            'materials'      => $materials,
            'activities'     => $activities,
            'grades'         => $grades,
            'attendances'    => $attendances,
            'activeActivity' => $activeActivity ? [
                'id'       => $activeActivity->id,
                'topic'    => $activeActivity->topic,
                'date'     => $activeActivity->date->toISOString(),
                'group_id' => $activeActivity->group_id,
            ] : null,
            'statistics' => [
                'totalMembers'     => (int) $totalMembers,
                'pendingApprovals' => (int) $pendingApprovals,
                'sharedMaterials'  => (int) $sharedMaterials,
                'averageGrade'     => $averageGrade ? number_format((float) $averageGrade, 1) : '0.0',
            ],
        ]);
    }
}
