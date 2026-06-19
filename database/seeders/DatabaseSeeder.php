<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\Group;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Seed Roles
        $superadminRole = Role::create(['name' => 'Superadmin', 'slug' => 'superadmin']);
        $adminRole = Role::create(['name' => 'Admin', 'slug' => 'admin']);
        $ustadRole = Role::create(['name' => 'Ustad (Pembina)', 'slug' => 'ustad']);
        $leaderRole = Role::create(['name' => 'Ketua Kelompok', 'slug' => 'leader']);
        $memberRole = Role::create(['name' => 'User (Anggota)', 'slug' => 'member']);

        // 2. Seed Mock Users
        $superadmin = User::create([
            'name' => 'Superadmin System',
            'email' => 'superadmin@upa.com',
            'password' => Hash::make('password123'),
            'role_id' => $superadminRole->id,
        ]);

        $admin = User::create([
            'name' => 'Admin Cabang',
            'email' => 'admin@upa.com',
            'password' => Hash::make('password123'),
            'role_id' => $adminRole->id,
        ]);

        // Ustad
        $ustad1 = User::create([
            'name' => 'Ustad Abu Hurairah',
            'email' => 'ustad.abu@upa.com',
            'password' => Hash::make('password123'),
            'role_id' => $ustadRole->id,
        ]);

        $ustad2 = User::create([
            'name' => 'Ustad Al-Fadhil',
            'email' => 'ustad.fadhil@upa.com',
            'password' => Hash::make('password123'),
            'role_id' => $ustadRole->id,
        ]);

        // Leaders (Ketua Kelompok)
        $leader1 = User::create([
            'name' => 'Akh Ahmad Bukhari',
            'email' => 'ahmad.bukhari@upa.com',
            'password' => Hash::make('password123'),
            'role_id' => $leaderRole->id,
        ]);

        $leader2 = User::create([
            'name' => 'Akh Zayd bin Harithah',
            'email' => 'zayd.harithah@upa.com',
            'password' => Hash::make('password123'),
            'role_id' => $leaderRole->id,
        ]);

        // 3. Create Mentoring Groups (Groups)
        $group1 = Group::create([
            'name' => 'Halaqah Al-Fatih',
            'ustad_id' => $ustad1->id,
            'leader_id' => $leader1->id,
            'is_delegated' => true,
            'delegated_until' => Carbon::now()->addDays(7),
        ]);

        $group2 = Group::create([
            'name' => 'Halaqah Ar-Rayyan',
            'ustad_id' => $ustad1->id,
            'leader_id' => $leader2->id,
            'is_delegated' => false,
            'delegated_until' => null,
        ]);

        // 4. Seed Members and Associate them with Groups
        $membersData = [
            ['name' => 'Akh Salman Al-Farisi', 'email' => 'salman@upa.com'],
            ['name' => 'Akh Bilal bin Rabah', 'email' => 'bilal@upa.com'],
            ['name' => 'Akh Zubair bin Awwam', 'email' => 'zubair@upa.com'],
            ['name' => 'Akh Talhah bin Ubaidillah', 'email' => 'talhah@upa.com'],
            ['name' => 'Akh Abdurrahman bin Auf', 'email' => 'abdurrahman@upa.com'],
            ['name' => 'Akh Sa\'ad bin Abi Waqqas', 'email' => 'saad@upa.com'],
            ['name' => 'Akh Said bin Zayd', 'email' => 'said@upa.com'],
            ['name' => 'Akh Abu Ubaidah', 'email' => 'abu.ubaidah@upa.com'],
        ];

        foreach ($membersData as $member) {
            $user = User::create([
                'name' => $member['name'],
                'email' => $member['email'],
                'password' => Hash::make('password123'),
                'role_id' => $memberRole->id,
            ]);
            
            // Attach to Halaqah Al-Fatih
            $group1->members()->attach($user->id);
        }

        $membersDataGroup2 = [
            ['name' => 'Akh Mus\'ab bin Umair', 'email' => 'musab@upa.com'],
            ['name' => 'Akh Mu\'adh bin Jabal', 'email' => 'muadh@upa.com'],
            ['name' => 'Akh Usamah bin Zayd', 'email' => 'usamah@upa.com'],
            ['name' => 'Akh Khabbab bin Al-Aratt', 'email' => 'khabbab@upa.com'],
            ['name' => 'Akh Ammar bin Yasir', 'email' => 'ammar@upa.com'],
            ['name' => 'Akh Suhayb Ar-Rumi', 'email' => 'suhayb@upa.com'],
        ];

        foreach ($membersDataGroup2 as $member) {
            $user = User::create([
                'name' => $member['name'],
                'email' => $member['email'],
                'password' => Hash::make('password123'),
                'role_id' => $memberRole->id,
            ]);
            
            // Attach to Halaqah Ar-Rayyan
            $group2->members()->attach($user->id);
        }

        // 5. Seed Activities (Sessions)
        Activity::create([
            'group_id' => $group1->id,
            'date' => Carbon::now()->subDays(2),
            'topic' => 'Tafsir Surah Al-Kahf Ayat 1-10',
            'description' => 'Membahas tentang keutamaan menghafal 10 ayat pertama Surah Al-Kahf dan perlindungan dari fitnah Dajjal.',
        ]);

        Activity::create([
            'group_id' => $group2->id,
            'date' => Carbon::now()->subDays(1),
            'topic' => 'Fadilah Menuntut Ilmu',
            'description' => 'Mengkaji hadits-hadits tentang kewajiban dan pahala menuntut ilmu syar\'i.',
        ]);

        // 6. Seed Default Settings
        \App\Models\Setting::create([
            'app_title' => 'Unit Pembinaan Anggota',
            'logo_path' => null,
            'favicon_path' => null,
        ]);
    }
}
