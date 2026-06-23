<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Grade;
use App\Models\Group;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class AdminGroupController extends Controller
{
    /**
     * Display the Admin dashboard with groups list, ustads, potential leaders, and potential members.
     */
    public function dashboard(): Response
    {
        $groups = Group::with(['ustad', 'leader', 'members'])
            ->withCount('members')
            ->get()
            ->map(fn (Group $g) => [
                'id'              => $g->id,
                'name'            => $g->name,
                'ustad_id'        => $g->ustad_id,
                'ustad'           => $g->ustad?->name ?? '—',
                'leader_id'       => $g->leader_id,
                'leader'          => $g->leader?->name ?? '—',
                'members_count'   => $g->members_count,
                'is_delegated'    => $g->is_delegated,
                'delegated_until' => $g->delegated_until?->toISOString(),
                'members'         => $g->members->map(fn ($m) => ['id' => $m->id, 'name' => $m->name, 'email' => $m->email]),
            ]);

        $ustads = User::whereHas('role', fn ($q) => $q->where('slug', 'ustad'))
            ->get(['id', 'name']);

        $potentialLeaders = User::whereHas('role', fn ($q) => $q->whereIn('slug', ['leader', 'member']))
            ->get(['id', 'name']);

        $potentialMembers = User::whereHas('role', fn ($q) => $q->where('slug', 'member'))
            ->get(['id', 'name']);

        $materials = \App\Models\Material::with('ustad')
            ->get()
            ->map(fn (\App\Models\Material $m) => [
                'id'           => $m->id,
                'title'        => $m->title,
                'content'      => $m->content,
                'ustad_id'     => $m->ustad_id,
                'ustad_name'   => $m->ustad?->name ?? 'System',
                'file_path'    => $m->file_path,
                'published_at' => $m->published_at ? $m->published_at->format('d M Y H:i') : '—',
            ]);

        $activities = \App\Models\Activity::with('group')
            ->get()
            ->map(fn (\App\Models\Activity $a) => [
                'id'          => $a->id,
                'group_id'    => $a->group_id,
                'group_name'  => $a->group?->name ?? '—',
                'topic'       => $a->topic,
                'date'        => $a->date ? $a->date->format('Y-m-d H:i') : '',
                'date_human'  => $a->date ? $a->date->format('d M Y H:i') : '—',
                'description' => $a->description,
            ]);

        $grades = Grade::with(['user', 'ustad'])
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
            ]);

        $attendances = Attendance::with(['user', 'activity', 'approver'])
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

        $pendingUsers = User::where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn (User $u) => [
                'id'         => $u->id,
                'name'       => $u->name,
                'email'      => $u->email,
                'phone'      => $u->phone ?? '—',
                'gender'     => $u->gender ?? '—',
                'department' => $u->department ?? '—',
                'created_at' => $u->created_at ? $u->created_at->format('d M Y H:i') : '—',
            ]);

        $roles = Role::all()->map(fn (Role $r) => [
            'id'   => $r->id,
            'name' => $r->name,
            'slug' => $r->slug,
        ]);

        return Inertia::render('Admin/Dashboard', [
            'auth'             => auth()->user()->load('role'),
            'groups'           => $groups,
            'ustads'           => $ustads,
            'potentialLeaders' => $potentialLeaders,
            'potentialMembers' => $potentialMembers,
            'materials'        => $materials,
            'activities'       => $activities,
            'grades'           => $grades,
            'attendances'      => $attendances,
            'pendingUsers'     => $pendingUsers,
            'roles'            => $roles,
        ]);
    }

    /**
     * Store a newly created group in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'ustad_id'  => ['required', 'exists:users,id'],
            'leader_id' => ['required', 'exists:users,id'],
        ]);

        Group::create($validated);

        return redirect()->back()->with('success', 'Kelompok halaqah baru berhasil ditambahkan.');
    }

    /**
     * Update group assignments (ustad/leader re-assignment).
     */
    public function update(Request $request, Group $group): RedirectResponse
    {
        $validated = $request->validate([
            'name'      => ['sometimes', 'string', 'max:255'],
            'ustad_id'  => ['sometimes', 'exists:users,id'],
            'leader_id' => ['sometimes', 'exists:users,id'],
        ]);

        $group->update($validated);

        return redirect()->back()->with('success', 'Data kelompok berhasil diperbarui.');
    }

    /**
     * Attach members to a group (plotting anggota).
     */
    public function attachMembers(Request $request, Group $group): RedirectResponse
    {
        $validated = $request->validate([
            'user_ids'   => ['required', 'array'],
            'user_ids.*' => ['exists:users,id'],
        ]);

        $group->members()->sync($validated['user_ids']);

        return redirect()->back()->with('success', 'Anggota berhasil diplotting ke kelompok.');
    }

    /**
     * Remove a member from a group.
     */
    public function detachMember(Request $request, Group $group, User $user): RedirectResponse
    {
        $group->members()->detach($user->id);

        return redirect()->back()->with('success', 'Anggota berhasil dihapus dari kelompok.');
    }

    /**
     * Delete a group entirely.
     */
    public function destroy(Group $group): RedirectResponse
    {
        $group->members()->detach();
        $group->delete();

        return redirect()->back()->with('success', 'Kelompok berhasil dihapus.');
    }

    /**
     * Download rekap CSV report.
     */
    public function downloadReport(Request $request): StreamedResponse
    {
        $validated = $request->validate([
            'group_id' => ['required'],
            'month'    => ['required', 'integer', 'min:1', 'max:12'],
            'year'     => ['required', 'integer', 'min:2020', 'max:2100'],
            'type'     => ['required', 'in:attendance,grades,attendance_monthly'],
        ]);

        $type = $validated['type'];
        $month = (int) $validated['month'];
        $year = (int) $validated['year'];
        $groupId = $validated['group_id'];

        $currentUser = Auth::user();

        // Role-based authorization & group scope constraints
        if ($currentUser->isLeader()) {
            $group = Group::where('leader_id', $currentUser->id)->first();
            if (!$group) {
                abort(403, 'Anda tidak terdaftar sebagai ketua kelompok mana pun.');
            }
            $groupId = (string) $group->id;
            if ($type === 'grades') {
                abort(403, 'Ketua Kelompok tidak diizinkan mengunduh rekap nilai.');
            }
        } elseif ($currentUser->isUstad()) {
            if ($groupId !== 'all') {
                $group = Group::findOrFail($groupId);
                if ($group->ustad_id !== $currentUser->id) {
                    abort(403, 'Anda bukan pembina dari kelompok ini.');
                }
            }
        } elseif (!$currentUser->isAdmin() && !$currentUser->isSuperadmin()) {
            abort(403, 'Anda tidak memiliki hak akses untuk mengunduh laporan ini.');
        }

        $fileName = "rekap-{$type}-bulan-{$month}-{$year}.csv";

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename={$fileName}",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function () use ($type, $month, $year, $groupId, $currentUser) {
            $file = fopen('php://output', 'w');

            if ($type === 'attendance') {
                fputcsv($file, ['Nama Anggota', 'Kelompok', 'Tanggal Sesi', 'Topik Kajian', 'Status Kehadiran', 'Diverifikasi Oleh']);

                $query = Attendance::with(['user', 'activity.group', 'approver'])
                    ->whereMonth('created_at', $month)
                    ->whereYear('created_at', $year);

                if ($groupId !== 'all') {
                    $query->whereHas('activity', fn ($q) => $q->where('group_id', $groupId));
                } else {
                    if ($currentUser->isUstad()) {
                        $query->whereHas('activity.group', fn ($q) => $q->where('ustad_id', $currentUser->id));
                    }
                }

                foreach ($query->get() as $att) {
                    $statusName = match ($att->status) {
                        'present'    => 'Hadir',
                        'late'       => 'Terlambat',
                        'sick'       => 'Sakit',
                        'permission' => 'I',
                        'absent'     => 'Alpa',
                        default      => $att->status
                    };

                    fputcsv($file, [
                        $att->user?->name ?? '—',
                        $att->activity?->group?->name ?? '—',
                        $att->activity?->date?->format('d-m-Y') ?? '—',
                        $att->activity?->topic ?? '—',
                        $statusName,
                        $att->approver?->name ?? '—',
                    ]);
                }
            } elseif ($type === 'attendance_monthly') {
                if ($groupId === 'all') {
                    fputcsv($file, ['No', 'Nama Anggota', 'Kelompok', 'Total Sesi', 'Hadir', 'Sakit', 'Izin', 'Alpa', 'Persentase Kehadiran']);

                    $groupsQuery = Group::with('members');
                    if ($currentUser->isUstad()) {
                        $groupsQuery->where('ustad_id', $currentUser->id);
                    }
                    $groups = $groupsQuery->get();
                    $no = 1;
                    foreach ($groups as $group) {
                        $activities = \App\Models\Activity::where('group_id', $group->id)
                            ->whereMonth('date', $month)
                            ->whereYear('date', $year)
                            ->get();

                        $activityIds = $activities->pluck('id')->toArray();
                        $totalActivities = count($activityIds);

                        foreach ($group->members as $member) {
                            $attendances = Attendance::where('user_id', $member->id)
                                ->whereIn('activity_id', $activityIds)
                                ->get();

                            $present = $attendances->whereIn('status', ['present', 'late'])->count();
                            $sick = $attendances->where('status', 'sick')->count();
                            $permission = $attendances->where('status', 'permission')->count();
                            $absent = $attendances->where('status', 'absent')->count();

                            $percentage = $totalActivities > 0 ? round(($present / $totalActivities) * 100, 2) : 0;

                            fputcsv($file, [
                                $no++,
                                $member->name,
                                $group->name,
                                $totalActivities,
                                $present,
                                $sick,
                                $permission,
                                $absent,
                                $percentage . '%'
                            ]);
                        }
                    }
                } else {
                    $group = Group::with('members')->findOrFail($groupId);
                    if ($currentUser->isLeader() && $group->leader_id !== $currentUser->id) {
                        abort(403, 'Anda bukan ketua kelompok dari kelompok ini.');
                    }
                    if ($currentUser->isUstad() && $group->ustad_id !== $currentUser->id) {
                        abort(403, 'Anda bukan pembina dari kelompok ini.');
                    }

                    $activities = \App\Models\Activity::where('group_id', $groupId)
                        ->whereMonth('date', $month)
                        ->whereYear('date', $year)
                        ->orderBy('date', 'asc')
                        ->get();

                    $headersRow = ['No', 'Nama Anggota', 'Kelompok'];
                    foreach ($activities as $idx => $act) {
                        $dateStr = $act->date->format('d/m/Y');
                        $headersRow[] = "P" . ($idx + 1) . " (" . $dateStr . " - " . $act->topic . ")";
                    }
                    $headersRow = array_merge($headersRow, ['Hadir', 'Sakit', 'Izin', 'Alpa', 'Persentase Kehadiran']);
                    fputcsv($file, $headersRow);

                    $no = 1;
                    $activityIds = $activities->pluck('id')->toArray();
                    $totalActivities = count($activityIds);

                    foreach ($group->members as $member) {
                        $attendances = Attendance::where('user_id', $member->id)
                            ->whereIn('activity_id', $activityIds)
                            ->get()
                            ->keyBy('activity_id');

                        $row = [
                            $no++,
                            $member->name,
                            $group->name,
                        ];

                        $present = 0;
                        $sick = 0;
                        $permission = 0;
                        $absent = 0;

                        foreach ($activities as $act) {
                            $att = $attendances->get($act->id);
                            if ($att) {
                                $statusName = match ($att->status) {
                                     'present'    => 'H',
                                     'late'       => 'T',
                                     'sick'       => 'S',
                                     'permission' => 'I',
                                     'absent'     => 'A',
                                     default      => $att->status
                                 };
 
                                 if ($att->status === 'present' || $att->status === 'late') $present++;
                                 elseif ($att->status === 'sick') $sick++;
                                 elseif ($att->status === 'permission') $permission++;
                                 elseif ($att->status === 'absent') $absent++;

                                $row[] = $statusName;
                            } else {
                                $row[] = '—';
                            }
                        }

                        $percentage = $totalActivities > 0 ? round(($present / $totalActivities) * 100, 2) : 0;

                        $row[] = $present;
                        $row[] = $sick;
                        $row[] = $permission;
                        $row[] = $absent;
                        $row[] = $percentage . '%';

                        fputcsv($file, $row);
                    }
                }
            } else {
                fputcsv($file, ['Nama Anggota', 'Kelompok', 'Bulan', 'Tahun', 'Skor', 'Catatan Perkembangan', 'Ustad Penilai']);

                $query = Grade::with(['user', 'ustad'])
                    ->where('month', $month)
                    ->where('year', $year);

                if ($groupId !== 'all') {
                    $query->whereHas('user.groups', fn ($q) => $q->where('groups.id', $groupId));
                } else {
                    if ($currentUser->isUstad()) {
                        $query->whereHas('user.groups', fn ($q) => $q->where('groups.ustad_id', $currentUser->id));
                    }
                }

                foreach ($query->get() as $gr) {
                    $groupName = $gr->user?->groups->first()?->name ?? '—';
                    fputcsv($file, [
                        $gr->user?->name ?? '—',
                        $groupName,
                        $gr->month,
                        $gr->year,
                        $gr->score,
                        $gr->notes ?? '—',
                        $gr->ustad?->name ?? '—',
                    ]);
                }
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Get monthly attendance recap data as JSON for live web-view preview.
     */
    public function getRecapData(Request $request): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'group_id' => ['required'],
            'month'    => ['required', 'integer', 'min:1', 'max:12'],
            'year'     => ['required', 'integer', 'min:2020', 'max:2100'],
        ]);

        $month = (int) $validated['month'];
        $year = (int) $validated['year'];
        $groupId = $validated['group_id'];

        $currentUser = Auth::user();

        // Role-based authorization & group scope constraints
        if ($currentUser->isLeader()) {
            $group = Group::where('leader_id', $currentUser->id)->first();
            if (!$group) {
                return response()->json(['error' => 'Anda tidak terdaftar sebagai ketua kelompok mana pun.'], 403);
            }
            $groupId = (string) $group->id;
        } elseif ($currentUser->isUstad()) {
            if ($groupId !== 'all') {
                $group = Group::findOrFail($groupId);
                if ($group->ustad_id !== $currentUser->id) {
                    return response()->json(['error' => 'Anda bukan pembina dari kelompok ini.'], 403);
                }
            }
        } elseif (!$currentUser->isAdmin() && !$currentUser->isSuperadmin()) {
            return response()->json(['error' => 'Anda tidak memiliki hak akses untuk melihat laporan ini.'], 403);
        }

        if ($groupId === 'all') {
            $groupsQuery = Group::with('members');
            if ($currentUser->isUstad()) {
                $groupsQuery->where('ustad_id', $currentUser->id);
            }
            $groups = $groupsQuery->get();
            $rows = [];
            $no = 1;

            foreach ($groups as $group) {
                $activities = \App\Models\Activity::where('group_id', $group->id)
                    ->whereMonth('date', $month)
                    ->whereYear('date', $year)
                    ->get();

                $activityIds = $activities->pluck('id')->toArray();
                $totalActivities = count($activityIds);

                foreach ($group->members as $member) {
                    $attendances = Attendance::where('user_id', $member->id)
                        ->whereIn('activity_id', $activityIds)
                        ->get();

                     $present = $attendances->whereIn('status', ['present', 'late'])->count();
                     $sick = $attendances->where('status', 'sick')->count();
                     $permission = $attendances->where('status', 'permission')->count();
                     $absent = $attendances->where('status', 'absent')->count();

                    $percentage = $totalActivities > 0 ? round(($present / $totalActivities) * 100, 2) : 0;

                    $rows[] = [
                        'no' => $no++,
                        'member_name' => $member->name,
                        'group_name' => $group->name,
                        'total_sessions' => $totalActivities,
                        'present' => $present,
                        'sick' => $sick,
                        'permission' => $permission,
                        'absent' => $absent,
                        'percentage' => $percentage . '%'
                    ];
                }
            }

            return response()->json([
                'type' => 'summary',
                'headers' => ['No', 'Nama Anggota', 'Kelompok', 'Total Sesi', 'Hadir', 'Sakit', 'Izin', 'Alpa', 'Persentase Kehadiran'],
                'rows' => $rows
            ]);
        } else {
            $group = Group::with('members')->findOrFail($groupId);
            if ($currentUser->isLeader() && $group->leader_id !== $currentUser->id) {
                return response()->json(['error' => 'Anda bukan ketua kelompok dari kelompok ini.'], 403);
            }
            if ($currentUser->isUstad() && $group->ustad_id !== $currentUser->id) {
                return response()->json(['error' => 'Anda bukan pembina dari kelompok ini.'], 403);
            }

            $activities = \App\Models\Activity::where('group_id', $groupId)
                ->whereMonth('date', $month)
                ->whereYear('date', $year)
                ->orderBy('date', 'asc')
                ->get();

            $activityIds = $activities->pluck('id')->toArray();
            $totalActivities = count($activityIds);

            $formattedActivities = [];
            foreach ($activities as $idx => $act) {
                $formattedActivities[] = [
                    'id' => $act->id,
                    'label' => 'P' . ($idx + 1),
                    'date' => $act->date->format('d/m/Y'),
                    'topic' => $act->topic
                ];
            }

            $rows = [];
            $no = 1;

            foreach ($group->members as $member) {
                $attendances = Attendance::where('user_id', $member->id)
                    ->whereIn('activity_id', $activityIds)
                    ->get()
                    ->keyBy('activity_id');

                $sessionStatuses = [];
                $present = 0;
                $sick = 0;
                $permission = 0;
                $absent = 0;

                foreach ($activities as $act) {
                    $att = $attendances->get($act->id);
                    if ($att) {
                        $statusName = match ($att->status) {
                            'present'    => 'H',
                            'late'       => 'T',
                            'sick'       => 'S',
                            'permission' => 'I',
                            'absent'     => 'A',
                            default      => $att->status
                        };
                        if ($att->status === 'present' || $att->status === 'late') $present++;
                        elseif ($att->status === 'sick') $sick++;
                        elseif ($att->status === 'permission') $permission++;
                        elseif ($att->status === 'absent') $absent++;
                    } else {
                        $statusName = '—';
                    }
                    $sessionStatuses[$act->id] = $statusName;
                }

                $percentage = $totalActivities > 0 ? round(($present / $totalActivities) * 100, 2) : 0;

                $rows[] = [
                    'no' => $no++,
                    'member_name' => $member->name,
                    'group_name' => $group->name,
                    'sessions' => $sessionStatuses,
                    'present' => $present,
                    'sick' => $sick,
                    'permission' => $permission,
                    'absent' => $absent,
                    'percentage' => $percentage . '%'
                ];
            }

            return response()->json([
                'type' => 'detail',
                'activities' => $formattedActivities,
                'rows' => $rows
            ]);
        }
    }

    /**
     * Store a newly uploaded Material.
     */
    public function storeMaterial(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title'    => ['required', 'string', 'max:255'],
            'content'  => ['nullable', 'string'],
            'ustad_id' => ['required', 'exists:users,id'],
            'file'     => ['nullable', 'file', 'mimes:pdf,doc,docx,ppt,pptx,zip', 'max:25600'],
        ]);

        $filePath = null;
        if ($request->hasFile('file')) {
            $uploadedFile = $request->file('file');
            if (!$uploadedFile->isValid() || empty($uploadedFile->getPathname())) {
                return redirect()->back()->withErrors(['file' => 'File tidak valid atau gagal diunggah. Periksa ukuran file (maks 25MB).'])->withInput();
            }
            try {
                $storedPath = $uploadedFile->store('materials', 'public');
                if (!$storedPath) {
                    throw new \RuntimeException('Penyimpanan file gagal.');
                }
                $filePath = $storedPath;
            } catch (\Exception $e) {
                Log::error('Material upload failed: ' . $e->getMessage());
                return redirect()->back()->withErrors(['file' => 'Gagal mengunggah file: ' . $e->getMessage()])->withInput();
            }
        }

        \App\Models\Material::create([
            'title'        => $validated['title'],
            'content'      => $validated['content'] ?? null,
            'ustad_id'     => $validated['ustad_id'],
            'file_path'    => $filePath,
            'published_at' => \Illuminate\Support\Carbon::now(),
        ]);

        return redirect()->back()->with('success', 'Materi kajian berhasil ditambahkan.');
    }

    /**
     * Update the specified Material.
     */
    public function updateMaterial(Request $request, \App\Models\Material $material): RedirectResponse
    {
        $validated = $request->validate([
            'title'    => ['required', 'string', 'max:255'],
            'content'  => ['nullable', 'string'],
            'ustad_id' => ['required', 'exists:users,id'],
            'file'     => ['nullable', 'file', 'mimes:pdf,doc,docx,ppt,pptx,zip', 'max:25600'],
        ]);

        $updateData = [
            'title'    => $validated['title'],
            'content'  => $validated['content'] ?? null,
            'ustad_id' => $validated['ustad_id'],
        ];

        if ($request->hasFile('file')) {
            $uploadedFile = $request->file('file');
            if (!$uploadedFile->isValid() || empty($uploadedFile->getPathname())) {
                return redirect()->back()->withErrors(['file' => 'File tidak valid atau gagal diunggah. Periksa ukuran file (maks 25MB).'])->withInput();
            }
            try {
                if ($material->file_path) {
                    Storage::disk('public')->delete($material->file_path);
                }
                $storedPath = $uploadedFile->store('materials', 'public');
                if (!$storedPath) {
                    throw new \RuntimeException('Penyimpanan file gagal.');
                }
                $updateData['file_path'] = $storedPath;
            } catch (\Exception $e) {
                Log::error('Material update upload failed: ' . $e->getMessage());
                return redirect()->back()->withErrors(['file' => 'Gagal mengunggah file: ' . $e->getMessage()])->withInput();
            }
        }

        $material->update($updateData);

        return redirect()->back()->with('success', 'Materi kajian berhasil diperbarui.');
    }

    /**
     * Remove the specified Material.
     */
    public function destroyMaterial(\App\Models\Material $material): RedirectResponse
    {
        if ($material->file_path) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($material->file_path);
        }

        $material->delete();

        return redirect()->back()->with('success', 'Materi kajian berhasil dihapus.');
    }

    /**
     * Store a newly created Activity.
     */
    public function storeActivity(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'group_id'    => ['required', 'exists:groups,id'],
            'date'        => ['required', 'date'],
            'topic'       => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);

        \App\Models\Activity::create($validated);

        return redirect()->back()->with('success', 'Sesi kajian baru berhasil dibuat.');
    }

    /**
     * Update the specified Activity.
     */
    public function updateActivity(Request $request, \App\Models\Activity $activity): RedirectResponse
    {
        $validated = $request->validate([
            'date'        => ['required', 'date'],
            'topic'       => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);

        $activity->update($validated);

        return redirect()->back()->with('success', 'Sesi kajian berhasil diperbarui.');
    }

    /**
     * Remove the specified Activity.
     */
    public function destroyActivity(\App\Models\Activity $activity): RedirectResponse
    {
        $activity->delete();

        return redirect()->back()->with('success', 'Sesi kajian berhasil dihapus.');
    }

    /**
     * Store monthly grade for a member.
     */
    public function storeGrade(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'user_id'  => ['required', 'exists:users,id'],
            'ustad_id' => ['required', 'exists:users,id'],
            'month'    => ['required', 'integer', 'min:1', 'max:12'],
            'year'     => ['required', 'integer', 'min:2020', 'max:2100'],
            'score'    => ['required', 'numeric', 'min:0', 'max:100'],
            'notes'    => ['nullable', 'string', 'max:2000'],
        ]);

        Grade::updateOrCreate(
            [
                'user_id' => $validated['user_id'],
                'month'   => $validated['month'],
                'year'    => $validated['year'],
            ],
            [
                'ustad_id' => $validated['ustad_id'],
                'score'    => $validated['score'],
                'notes'    => $validated['notes'] ?? null,
            ]
        );

        return redirect()->back()->with('success', 'Nilai bulanan berhasil disimpan.');
    }

    /**
     * Remove the specified Grade.
     */
    public function destroyGrade(Grade $grade): RedirectResponse
    {
        $grade->delete();

        return redirect()->back()->with('success', 'Nilai bulanan berhasil dihapus.');
    }

    /**
     * Approve attendance bulk for an activity.
     */
    public function approveAttendance(Request $request, \App\Models\Activity $activity): RedirectResponse
    {
        $validated = $request->validate([
            'attendances'           => ['required', 'array'],
            'attendances.*.user_id' => ['required', 'exists:users,id'],
            'attendances.*.status'   => ['required', 'in:present,absent,sick,permission'],
        ]);

        foreach ($validated['attendances'] as $attData) {
            Attendance::updateOrCreate(
                [
                    'activity_id' => $activity->id,
                    'user_id'     => $attData['user_id'],
                ],
                [
                    'status'      => $attData['status'],
                    'approved_by' => \Illuminate\Support\Facades\Auth::id(),
                    'approved_at' => \Illuminate\Support\Carbon::now(),
                ]
            );
        }

        return redirect()->back()->with('success', 'Kehadiran berhasil disimpan & disetujui.');
    }

    /**
     * Remove the specified Attendance record.
     */
    public function destroyAttendance(Attendance $attendance): RedirectResponse
    {
        $attendance->delete();

        return redirect()->back()->with('success', 'Catatan absensi berhasil dihapus.');
    }

    /**
     * Approve a pending user and assign role.
     */
    public function approveUser(User $user, Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'role_id' => ['required', 'exists:roles,id'],
        ]);

        $user->update([
            'status'  => 'approved',
            'role_id' => $validated['role_id'],
        ]);

        return redirect()->back()->with('success', "User {$user->name} berhasil disetujui.");
    }

    /**
     * Reject a pending user registration.
     */
    public function rejectUser(User $user): RedirectResponse
    {
        $user->update(['status' => 'rejected']);

        return redirect()->back()->with('success', "Pendaftaran {$user->name} telah ditolak.");
    }

    /**
     * Import users from Excel/CSV file.
     */
    public function importUsers(Request $request): RedirectResponse
    {
        $currentUser = Auth::user();
        if (!$currentUser->isAdmin() && !$currentUser->isSuperadmin()) {
            abort(403, 'Hanya Admin atau Superadmin yang dapat mengimpor data user.');
        }

        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:5120'], // max 5MB
        ]);

        $file = $request->file('file');
        $path = $file->getRealPath();

        $handle = fopen($path, 'r');
        if ($handle === false) {
            return redirect()->back()->with('error', 'Gagal membuka file CSV.');
        }

        // Auto-detect delimiter: comma or semicolon
        $firstLine = fgets($handle);
        rewind($handle);
        $delimiter = (strpos($firstLine, ';') !== false) ? ';' : ',';

        // Skip header row
        $header = fgetcsv($handle, 1000, $delimiter);

        $rowCount = 0;
        $successCount = 0;
        $errors = [];

        while (($row = fgetcsv($handle, 1000, $delimiter)) !== false) {
            $rowCount++;

            // Skip empty rows
            if (empty($row) || count($row) < 2) {
                continue;
            }

            $name = trim($row[0] ?? '');
            $email = trim($row[1] ?? '');
            $phone = isset($row[2]) ? trim($row[2]) : null;
            $gender = isset($row[3]) ? strtolower(trim($row[3])) : null;
            $department = isset($row[4]) ? trim($row[4]) : null;
            $roleSlug = isset($row[5]) ? strtolower(trim($row[5])) : 'member';
            $groupName = isset($row[6]) ? trim($row[6]) : null;

            if (empty($name) || empty($email)) {
                $errors[] = "Baris {$rowCount}: Nama Lengkap dan Email wajib diisi.";
                continue;
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = "Baris {$rowCount}: Format email '{$email}' tidak valid.";
                continue;
            }

            // Check if email already exists
            if (User::where('email', $email)->exists()) {
                $errors[] = "Baris {$rowCount}: Email '{$email}' sudah terdaftar.";
                continue;
            }

            // Normalize gender
            if ($gender !== 'ikhwan' && $gender !== 'akhwat') {
                $gender = null;
            }

            // Resolve role
            $role = Role::where('slug', $roleSlug)->first();
            if (!$role) {
                $role = Role::where('slug', 'member')->first();
            }
            $roleId = $role ? $role->id : null;

            try {
                $user = User::create([
                    'name' => $name,
                    'email' => $email,
                    'phone' => $phone,
                    'gender' => $gender,
                    'department' => $department,
                    'role_id' => $roleId,
                    'status' => 'approved',
                    'password' => \Illuminate\Support\Facades\Hash::make('password123'),
                ]);

                // Attach to group if specified
                if (!empty($groupName)) {
                    $group = Group::where('name', $groupName)->first();
                    if ($group) {
                        $group->members()->attach($user->id);
                    } else {
                        $errors[] = "Baris {$rowCount}: User '{$name}' berhasil diimpor, tetapi kelompok '{$groupName}' tidak ditemukan.";
                    }
                }

                $successCount++;
            } catch (\Exception $e) {
                $errors[] = "Baris {$rowCount}: Gagal menyimpan user '{$name}'. Error: " . $e->getMessage();
            }
        }

        fclose($handle);

        $msg = "Berhasil mengimpor {$successCount} user.";
        if (count($errors) > 0) {
            $msg .= " Terdapat beberapa catatan: " . implode(" | ", array_slice($errors, 0, 5));
            if (count($errors) > 5) {
                $msg .= " (dan " . (count($errors) - 5) . " kesalahan lainnya)";
            }
            return redirect()->back()->with('warning', $msg);
        }

        return redirect()->back()->with('success', $msg);
    }
}
