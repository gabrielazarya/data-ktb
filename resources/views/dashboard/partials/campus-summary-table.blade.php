<section class="panel" id="ringkasan-kampus">
  <div class="panel-head table-panel-head">
    <div class="table-panel-title">
      <span class="eyebrow">Ringkasan</span>
      <h2>Pengguna per Kampus</h2>
    </div>
    <div class="row-actions table-panel-actions">
      <input class="search" type="search" placeholder="Cari kampus..." data-filter-table="campus-table" aria-label="Cari kampus">
      @if ($canManageData)
        <button class="btn is-compact" type="button" data-modal-open="modal-kampus-create">Tambah Kampus</button>
      @endif
    </div>
  </div>

  @if ($canManageData)
    <div class="modal" id="modal-kampus-create" hidden>
      <div class="modal-panel" role="dialog" aria-modal="true" aria-labelledby="modal-kampus-create-title">
        <div class="modal-head">
          <div>
            <span class="eyebrow">Tambah</span>
            <h2 id="modal-kampus-create-title">Kampus</h2>
          </div>
          <button class="btn modal-close" type="button" data-modal-close aria-label="Tutup">x</button>
        </div>
        <div class="modal-body">
          @include('dashboard.partials.kampus-form')
        </div>
      </div>
    </div>
  @endif

  @if ($campusSummaries->isEmpty())
    <div class="empty-state">Belum ada data kampus.</div>
  @else
    <div class="table-wrap">
      <table class="table" id="campus-table">
        <thead>
          <tr>
            <th>Kampus</th>
            <th>Regio</th>
            <th>Status</th>
            <th>Total User</th>
            <th>Aktif</th>
            <th>PKK</th>
            <th>AKK</th>
            @if ($canManageData)
              <th>Aksi</th>
            @endif
          </tr>
        </thead>
        <tbody>
          @foreach ($campusSummaries as $kampus)
            <tr>
              <td>
                <strong>{{ $kampus->nama_kampus }}</strong>
                <div class="muted">{{ $kampus->singkatan ?: '-' }}</div>
              </td>
              <td>{{ $kampus->regio?->nama_regio ?: '-' }}</td>
              <td>
                <span class="badge {{ $kampus->is_active ? '' : 'warning' }}">
                  {{ $kampus->is_active ? 'Aktif' : 'Nonaktif' }}
                </span>
              </td>
              <td>{{ number_format($kampus->total_users, 0, ',', '.') }}</td>
              <td>{{ number_format($kampus->active_users, 0, ',', '.') }}</td>
              <td>{{ number_format($kampus->pkk_users, 0, ',', '.') }}</td>
              <td>{{ number_format($kampus->akk_users, 0, ',', '.') }}</td>
              @if ($canManageData)
                <td>
                  <div class="row-actions">
                    <button class="btn icon-btn" type="button" data-modal-open="modal-kampus-edit-{{ $kampus->kampus_id }}" title="Edit" aria-label="Edit {{ $kampus->nama_kampus }}">
                      <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                        <path d="M12 20h9"></path>
                        <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"></path>
                      </svg>
                    </button>
                    <button class="btn icon-btn is-danger" type="button" data-modal-open="modal-kampus-delete-{{ $kampus->kampus_id }}" title="Hapus" aria-label="Hapus {{ $kampus->nama_kampus }}">
                      <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                        <path d="M3 6h18"></path>
                        <path d="M8 6V4h8v2"></path>
                        <path d="m19 6-1 14H6L5 6"></path>
                        <path d="M10 11v6"></path>
                        <path d="M14 11v6"></path>
                      </svg>
                    </button>
                  </div>

                  <div class="modal" id="modal-kampus-edit-{{ $kampus->kampus_id }}" hidden>
                    <div class="modal-panel" role="dialog" aria-modal="true" aria-labelledby="modal-kampus-edit-title-{{ $kampus->kampus_id }}">
                      <div class="modal-head">
                        <div>
                          <span class="eyebrow">Edit</span>
                          <h2 id="modal-kampus-edit-title-{{ $kampus->kampus_id }}">{{ $kampus->nama_kampus }}</h2>
                        </div>
                        <button class="btn modal-close" type="button" data-modal-close aria-label="Tutup">x</button>
                      </div>
                      <div class="modal-body">
                        @include('dashboard.partials.kampus-form', ['kampus' => $kampus])
                      </div>
                    </div>
                  </div>

                  <div class="modal" id="modal-kampus-delete-{{ $kampus->kampus_id }}" hidden>
                    <div class="modal-panel is-small" role="dialog" aria-modal="true" aria-labelledby="modal-kampus-delete-title-{{ $kampus->kampus_id }}">
                      <div class="modal-head">
                        <div>
                          <span class="eyebrow">Hapus</span>
                          <h2 id="modal-kampus-delete-title-{{ $kampus->kampus_id }}">Kampus</h2>
                        </div>
                        <button class="btn modal-close" type="button" data-modal-close aria-label="Tutup">x</button>
                      </div>
                      <div class="modal-body">
                        <p class="muted">Hapus {{ $kampus->nama_kampus }}? Pengguna terkait akan menjadi tanpa kampus.</p>
                        <form method="POST" action="{{ route('dashboard.kampus.destroy', $kampus) }}" class="inline-delete">
                          @csrf
                          @method('DELETE')
                          <div class="form-actions">
                            <button class="btn is-compact is-danger" type="submit">Hapus Kampus</button>
                            <button class="btn is-compact" type="button" data-modal-close>Batal</button>
                          </div>
                        </form>
                      </div>
                    </div>
                  </div>
                </td>
              @endif
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  @endif
</section>
