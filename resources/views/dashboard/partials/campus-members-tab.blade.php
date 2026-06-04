<section class="panel campus-members-panel">
  <div class="panel-head table-panel-head">
    <div class="table-panel-title">
      <span class="eyebrow">Anggota Kampus</span>
      <h2>Anggota KTB {{ $selectedKampus->singkatan ?: $selectedKampus->nama_kampus }}</h2>
    </div>
    <div class="row-actions table-panel-actions">
      <input class="search" type="search" placeholder="Cari anggota..." data-filter-table="campus-member-table" aria-label="Cari anggota kampus">
    </div>
  </div>

  @if ($selectedCampusMembers->isEmpty())
    <div class="empty-state">Belum ada anggota KTB di kampus ini.</div>
  @else
    <div class="table-wrap">
      <table class="table" id="campus-member-table">
        <thead>
          <tr>
            <th>Nama</th>
            <th>Username</th>
            <th>Role</th>
            <th>Angkatan</th>
            <th>PKK</th>
            <th>Kelompok</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($selectedCampusMembers as $member)
            <tr>
              <td>
                <strong>{{ $member->nama_lengkap }}</strong>
                <div class="muted">{{ $selectedKampus->singkatan ?: $selectedKampus->nama_kampus }}</div>
              </td>
              <td>{{ $member->username }}</td>
              <td><span class="badge neutral">{{ $roleNames[$member->role] ?? strtoupper($member->role) }}</span></td>
              <td>{{ $member->angkatan ?: '-' }}</td>
              <td>{{ $member->pkkLeader?->nama_lengkap ?: '-' }}</td>
              <td>{{ $member->kelompokPemuridan?->nama_kelompok ?: '-' }}</td>
              <td>
                <span class="badge {{ $member->is_active ? '' : 'warning' }}">
                  {{ $member->is_active ? 'Aktif' : 'Nonaktif' }}
                </span>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  @endif
</section>
