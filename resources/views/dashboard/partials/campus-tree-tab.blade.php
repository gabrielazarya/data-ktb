<section class="tree-v2-surface">
  @if ($treeGroups->isEmpty())
    <div class="empty-state">Belum ada data PKK atau AKK untuk ditampilkan.</div>
  @else
    <div class="tree-v2-toolbar">
      <div class="tree-v2-search">
        <input class="search" type="search" list="tree-search-list" placeholder="Cari nama atau kampus..." data-tree-search-input aria-label="Cari nama atau kampus di pohon">
        <button class="btn" type="button" data-tree-search-submit>Cari</button>
        <datalist id="tree-search-list">
          @foreach ($treeSearchNames as $name)
            <option value="{{ $name }}"></option>
          @endforeach
        </datalist>
      </div>
      <div class="zoom-controls" data-zoom-controls>
        <button class="btn" type="button" data-zoom-out aria-label="Perkecil zoom">-</button>
        <span class="zoom-value" data-zoom-value>90%</span>
        <button class="btn" type="button" data-zoom-in aria-label="Perbesar zoom">+</button>
      </div>
    </div>

    <div class="tree-v2-scroll" data-drag-scroll>
      <div class="tree-v2-zoom" data-tree-zoom>
        <div class="tree-v2-graph" role="tree" aria-label="Grafik pohon pemuridan">
          <ul class="tree-v2-root">
            @foreach ($treeGroups as $group)
              @php
                $groupCampusId = $group['campus_id'] ?? null;
                $groupCampusLabel = $group['short'] !== '-' ? $group['short'] : $group['name'];
                $groupMeta = $group['total'].' anggota - '.($group['groups_count'] ?? 0).' kelompok';
              @endphp
              <li class="tree-v2-item">
                <article
                  class="tree-v2-node tree-v2-campus is-actionable"
                  data-tree-v2-node-action="campus"
                  data-node-name="{{ $groupCampusLabel }}"
                  data-node-meta="{{ $groupMeta }}"
                  data-campus-id="{{ $groupCampusId ?: '' }}"
                  data-campus-name="{{ $group['name'] }}"
                  data-search-name="{{ $group['name'] }} {{ $group['short'] }}"
                  tabindex="0"
                  role="button"
                  aria-label="Aksi untuk {{ $group['name'] }}"
                >
                  <div class="tree-v2-node-head">
                    <div class="tree-v2-name" title="{{ $group['name'] }}">{{ $groupCampusLabel }}</div>
                    <span class="badge neutral">Kampus</span>
                  </div>
                  <div class="tree-v2-meta">{{ $groupMeta }}</div>
                </article>

                <ul class="tree-v2-children">
                  @if ($group['branches']->isEmpty() && $group['unassigned_akk']->isEmpty())
                    <li class="tree-v2-item">
                      <article
                        class="tree-v2-node tree-v2-empty-node is-actionable"
                        data-tree-v2-node-action="empty-campus"
                        data-node-name="Belum ada anggota"
                        data-node-meta="{{ $group['name'] }}"
                        data-campus-id="{{ $groupCampusId ?: '' }}"
                        data-campus-name="{{ $group['name'] }}"
                        data-search-name="{{ $group['name'] }} belum ada anggota"
                        tabindex="0"
                        role="button"
                        aria-label="Tambah anggota untuk {{ $group['name'] }}"
                      >
                        <div class="tree-v2-name">Belum ada anggota</div>
                        <div class="tree-v2-meta">Klik kampus untuk tambah anggota</div>
                      </article>
                    </li>
                  @endif

                  @foreach ($group['branches'] as $node)
                    @include('dashboard.partials.tree-person-node', [
                      'node' => $node,
                      'campusId' => $groupCampusId,
                      'campusName' => $group['name'],
                    ])
                  @endforeach

                  @if ($group['unassigned_akk']->isNotEmpty())
                    <li class="tree-v2-item">
                      <article
                        class="tree-v2-node tree-v2-group is-actionable"
                        data-tree-v2-node-action="unassigned-akk"
                        data-node-name="AKK tanpa PKK"
                        data-node-meta="{{ $group['unassigned_akk']->count() }} AKK - {{ $group['name'] }}"
                        data-campus-id="{{ $groupCampusId ?: '' }}"
                        data-campus-name="{{ $group['name'] }}"
                        data-search-name="AKK tanpa PKK {{ $group['name'] }}"
                        tabindex="0"
                        role="button"
                        aria-label="Aksi untuk AKK tanpa PKK {{ $group['name'] }}"
                      >
                        <div class="tree-v2-node-head">
                          <div class="tree-v2-name">AKK tanpa PKK</div>
                          <span class="badge">{{ $group['unassigned_akk']->count() }}</span>
                        </div>
                        <div class="tree-v2-meta">{{ $group['name'] }}</div>
                      </article>
                      <ul class="tree-v2-children tree-v2-level-members">
                        @foreach ($group['unassigned_akk'] as $row)
                          <li class="tree-v2-item">
                            <article
                              class="tree-v2-node tree-v2-person is-akk is-actionable"
                              data-tree-v2-node-action="akk"
                              data-node-name="{{ $row->nama_lengkap }}"
                              data-node-meta="{{ $row->username }}{{ $row->angkatan ? ' - '.$row->angkatan : '' }}"
                              data-campus-id="{{ $row->kampus_id ?: $groupCampusId ?: '' }}"
                              data-campus-name="{{ $row->kampus?->nama_kampus ?: $group['name'] }}"
                              data-person-id="{{ $row->user_id }}"
                              data-search-name="{{ $row->nama_lengkap }}"
                              tabindex="0"
                              role="button"
                              aria-label="Aksi untuk {{ $row->nama_lengkap }}"
                            >
                              <div class="tree-v2-node-head">
                                <div class="tree-v2-name" title="{{ $row->nama_lengkap }}">{{ $row->nama_lengkap }}</div>
                                <span class="badge">AKK</span>
                              </div>
                              <div class="tree-v2-meta">{{ $row->username }}{{ $row->angkatan ? ' - '.$row->angkatan : '' }}</div>
                            </article>
                          </li>
                        @endforeach
                      </ul>
                    </li>
                  @endif
                </ul>
              </li>
            @endforeach
          </ul>
        </div>
      </div>
    </div>
  @endif
</section>
