<?php

namespace App\Http\Controllers;

use App\Models\Kampus;
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

    public function pemuridan(): View
    {
        return $this->showAdminSection('pemuridan');
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
        $treeGroups = $canSeeAdminData
            ? $campusRoleGroups->filter(fn (array $group) => $group['pkk']->isNotEmpty() || $group['akk']->isNotEmpty())->values()
            : collect();

        return [
            'activePage' => $activePage,
            'dashboard' => $this->dashboardConfig($role, $user, $activePage),
            'metrics' => $canSeeAdminData
                ? $this->adminMetrics($stats)
                : $this->personalMetrics($user),
            'canSeeAdminData' => $canSeeAdminData,
            'roleCounts' => $stats['roleCounts'] ?? $this->blankRoleCounts(),
            'campusSummaries' => $canSeeAdminData ? $this->campusSummaries() : collect(),
            'userRows' => $canSeeAdminData ? $this->userRows() : collect(),
            'campusRoleGroups' => $campusRoleGroups,
            'treeGroups' => $treeGroups,
            'treeSearchNames' => $canSeeAdminData ? $this->treeSearchNames($treeGroups) : collect(),
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
            'targetPkk' => User::query()
                ->where('role', 'pkk')
                ->where('is_target', true)
                ->count(),
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
                'hint' => $this->formatNumber($stats['targetPkk']).' bertanda target',
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
                'hint' => $user->is_target ? 'PKK target' : 'Data profil',
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
            ->with('kampus')
            ->orderByDesc('created_at')
            ->get();
    }

    private function campusRoleGroups()
    {
        $campuses = Kampus::query()
            ->with(['users' => fn ($query) => $query
                ->whereIn('role', ['pkk', 'akk'])
                ->orderBy('nama_lengkap')])
            ->orderBy('nama_kampus')
            ->get()
            ->map(function (Kampus $kampus): array {
                $pkkUsers = $kampus->users->where('role', 'pkk')->values();
                $akkUsers = $kampus->users->where('role', 'akk')->values();

                return [
                    'id' => 'kampus-'.$kampus->kampus_id,
                    'name' => $kampus->nama_kampus,
                    'short' => $kampus->singkatan ?: '-',
                    'is_active' => $kampus->is_active,
                    'pkk' => $pkkUsers,
                    'akk' => $akkUsers,
                    'branches' => $this->buildPkkBranches($pkkUsers, $akkUsers),
                    'unassigned_akk' => $pkkUsers->isEmpty() ? $akkUsers : collect(),
                    'total' => $pkkUsers->count() + $akkUsers->count(),
                ];
            });

        $unassignedUsers = User::query()
            ->whereNull('kampus_id')
            ->whereIn('role', ['pkk', 'akk'])
            ->orderBy('nama_lengkap')
            ->get();

        if ($unassignedUsers->isNotEmpty()) {
            $pkkUsers = $unassignedUsers->where('role', 'pkk')->values();
            $akkUsers = $unassignedUsers->where('role', 'akk')->values();

            $campuses->push([
                'id' => 'kampus-tanpa-kampus',
                'name' => 'Tanpa Kampus',
                'short' => '-',
                'is_active' => false,
                'pkk' => $pkkUsers,
                'akk' => $akkUsers,
                'branches' => $this->buildPkkBranches($pkkUsers, $akkUsers),
                'unassigned_akk' => $pkkUsers->isEmpty() ? $akkUsers : collect(),
                'total' => $pkkUsers->count() + $akkUsers->count(),
            ]);
        }

        return $campuses;
    }

    private function buildPkkBranches($pkkUsers, $akkUsers)
    {
        if ($pkkUsers->isEmpty()) {
            return collect();
        }

        $branches = $pkkUsers->map(fn (User $pkk): array => [
            'pkk' => $pkk,
            'akk' => collect(),
        ]);

        foreach ($akkUsers->values() as $index => $akk) {
            $branchIndex = $index % $branches->count();
            $branch = $branches->get($branchIndex);
            $branch['akk']->push($akk);
            $branches->put($branchIndex, $branch);
        }

        return $branches->values();
    }

    private function treeSearchNames($treeGroups)
    {
        return $treeGroups
            ->flatMap(function (array $group): array {
                $names = [$group['name'], $group['short'], 'PKK '.$group['name'], 'AKK '.$group['name']];

                foreach ($group['pkk'] as $row) {
                    $names[] = $row->nama_lengkap;
                }

                foreach ($group['akk'] as $row) {
                    $names[] = $row->nama_lengkap;
                }

                return $names;
            })
            ->filter()
            ->unique()
            ->sort()
            ->values();
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
            'pemuridan' => array_merge($config, [
                'title' => 'AKK dan PKK per Kampus',
                'eyebrow' => 'Data Pemuridan',
                'subtitle' => 'Daftar AKK dan PKK yang dikelompokkan berdasarkan kampus.',
            ]),
            'pohon' => array_merge($config, [
                'title' => 'Pohon Pemuridan',
                'eyebrow' => 'Peta Pemuridan',
                'subtitle' => 'Visualisasi PKK dan AKK berdasarkan kampus.',
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
