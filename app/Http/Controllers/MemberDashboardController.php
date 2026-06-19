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

class MemberDashboardController extends Controller
{
    /**
     * Display the member (Anggota) dashboard with personal attendance, grades, and materials.
     */
    public function index(): Response
    {
        $user = Auth::user();

        // Find which group(s) this member belongs to
        $groupIds = $user->groups()->pluck('groups.id');

        // Latest active activity across member's groups
        $latestActivity = Activity::whereIn('group_id', $groupIds)
            ->with('group:id,name,ustad_id')
            ->latest('date')
            ->first();

        $ustadName = null;
        if ($latestActivity?->group?->ustad) {
            $ustadName = $latestActivity->group->ustad->name;
        }

        $hasCheckedIn = false;
        if ($latestActivity) {
            $hasCheckedIn = Attendance::where('user_id', $user->id)
                ->where('activity_id', $latestActivity->id)
                ->exists();
        }

        // Personal attendance history (latest 20 records)
        $attendances = Attendance::where('user_id', $user->id)
            ->with(['activity:id,topic,date,group_id'])
            ->with(['approver:id,name'])
            ->latest('created_at')
            ->take(20)
            ->get()
            ->map(fn (Attendance $a) => [
                'id'       => $a->id,
                'topic'    => $a->activity?->topic ?? '—',
                'date'     => $a->activity?->date?->format('d M Y') ?? '—',
                'status'   => $a->status,
                'approver' => $a->approver?->name ?? 'Menunggu Verifikasi',
            ]);

        // Personal grades history
        $grades = Grade::where('user_id', $user->id)
            ->with('ustad:id,name')
            ->latest('year')
            ->latest('month')
            ->take(12)
            ->get()
            ->map(fn (Grade $g) => [
                'id'    => $g->id,
                'month' => $this->monthName($g->month),
                'year'  => $g->year,
                'score' => number_format((float) $g->score, 2),
                'notes' => $g->notes ?? '',
            ]);

        // Available materials
        $materials = Material::published()
            ->latest('published_at')
            ->take(10)
            ->get(['id', 'title', 'file_path', 'published_at'])
            ->map(fn (Material $m) => [
                'id'        => $m->id,
                'title'     => $m->title,
                'file_path' => $m->file_path ? route('materials.download', $m->id) : null,
                'size'      => '—',
            ]);

        $group = $user->groups()->with(['ustad', 'leader'])->first();
        $mentoringGroup = null;
        if ($group) {
            $mentoringGroup = [
                'name' => $group->name,
                'ustad' => $group->ustad ? ['name' => $group->ustad->name] : null,
                'leader' => $group->leader ? ['name' => $group->leader->name] : null,
            ];
        }

        return Inertia::render('Member/Dashboard', [
            'auth' => $user->load('role'),
            'mentoringGroup' => $mentoringGroup,
            'activeActivity' => $latestActivity ? [
                'id'    => $latestActivity->id,
                'topic' => $latestActivity->topic,
                'date'  => $latestActivity->date->toISOString(),
                'ustad' => $ustadName ?? '—',
            ] : null,
            'attendances'  => $attendances,
            'grades'       => $grades,
            'materials'    => $materials,
            'hasCheckedIn' => $hasCheckedIn,
        ]);
    }

    /**
     * Convert month number to Indonesian month name.
     */
    private function monthName(int $month): string
    {
        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        return $months[$month] ?? '—';
    }
}
