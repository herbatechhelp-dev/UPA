<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Attendance;
use App\Models\Grade;
use App\Models\Group;
use App\Models\Material;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class SuperadminDashboardController extends Controller
{
    /**
     * Display the Superadmin control center dashboard with all entities loaded.
     */
    public function index(): Response
    {
        $roles = Role::all()->map(fn (Role $r) => [
            'id'   => $r->id,
            'name' => $r->name,
            'slug' => $r->slug,
            'desc' => $this->getRoleDescription($r->slug),
        ]);

        $users = User::with(['role', 'groups.ustad', 'groups.leader', 'groupsManaged', 'groupsLed'])
            ->get()
            ->map(fn (User $u) => [
                'id'         => $u->id,
                'name'       => $u->name,
                'email'      => $u->email,
                'role_id'    => $u->role_id,
                'role_name'  => $u->role?->name ?? '—',
                'role_slug'  => $u->role?->slug ?? '—',
                'group_name' => $u->groups->first()?->name ?? ($u->groupsManaged->first()?->name ? $u->groupsManaged->first()?->name . ' (Pembina)' : ($u->groupsLed->first()?->name ? $u->groupsLed->first()?->name . ' (Ketua)' : '—')),
                'ustad_name' => $u->groups->first()?->ustad?->name ?? '—',
                'leader_name'=> $u->groups->first()?->leader?->name ?? '—',
            ]);

        $groups = Group::with(['ustad', 'leader', 'members'])
            ->withCount('members')
            ->get()
            ->map(fn (Group $g) => [
                'id'            => $g->id,
                'name'          => $g->name,
                'ustad_id'      => $g->ustad_id,
                'ustad'         => $g->ustad?->name ?? '—',
                'leader_id'     => $g->leader_id,
                'leader'        => $g->leader?->name ?? '—',
                'members_count' => $g->members_count,
                'members'       => $g->members->map(fn ($m) => ['id' => $m->id, 'name' => $m->name, 'email' => $m->email]),
            ]);

        $ustads = User::whereHas('role', fn ($q) => $q->where('slug', 'ustad'))->get(['id', 'name']);
        $potentialLeaders = User::whereHas('role', fn ($q) => $q->whereIn('slug', ['leader', 'member']))->get(['id', 'name']);
        $potentialMembers = User::whereHas('role', fn ($q) => $q->where('slug', 'member'))->get(['id', 'name']);

        $materials = Material::with('ustad')
            ->get()
            ->map(fn (Material $m) => [
                'id'           => $m->id,
                'title'        => $m->title,
                'content'      => $m->content,
                'ustad_name'   => $m->ustad?->name ?? 'System',
                'file_path'    => $m->file_path,
                'published_at' => $m->published_at ? $m->published_at->format('d M Y H:i') : '—',
            ]);

        $activities = Activity::with('group')
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

        return Inertia::render('Superadmin/Dashboard', [
            'auth'             => Auth::user()->load('role'),
            'roles'            => $roles,
            'users'            => $users,
            'groups'           => $groups,
            'ustads'           => $ustads,
            'potentialLeaders' => $potentialLeaders,
            'potentialMembers' => $potentialMembers,
            'materials'        => $materials,
            'activities'       => $activities,
            'grades'           => $grades,
            'attendances'      => $attendances,
            'systemStats'      => [
                'totalUsers'      => User::count(),
                'totalGroups'     => Group::count(),
                'totalMaterials'  => Material::count(),
                'totalActivities' => Activity::count(),
                'activeSessions'  => 1, // mock placeholder
                'systemStatus'    => 'Optimal',
                'backupStatus'    => 'Up-to-date (Hari ini 04:00)',
            ],
        ]);
    }

    /**
     * Store a newly created User.
     */
    public function storeUser(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'role_id'  => ['required', 'exists:roles,id'],
        ]);

        User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role_id'  => $validated['role_id'],
        ]);

        return redirect()->back()->with('success', 'User baru berhasil dibuat.');
    }

    /**
     * Update the specified User.
     */
    public function updateUser(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'password' => ['nullable', 'string', 'min:6'],
            'role_id'  => ['required', 'exists:roles,id'],
        ]);

        $updateData = [
            'name'    => $validated['name'],
            'email'   => $validated['email'],
            'role_id' => $validated['role_id'],
        ];

        if (!empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $user->update($updateData);

        return redirect()->back()->with('success', 'Data user berhasil diperbarui.');
    }

    /**
     * Remove the specified User.
     */
    public function destroyUser(User $user): RedirectResponse
    {
        if ($user->id === Auth::id()) {
            return redirect()->back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        // Detach relations
        $user->groups()->detach();
        $user->delete();

        return redirect()->back()->with('success', 'User berhasil dihapus.');
    }

    /**
     * Store a newly created Group.
     */
    public function storeGroup(Request $request): RedirectResponse
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
     * Update the specified Group.
     */
    public function updateGroup(Request $request, Group $group): RedirectResponse
    {
        $validated = $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'ustad_id'  => ['required', 'exists:users,id'],
            'leader_id' => ['required', 'exists:users,id'],
        ]);

        $group->update($validated);

        return redirect()->back()->with('success', 'Kelompok halaqah berhasil diperbarui.');
    }

    /**
     * Remove the specified Group.
     */
    public function destroyGroup(Group $group): RedirectResponse
    {
        $group->members()->detach();
        $group->delete();

        return redirect()->back()->with('success', 'Kelompok halaqah berhasil dihapus.');
    }

    /**
     * Attach members to a group.
     */
    public function attachMembers(Request $request, Group $group): RedirectResponse
    {
        $validated = $request->validate([
            'user_ids'   => ['required', 'array'],
            'user_ids.*' => ['exists:users,id'],
        ]);

        $group->members()->syncWithoutDetaching($validated['user_ids']);

        return redirect()->back()->with('success', 'Anggota berhasil ditambahkan ke kelompok.');
    }

    /**
     * Detach a member from a group.
     */
    public function detachMember(Request $request, Group $group, User $user): RedirectResponse
    {
        $group->members()->detach($user->id);

        return redirect()->back()->with('success', 'Anggota berhasil dihapus dari kelompok.');
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
            $filePath = $request->file('file')->store('materials', 'public');
        }

        Material::create([
            'title'        => $validated['title'],
            'content'      => $validated['content'] ?? null,
            'ustad_id'     => $validated['ustad_id'],
            'file_path'    => $filePath,
            'published_at' => Carbon::now(),
        ]);

        return redirect()->back()->with('success', 'Materi kajian berhasil ditambahkan.');
    }

    /**
     * Remove the specified Material.
     */
    public function destroyMaterial(Material $material): RedirectResponse
    {
        if ($material->file_path) {
            Storage::disk('public')->delete($material->file_path);
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

        Activity::create($validated);

        return redirect()->back()->with('success', 'Sesi kajian baru berhasil dibuat.');
    }

    /**
     * Update the specified Activity.
     */
    public function updateActivity(Request $request, Activity $activity): RedirectResponse
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
    public function destroyActivity(Activity $activity): RedirectResponse
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
    public function approveAttendance(Request $request, Activity $activity): RedirectResponse
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
                    'approved_by' => Auth::id(),
                    'approved_at' => Carbon::now(),
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
     * Update system settings (App Title, Logo, and Favicon).
     */
    public function updateSettings(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'app_title' => ['required', 'string', 'max:255'],
            'logo'      => ['nullable', 'image', 'mimes:png,jpg,jpeg,svg', 'max:2048'],
            'favicon'   => ['nullable', 'file', 'mimes:ico,png,png-ico,x-icon,svg', 'max:1024'],
        ]);

        $settings = \App\Models\Setting::firstOrCreate([]);

        $updateData = [
            'app_title' => $validated['app_title'],
        ];

        if ($request->hasFile('logo')) {
            if ($settings->logo_path) {
                Storage::disk('public')->delete($settings->logo_path);
            }
            $updateData['logo_path'] = $request->file('logo')->store('settings', 'public');
        }

        if ($request->hasFile('favicon')) {
            if ($settings->favicon_path) {
                Storage::disk('public')->delete($settings->favicon_path);
            }
            $updateData['favicon_path'] = $request->file('favicon')->store('settings', 'public');
        }

        $settings->update($updateData);

        return redirect()->back()->with('success', 'Pengaturan sistem berhasil diperbarui.');
    }

    /**
     * Get human-readable description for each role.
     */
    private function getRoleDescription(string $slug): string
    {
        return match ($slug) {
            'superadmin' => 'Pemegang kontrol utama sistem, konfigurasi server, dan audit log.',
            'admin'      => 'Pengelola pembagian kelompok, plotting Ustad, plotting Ketua Kelompok, & unduh rekap.',
            'ustad'      => 'Pembina halaqah mentoring, pembuat absensi, pemberi nilai bulanan, & pengunggah materi.',
            'leader'     => 'Leader mutarabbi, pengakses materi, & penerima delegasi absensi jika Ustad berhalangan.',
            'member'     => 'Mutarabbi/peserta mentoring, melakukan check-in mandiri, & mengunduh materi kajian.',
            default      => 'Role tidak dikenali.',
        };
    }
}
