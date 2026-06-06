<section class="panel campus-groups-panel">
  <div class="panel-head table-panel-head">
    <div class="table-panel-title">
      <span class="eyebrow">Kelompok Kampus</span>
      <h2>Kelompok KTB {{ $selectedKampus->singkatan ?: $selectedKampus->nama_kampus }}</h2>
    </div>
    <div class="row-actions table-panel-actions">
      <input class="search" type="search" placeholder="Cari kelompok atau laporan..." data-filter-block=".campus-group-card" aria-label="Cari kelompok KTB kampus">
    </div>
  </div>

  @if ($selectedCampusGroups->isEmpty())
    <div class="empty-state">Belum ada kelompok KTB di kampus ini.</div>
  @else
    <div class="campus-group-grid">
      @foreach ($selectedCampusGroups as $group)
        @php
          $reportSearchText = $group->laporanPertemuan
              ->flatMap(fn ($report) => [
                  $report->bahan,
                  $report->ringkasan,
                  $report->pelapor?->nama_lengkap,
                  $report->tanggal_pertemuan?->format('d/m/Y'),
              ])
              ->filter()
              ->join(' ');
        @endphp
        <article
          class="campus-group-card"
          data-filter-text="{{ trim($group->nama_kelompok.' '.($group->pemimpin?->nama_lengkap ?: '').' '.$reportSearchText) }}"
        >
          <div class="campus-group-head">
            <div>
              <span class="eyebrow">Kelompok KTB</span>
              <h3>{{ $group->nama_kelompok }}</h3>
              <p>{{ $group->pemimpin?->nama_lengkap ? 'PKK '.$group->pemimpin->nama_lengkap : 'Belum ada pemimpin' }}</p>
            </div>
            <span class="badge {{ $group->is_active ? '' : 'warning' }}">{{ $group->is_active ? 'Aktif' : 'Nonaktif' }}</span>
          </div>

          <div class="campus-group-stats">
            <div>
              <span>Anggota</span>
              <strong>{{ number_format($group->anggota_count, 0, ',', '.') }}</strong>
            </div>
            <div>
              <span>Laporan</span>
              <strong>{{ number_format($group->laporan_pertemuan_count, 0, ',', '.') }}</strong>
            </div>
          </div>

          @if ($group->laporanPertemuan->isEmpty())
            <div class="empty-state campus-group-empty">Belum ada laporan pertemuan dari PKK kelompok ini.</div>
          @else
            <div class="table-wrap campus-report-table-wrap">
              <table class="table campus-report-table">
                <thead>
                  <tr>
                    <th>Tanggal</th>
                    <th>Bahan</th>
                    <th>Hadir</th>
                    <th>Pelapor</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach ($group->laporanPertemuan as $report)
                    @php
                      $attendanceIds = collect($report->anggota_hadir ?? [])->map(fn ($id) => (int) $id);
                      $attendanceNames = $group->anggota
                          ->whereIn('user_id', $attendanceIds)
                          ->pluck('nama_lengkap')
                          ->values();
                    @endphp
                    <tr>
                      <td>
                        <strong>{{ $report->tanggal_pertemuan?->format('d/m/Y') }}</strong>
                        <div class="muted">{{ $report->pertemuan_ke ? 'Pertemuan '.$report->pertemuan_ke : 'Pertemuan' }}</div>
                      </td>
                      <td>
                        <div class="cell-main">{{ $report->bahan }}</div>
                        @if ($report->ringkasan)
                          <div class="muted">{{ $report->ringkasan }}</div>
                        @endif
                      </td>
                      <td>
                        <strong>{{ number_format($report->jumlah_hadir, 0, ',', '.') }}</strong>
                        <div class="muted">{{ $attendanceNames->isNotEmpty() ? $attendanceNames->join(', ') : 'Tidak ada anggota dicatat hadir' }}</div>
                      </td>
                      <td>{{ $report->pelapor?->nama_lengkap ?: '-' }}</td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          @endif
        </article>
      @endforeach
    </div>
  @endif
</section>
