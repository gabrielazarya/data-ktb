<?php

namespace App\Http\Controllers;

use App\Models\Kampus;
use App\Models\KelompokPemuridan;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(): RedirectResponse
    {
        return redirect()->route($this->routeForRole((string) Auth::user()->role));
    }

    public function superadmin(): View
    {
        return $this->show('super_admin');
    }

    public function admin(): View
    {
        return $this->show('admin');
    }

    public function pkk(): View
    {
        return $this->show('pkk');
    }

    public function akk(): View
    {
        return $this->show('akk');
    }

    public function kampus(): View
    {
        return $this->showAdminSection('kampus');
    }

    public function pengguna(): View
    {
        return $this->showAdminSection('pengguna');
    }

    public function pohon(): View
    {
        return $this->showAdminSection('pohon');
    }

    private function show(string $dashboardRole): View
    {
        /** @var User $user */
        $user = Auth::user();

        abort_if($user->role !== $dashboardRole, 403);

        return view($this->viewForRole($dashboardRole), $this->buildDashboardData($dashboardRole, $user, 'dashboard'));
    }

    private function showAdminSection(string $activePage): View
    {
        /** @var User $user */
        $user = Auth::user();

        abort_unless(in_array($user->role, ['super_admin', 'admin'], true), 403);

        return view('dashboard.shell', $this->buildDashboardData((string) $user->role, $user, $activePage));
    }

    private function buildDashboardData(string $role, User $user, string $activePage): array
    {
        $canSeeAdminData = in_array($role, ['super_admin', 'admin'], true);
        $stats = $canSeeAdminData ? $this->adminStats() : [];
        $campusRoleGroups = $canSeeAdminData ? $this->campusRoleGroups() : collect();
        $treeGroups = $canSeeAdminData ? $campusRoleGroups : collect();

        return [
            'activePage' => $activePage,
            'dashboard' => $this->dashboardConfig($role, $user, $activePage),
            'metrics' => $canSeeAdminData
                ? $this->adminMetrics($stats)
                : $this->personalMetrics($user),
            'canSeeAdminData' => $canSeeAdminData,
            'canManageData' => $this->canManageData($user),
            'roleCounts' => $stats['roleCounts'] ?? $this->blankRoleCounts(),
            'campusSummaries' => $canSeeAdminData ? $this->campusSummaries() : collect(),
            'userRows' => $canSeeAdminData ? $this->userRows() : collect(),
            'campusRoleGroups' => $campusRoleGroups,
            'treeGroups' => $treeGroups,
            'treeSearchNames' => $canSeeAdminData ? $this->treeSearchNames($treeGroups) : collect(),
            'campusOptions' => $canSeeAdminData ? $this->campusOptions() : collect(),
        ];
    }

    private function adminStats(): array
    {
        $totalUsers = User::query()->count();
        $activeUsers = User::query()->where('is_active', true)->count();
        $roleCounts = User::query()
            ->selectRaw('role, COUNT(*) as total')
            ->groupBy('role')
            ->pluck('total', 'role')
            ->map(fn ($total) => (int) $total)
            ->all();

        return [
            'totalUsers' => $totalUsers,
            'activeUsers' => $activeUsers,
            'inactiveUsers' => max(0, $totalUsers - $activeUsers),
            'activeCampuses' => Kampus::query()->where('is_active', true)->count(),
            'roleCounts' => array_merge($this->blankRoleCounts(), $roleCounts),
        ];
    }

    private function adminMetrics(array $stats): array
    {
        return [
            [
                'label' => 'Pengguna Aktif',
                'value' => $this->formatNumber($stats['activeUsers']),
                'hint' => $this->formatNumber($stats['totalUsers']).' total akun',
                'tone' => 'primary',
            ],
            [
                'label' => 'PKK',
                'value' => $this->formatNumber($stats['roleCounts']['pkk']),
                'hint' => 'Akun pendamping KTB',
                'tone' => 'success',
            ],
            [
                'label' => 'AKK',
                'value' => $this->formatNumber($stats['roleCounts']['akk']),
                'hint' => 'Akun peserta KTB',
                'tone' => 'info',
            ],
            [
                'label' => 'Kampus Aktif',
                'value' => $this->formatNumber($stats['activeCampuses']),
                'hint' => $this->formatNumber($stats['inactiveUsers']).' akun nonaktif',
                'tone' => 'warning',
            ],
        ];
    }

    private function personalMetrics(User $user): array
    {
        return [
            [
                'label' => 'Status Akun',
                'value' => $user->is_active ? 'Aktif' : 'Nonaktif',
                'hint' => 'Akses login saat ini',
                'tone' => 'primary',
            ],
            [
                'label' => 'Role',
                'value' => strtoupper((string) $user->role),
                'hint' => 'Hak akses pengguna',
                'tone' => 'success',
            ],
            [
                'label' => 'Kampus',
                'value' => $user->kampus?->singkatan ?: '-',
                'hint' => $user->kampus?->nama_kampus ?: 'Belum terhubung kampus',
                'tone' => 'info',
            ],
            [
                'label' => 'Angkatan',
                'value' => $user->angkatan ? (string) $user->angkatan : '-',
                'hint' => 'Data profil',
                'tone' => 'warning',
            ],
        ];
    }

    private function campusSummaries()
    {
        return Kampus::query()
            ->withCount([
                'users as total_users',
                'users as active_users' => fn ($query) => $query->where('is_active', true),
                'users as pkk_users' => fn ($query) => $query->where('role', 'pkk'),
                'users as akk_users' => fn ($query) => $query->where('role', 'akk'),
            ])
            ->orderByDesc('active_users')
            ->orderBy('nama_kampus')
            ->get();
    }

    private function userRows()
    {
        return User::query()
            ->with(['kampus', 'regio', 'kategoriJurusan', 'pkkLeader.kampus'])
            ->orderByDesc('created_at')
            ->get();
    }

    private function campusRoleGroups()
    {
        $allPeople = User::query()
            ->with(['kampus', 'regio', 'kategoriJurusan', 'pkkLeader.kampus', 'kelompokPemuridan'])
            ->whereIn('role', ['akk', 'pkk'])
            ->orderBy('nama_lengkap')
            ->get();

        $allGroups = KelompokPemuridan::query()
            ->with(['kampus', 'pemimpin.kampus'])
            ->orderBy('nama_kelompok')
            ->get();

        $campuses = Kampus::query()
            ->orderBy('nama_kampus')
            ->get()
            ->map(function (Kampus $kampus) use ($allPeople, $allGroups): array {
                $people = $allPeople->where('kampus_id', $kampus->kampus_id)->values();
                $personIds = $people->pluck('user_id');
                $pemuridanGroups = $allGroups
                    ->filter(fn (KelompokPemuridan $group): bool => $personIds->contains($group->pemimpin_id))
                    ->values();
                $leaderIds = $pemuridanGroups->pluck('pemimpin_id')->unique();

                return [
                    'id' => 'kampus-'.$kampus->kampus_id,
                    'campus_id' => $kampus->kampus_id,
                    'name' => $kampus->nama_kampus,
                    'short' => $kampus->singkatan ?: '-',
                    'is_active' => $kampus->is_active,
                    'pkk' => $people->whereIn('user_id', $leaderIds)->values(),
                    'akk' => $people,
                    'groups' => $pemuridanGroups,
                    'branches' => $this->buildPersonTree($people, $pemuridanGroups),
                    'unassigned_akk' => collect(),
                    'groups_count' => $pemuridanGroups->count(),
                    'total' => $people->count(),
                ];
            });

        $unassignedPeople = $allPeople
            ->whereNull('kampus_id')
            ->values();
        $unassignedPersonIds = $unassignedPeople->pluck('user_id');
        $unassignedPemuridanGroups = $allGroups
            ->filter(fn (KelompokPemuridan $group): bool => $unassignedPersonIds->contains($group->pemimpin_id))
            ->values();

        if ($unassignedPeople->isNotEmpty() || $unassignedPemuridanGroups->isNotEmpty()) {
            $leaderIds = $unassignedPemuridanGroups->pluck('pemimpin_id')->unique();

            $campuses->push([
                'id' => 'kampus-tanpa-kampus',
                'campus_id' => null,
                'name' => 'Tanpa Kampus',
                'short' => '-',
                'is_active' => false,
                'pkk' => $unassignedPeople->whereIn('user_id', $leaderIds)->values(),
                'akk' => $unassignedPeople,
                'groups' => $unassignedPemuridanGroups,
                'branches' => $this->buildPersonTree($unassignedPeople, $unassignedPemuridanGroups),
                'unassigned_akk' => collect(),
                'groups_count' => $unassignedPemuridanGroups->count(),
                'total' => $unassignedPeople->count(),
            ]);
        }

        return $campuses;
    }

    private function campusOptions()
    {
        return Kampus::query()
            ->orderBy('nama_kampus')
            ->get();
    }

    private function buildPersonTree($people, $groupRows)
    {
        if ($people->isEmpty()) {
            return collect();
        }

        $groupIds = $groupRows->pluck('kelompok_id');
        $rootPeople = $people
            ->filter(fn (User $person): bool => blank($person->kelompok_id) || ! $groupIds->contains($person->kelompok_id))
            ->values();

        return $rootPeople
            ->map(fn (User $person): array => $this->buildPersonNode($person, $people, $groupRows))
            ->values();
    }

    private function buildPersonNode(User $person, $people, $groupRows, array $trail = []): array
    {
        if (in_array($person->user_id, $trail, true)) {
            return [
                'person' => $person,
                'is_pkk' => false,
                'groups' => collect(),
            ];
        }

        $nextTrail = [...$trail, $person->user_id];
        $groups = $groupRows
            ->where('pemimpin_id', $person->user_id)
            ->map(function (KelompokPemuridan $group) use ($people, $groupRows, $person, $nextTrail): array {
                $members = $people
                    ->where('kelompok_id', $group->kelompok_id)
                    ->reject(fn (User $member): bool => $member->user_id === $person->user_id)
                    ->values()
                    ->map(fn (User $member): array => $this->buildPersonNode($member, $people, $groupRows, $nextTrail))
                    ->values();

                return [
                    'id' => $group->kelompok_id,
                    'name' => $group->nama_kelompok,
                    'is_legacy' => false,
                    'model' => $group,
                    'members' => $members,
                ];
            })
            ->values();

        return [
            'person' => $person,
            'is_pkk' => $groups->isNotEmpty(),
            'groups' => $groups,
        ];
    }

    private function treeSearchNames($treeGroups)
    {
        return $treeGroups
            ->flatMap(function (array $group): array {
                $names = [$group['name'], $group['short'], 'PKK '.$group['name'], 'AKK '.$group['name']];

                $names = array_merge($names, $this->treeNodeSearchNames($group['branches']));

                return $names;
            })
            ->filter()
            ->unique()
            ->sort()
            ->values();
    }

    private function treeNodeSearchNames($nodes): array
    {
        return $nodes
            ->flatMap(function (array $node): array {
                $names = [$node['person']->nama_lengkap];

                foreach ($node['groups'] as $group) {
                    $names[] = $group['name'];
                    $names = array_merge($names, $this->treeNodeSearchNames($group['members']));
                }

                return $names;
            })
            ->all();
    }

    private function dashboardConfig(string $role, User $user, string $activePage): array
    {
        $config = match ($role) {
            'super_admin' => [
                'title' => 'Dashboard Super Admin',
                'eyebrow' => 'Kontrol sistem',
                'subtitle' => 'Ringkasan akun, kampus, dan akses Sistem KTB.',
                'roleLabel' => 'Super Admin',
                'route' => 'superadmin.dashboard',
            ],
            'admin' => [
                'title' => 'Dashboard Admin',
                'eyebrow' => $user->admin_tipe ? 'Admin '.ucfirst($user->admin_tipe) : 'Admin',
                'subtitle' => 'Ringkasan data pengguna dan kampus untuk pengelolaan KTB.',
                'roleLabel' => 'Admin',
                'route' => 'admin.dashboard',
            ],
            'pkk' => [
                'title' => 'Dashboard PKK',
                'eyebrow' => 'Pendamping KTB',
                'subtitle' => 'Ringkasan akun dan identitas pelayanan Anda.',
                'roleLabel' => 'PKK',
                'route' => 'pkk.dashboard',
            ],
            'akk' => [
                'title' => 'Dashboard AKK',
                'eyebrow' => 'Anggota KTB',
                'subtitle' => 'Ringkasan akun dan identitas KTB Anda.',
                'roleLabel' => 'AKK',
                'route' => 'akk.dashboard',
            ],
            default => [
                'title' => 'Dashboard',
                'eyebrow' => 'Sistem KTB',
                'subtitle' => 'Ringkasan akun Sistem KTB.',
                'roleLabel' => strtoupper($role),
                'route' => 'dashboard',
            ],
        };

        return match ($activePage) {
            'kampus' => array_merge($config, [
                'title' => 'Kampus',
                'eyebrow' => 'Data Kampus',
                'subtitle' => 'Daftar kampus dan ringkasan pengguna Sistem KTB per kampus.',
            ]),
            'pengguna' => array_merge($config, [
                'title' => 'Pengguna',
                'eyebrow' => 'Data Pengguna',
                'subtitle' => 'Daftar akun yang terdaftar di Sistem KTB.',
            ]),
            'pohon' => array_merge($config, [
                'title' => 'Pohon Pemuridan',
                'eyebrow' => 'Peta Pemuridan',
                'subtitle' => 'Visualisasi AKK, PKK, dan kelompok berdasarkan kampus.',
            ]),
            default => $config,
        };
    }

    private function blankRoleCounts(): array
    {
        return [
            'super_admin' => 0,
            'admin' => 0,
            'pkk' => 0,
            'akk' => 0,
        ];
    }

    private function canManageData(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isAdminEditor();
    }

    private function routeForRole(string $role): string
    {
        return match ($role) {
            'super_admin' => 'superadmin.dashboard',
            'admin' => 'admin.dashboard',
            'pkk' => 'pkk.dashboard',
            'akk' => 'akk.dashboard',
            default => 'landing',
        };
    }

    private function viewForRole(string $role): string
    {
        return match ($role) {
            'super_admin' => 'superadmin.dashboard',
            'admin' => 'admin.dashboard',
            'pkk' => 'pkk.dashboard',
            'akk' => 'akk.dashboard',
            default => 'dashboard.shell',
        };
    }

    private function formatNumber(int $value): string
    {
        return number_format($value, 0, ',', '.');
    }
}
