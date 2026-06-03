<?php

namespace App\Http\Controllers;

use App\Models\Kampus;
use App\Models\KategoriJurusan;
use App\Models\KelompokPemuridan;
use App\Models\Regio;
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

    public function profile(): View
    {
        /** @var User $user */
        $user = Auth::user();

        return view('dashboard.shell', $this->buildDashboardData((string) $user->role, $user, 'profil'));
    }

    public function kampus(): RedirectResponse
    {
        /** @var User|null $user */
        $user = Auth::user();

        abort_unless($user && $user->isAdmin(), 403);

        return redirect()->route('admin.dashboard');
    }

    public function kampusDetail(Kampus $kampus): View
    {
        return $this->showKampusDetail($kampus);
    }

    public function regio(): View
    {
        return $this->showAdminSection('regio');
    }

    public function pengguna(): View
    {
        return $this->showAdminSection('pengguna');
    }

    public function anggotaKtb(): View
    {
        return $this->showAdminSection('anggota-ktb');
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
        abort_if(in_array($activePage, ['regio', 'pengguna'], true) && ! $user->isSuperAdmin(), 403);
        abort_if(in_array($activePage, ['kampus', 'anggota-ktb', 'pohon'], true) && $user->isSuperAdmin(), 403);

        return view('dashboard.shell', $this->buildDashboardData((string) $user->role, $user, $activePage));
    }

    private function showKampusDetail(Kampus $kampus): View
    {
        /** @var User $user */
        $user = Auth::user();

        abort_unless($user && $user->isAdmin(), 403);
        $this->authorizeKampusDetailAccess($kampus, $user);

        $kampusDetail = $this->campusDetailRow($kampus);
        $data = $this->buildDashboardData((string) $user->role, $user, 'kampus-detail');
        $campusLabel = $kampusDetail->singkatan ?: $kampusDetail->nama_kampus;

        $data['selectedKampus'] = $kampusDetail;
        $data['selectedCampusMembers'] = $this->selectedCampusMembers($kampusDetail);
        $data['selectedCampusGroups'] = $this->selectedCampusGroups($kampusDetail);
        $selectedTreeGroups = $data['treeGroups']
            ->filter(fn (array $group): bool => (int) ($group['campus_id'] ?? 0) === (int) $kampusDetail->kampus_id)
            ->values();
        $data['treeGroups'] = $selectedTreeGroups;
        $data['treeSearchNames'] = $this->treeSearchNames($selectedTreeGroups);
        $data['dashboard'] = array_merge($data['dashboard'], [
            'title' => $campusLabel,
            'eyebrow' => 'Detail Kampus',
            'subtitle' => 'Pohon pemuridan khusus '.$kampusDetail->nama_kampus.'.',
        ]);

        return view('dashboard.shell', $data);
    }

    private function buildDashboardData(string $role, User $user, string $activePage): array
    {
        $canSeeAdminData = in_array($role, ['super_admin', 'admin'], true);
        $isRegioScoped = $canSeeAdminData && ! $user->isSuperAdmin();
        $regioScopeId = $isRegioScoped ? $user->regio_id : null;
        $stats = $canSeeAdminData ? $this->adminStats($regioScopeId, $isRegioScoped) : [];
        $campusRoleGroups = $canSeeAdminData ? $this->campusRoleGroups($regioScopeId, $isRegioScoped) : collect();
        $treeGroups = $canSeeAdminData ? $campusRoleGroups : collect();

        return [
            'activePage' => $activePage,
            'dashboard' => $this->dashboardConfig($role, $user, $activePage),
            'metrics' => $canSeeAdminData
                ? ($user->isSuperAdmin() ? $this->superAdminMetrics($stats) : $this->adminMetrics($stats))
                : $this->personalMetrics($user),
            'canSeeAdminData' => $canSeeAdminData,
            'canManageData' => $this->canManageData($user),
            'roleCounts' => $stats['roleCounts'] ?? $this->blankRoleCounts(),
            'campusSummaries' => $canSeeAdminData ? $this->campusSummaries($regioScopeId, $isRegioScoped) : collect(),
            'regioRows' => $canSeeAdminData ? $this->regioRows($regioScopeId, $isRegioScoped) : collect(),
            'userRows' => $canSeeAdminData ? $this->userRows($regioScopeId, $isRegioScoped) : collect(),
            'memberRows' => $canSeeAdminData ? $this->memberRows($regioScopeId, $isRegioScoped) : collect(),
            'campusRoleGroups' => $campusRoleGroups,
            'treeGroups' => $treeGroups,
            'treeSearchNames' => $canSeeAdminData ? $this->treeSearchNames($treeGroups) : collect(),
            'campusOptions' => $canSeeAdminData ? $this->campusOptions($regioScopeId, $isRegioScoped) : collect(),
            'regioOptions' => $canSeeAdminData ? $this->regioOptions($regioScopeId, $isRegioScoped) : collect(),
            'kategoriJurusanOptions' => $this->kategoriJurusanOptions(),
            'selectedKampus' => null,
            'selectedCampusMembers' => collect(),
            'selectedCampusGroups' => collect(),
        ];
    }

    private function adminStats(?int $regioId, bool $isRegioScoped): array
    {
        $userQuery = $this->applyRegioScope(User::query(), $regioId, $isRegioScoped);
        $totalUsers = (clone $userQuery)->count();
        $activeUsers = (clone $userQuery)->where('is_active', true)->count();
        $roleCounts = (clone $userQuery)
            ->selectRaw('role, COUNT(*) as total')
            ->groupBy('role')
            ->pluck('total', 'role')
            ->map(fn ($total) => (int) $total)
            ->all();
        $activeRoleCounts = (clone $userQuery)
            ->where('is_active', true)
            ->selectRaw('role, COUNT(*) as total')
            ->groupBy('role')
            ->pluck('total', 'role')
            ->map(fn ($total) => (int) $total)
            ->all();
        $campusQuery = $this->applyRegioScope(Kampus::query(), $regioId, $isRegioScoped);
        $groupQuery = $this->applyRegioScope(KelompokPemuridan::query(), $regioId, $isRegioScoped);
        $regioQuery = $this->applyRegioScope(Regio::query(), $regioId, $isRegioScoped);

        return [
            'totalUsers' => $totalUsers,
            'activeUsers' => $activeUsers,
            'inactiveUsers' => max(0, $totalUsers - $activeUsers),
            'totalCampuses' => (clone $campusQuery)->count(),
            'activeCampuses' => (clone $campusQuery)->where('is_active', true)->count(),
            'totalGroups' => (clone $groupQuery)->count(),
            'activeGroups' => (clone $groupQuery)->where('is_active', true)->count(),
            'totalRegios' => (clone $regioQuery)->count(),
            'activeRegios' => (clone $regioQuery)->where('is_active', true)->count(),
            'roleCounts' => array_merge($this->blankRoleCounts(), $roleCounts),
            'activeRoleCounts' => array_merge($this->blankRoleCounts(), $activeRoleCounts),
        ];
    }

    private function superAdminMetrics(array $stats): array
    {
        return [
            [
                'label' => 'Admin',
                'value' => $this->formatNumber($stats['roleCounts']['admin']),
                'hint' => $this->formatNumber($stats['activeRoleCounts']['admin']).' akun admin aktif',
                'tone' => 'primary',
            ],
            [
                'label' => 'Regio Aktif',
                'value' => $this->formatNumber($stats['activeRegios']),
                'hint' => $this->formatNumber($stats['totalRegios']).' total regio pelayanan',
                'tone' => 'success',
            ],
            [
                'label' => 'Super Admin',
                'value' => $this->formatNumber($stats['roleCounts']['super_admin']),
                'hint' => 'Akses pusat sistem',
                'tone' => 'info',
            ],
            [
                'label' => 'Akun Aktif',
                'value' => $this->formatNumber($stats['activeUsers']),
                'hint' => $this->formatNumber($stats['totalUsers']).' total akun sistem',
                'tone' => 'warning',
            ],
        ];
    }

    private function adminMetrics(array $stats): array
    {
        $memberUsers = $stats['roleCounts']['pkk'] + $stats['roleCounts']['akk'];
        $activeMemberUsers = $stats['activeRoleCounts']['pkk'] + $stats['activeRoleCounts']['akk'];

        return [
            [
                'label' => 'Anggota KTB',
                'value' => $this->formatNumber($memberUsers),
                'hint' => $this->formatNumber($activeMemberUsers).' anggota aktif',
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
                'label' => 'Kelompok',
                'value' => $this->formatNumber($stats['totalGroups']),
                'hint' => $this->formatNumber($stats['activeGroups']).' kelompok aktif',
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

    private function campusSummaries(?int $regioId, bool $isRegioScoped)
    {
        return $this->applyRegioScope(Kampus::query(), $regioId, $isRegioScoped)
            ->with('regio')
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

    private function regioRows(?int $regioId, bool $isRegioScoped)
    {
        return $this->applyRegioScope(Regio::query(), $regioId, $isRegioScoped)
            ->withCount([
                'users as total_users',
                'users as active_users' => fn ($query) => $query->where('is_active', true),
                'users as admin_users' => fn ($query) => $query->where('role', 'admin'),
                'users as pkk_users' => fn ($query) => $query->where('role', 'pkk'),
                'users as akk_users' => fn ($query) => $query->where('role', 'akk'),
            ])
            ->orderByDesc('active_users')
            ->orderBy('nama_regio')
            ->get();
    }

    private function userRows(?int $regioId, bool $isRegioScoped)
    {
        return $this->applyRegioScope(User::query(), $regioId, $isRegioScoped)
            ->with(['kampus', 'regio', 'kategoriJurusan', 'pkkLeader.kampus'])
            ->whereIn('role', ['admin', 'pkk', 'akk'])
            ->orderByDesc('created_at')
            ->get();
    }

    private function memberRows(?int $regioId, bool $isRegioScoped)
    {
        return $this->applyRegioScope(User::query(), $regioId, $isRegioScoped)
            ->with(['kampus', 'regio', 'kategoriJurusan', 'pkkLeader.kampus', 'kelompokPemuridan'])
            ->whereIn('role', ['akk', 'pkk'])
            ->orderByDesc('created_at')
            ->get();
    }

    private function campusRoleGroups(?int $regioId, bool $isRegioScoped)
    {
        $allPeople = $this->applyRegioScope(User::query(), $regioId, $isRegioScoped)
            ->with(['kampus', 'regio', 'kategoriJurusan', 'pkkLeader.kampus', 'kelompokPemuridan'])
            ->whereIn('role', ['akk', 'pkk'])
            ->orderBy('nama_lengkap')
            ->get();

        $allGroups = $this->applyRegioScope(KelompokPemuridan::query(), $regioId, $isRegioScoped)
            ->with(['kampus.regio', 'regio', 'pemimpin.kampus'])
            ->orderBy('nama_kelompok')
            ->get();

        $campuses = $this->applyRegioScope(Kampus::query(), $regioId, $isRegioScoped)
            ->with('regio')
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

    private function campusOptions(?int $regioId, bool $isRegioScoped)
    {
        return $this->applyRegioScope(Kampus::query(), $regioId, $isRegioScoped)
            ->with('regio')
            ->orderBy('nama_kampus')
            ->get();
    }

    private function campusDetailRow(Kampus $kampus): Kampus
    {
        return Kampus::query()
            ->with('regio')
            ->withCount([
                'users as total_users',
                'users as active_users' => fn ($query) => $query->where('is_active', true),
                'users as pkk_users' => fn ($query) => $query->where('role', 'pkk'),
                'users as akk_users' => fn ($query) => $query->where('role', 'akk'),
                'kelompokPemuridan as groups_count',
                'kelompokPemuridan as active_groups_count' => fn ($query) => $query->where('is_active', true),
            ])
            ->findOrFail($kampus->getKey());
    }

    private function selectedCampusMembers(Kampus $kampus)
    {
        return User::query()
            ->with(['pkkLeader', 'kelompokPemuridan'])
            ->where('kampus_id', $kampus->kampus_id)
            ->whereIn('role', ['akk', 'pkk'])
            ->orderBy('nama_lengkap')
            ->get();
    }

    private function selectedCampusGroups(Kampus $kampus)
    {
        return KelompokPemuridan::query()
            ->with('pemimpin')
            ->withCount('anggota')
            ->where('kampus_id', $kampus->kampus_id)
            ->orderBy('nama_kelompok')
            ->get();
    }

    private function regioOptions(?int $regioId, bool $isRegioScoped)
    {
        return $this->applyRegioScope(Regio::query(), $regioId, $isRegioScoped)
            ->orderByDesc('is_active')
            ->orderBy('nama_regio')
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

    private function kategoriJurusanOptions()
    {
        return KategoriJurusan::query()
            ->orderBy('nama_kategori')
            ->get();
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
                'subtitle' => 'Kelola akses admin dan struktur regio Sistem KTB.',
                'roleLabel' => 'Super Admin',
                'route' => 'superadmin.dashboard',
            ],
            'admin' => [
                'title' => 'Dashboard Admin',
                'eyebrow' => $user->admin_tipe ? 'Admin '.ucfirst($user->admin_tipe) : 'Admin',
                'subtitle' => 'Pantau anggota, kampus, dan kelompok KTB di '.($user->regio?->nama_regio ?: 'regio Anda').'.',
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
            'regio' => array_merge($config, [
                'title' => 'Regio',
                'eyebrow' => 'Data Regio',
                'subtitle' => 'Daftar wilayah pelayanan dan ringkasan anggota Sistem KTB per regio.',
            ]),
            'pengguna' => array_merge($config, [
                'title' => 'Pengguna',
                'eyebrow' => 'Data Pengguna',
                'subtitle' => 'Daftar akun admin, PKK, dan AKK Sistem KTB.',
            ]),
            'profil' => array_merge($config, [
                'title' => 'Profil',
                'eyebrow' => 'Akun Pengguna',
                'subtitle' => 'Detail profil, data pribadi, dan pengaturan password akun Anda.',
            ]),
            'anggota-ktb' => array_merge($config, [
                'title' => 'Anggota KTB',
                'eyebrow' => 'Data Anggota',
                'subtitle' => 'Daftar akun AKK dan PKK yang terdaftar di Sistem KTB.',
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

    private function authorizeKampusDetailAccess(Kampus $kampus, User $user): void
    {
        abort_unless(
            filled($user->regio_id) && (int) $kampus->regio_id === (int) $user->regio_id,
            403
        );
    }

    private function applyRegioScope($query, ?int $regioId, bool $isRegioScoped)
    {
        if (! $isRegioScoped) {
            return $query;
        }

        return filled($regioId)
            ? $query->where('regio_id', $regioId)
            : $query->whereRaw('1 = 0');
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
