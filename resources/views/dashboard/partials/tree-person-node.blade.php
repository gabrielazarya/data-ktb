@php
  $person = $node['person'];
  $nodeGroups = $node['groups'];
  $isPkkNode = $node['is_pkk'];
  $nodeCampusId = $person->kampus_id ?: ($campusId ?? null);
  $nodeCampusName = $person->kampus?->nama_kampus ?: ($campusName ?? 'Tanpa kampus');
  $nodeAction = $isPkkNode ? 'person' : 'akk';
  $nodeLabel = $isPkkNode ? 'PKK' : 'AKK';
  $nodeMeta = $person->username.' - '.$nodeGroups->count().' kelompok';
@endphp

<li class="tree-v2-item">
  <article
    class="tree-v2-node tree-v2-person {{ $isPkkNode ? 'is-pkk' : 'is-akk' }} is-actionable"
    data-tree-v2-node-action="{{ $nodeAction }}"
    data-node-name="{{ $person->nama_lengkap }}"
    data-node-meta="{{ $nodeMeta }}"
    data-campus-id="{{ $nodeCampusId ?: '' }}"
    data-campus-name="{{ $nodeCampusName }}"
    data-person-id="{{ $person->user_id }}"
    data-role="{{ $nodeLabel }}"
    data-search-name="{{ $person->nama_lengkap }}"
    tabindex="0"
    role="button"
    aria-label="Aksi untuk {{ $person->nama_lengkap }}"
  >
    <div class="tree-v2-node-head">
      <div class="tree-v2-name" title="{{ $person->nama_lengkap }}">{{ $person->nama_lengkap }}</div>
      <span class="badge {{ $isPkkNode ? 'neutral' : '' }}">{{ $nodeLabel }}</span>
    </div>
    <div class="tree-v2-meta">{{ $nodeMeta }}</div>
  </article>

  @if ($nodeGroups->isNotEmpty())
    <ul class="tree-v2-children tree-v2-level-groups">
      @foreach ($nodeGroups as $pemuridanGroup)
        @php
          $pemuridanGroupId = $pemuridanGroup['id'] ?? null;
          $pemuridanGroupMembers = $pemuridanGroup['members'];
          $pemuridanGroupMeta = $pemuridanGroupMembers->count().' anggota - pemimpin '.$person->nama_lengkap;
        @endphp
        <li class="tree-v2-item">
          <article
            class="tree-v2-node tree-v2-group is-actionable"
            data-tree-v2-node-action="group"
            data-node-name="{{ $pemuridanGroup['name'] }}"
            data-node-meta="{{ $pemuridanGroupMeta }}"
            data-campus-id="{{ $nodeCampusId ?: '' }}"
            data-campus-name="{{ $nodeCampusName }}"
            data-group-id="{{ $pemuridanGroupId ?: '' }}"
            data-leader-id="{{ $person->user_id }}"
            data-leader-name="{{ $person->nama_lengkap }}"
            data-search-name="{{ $pemuridanGroup['name'] }} {{ $person->nama_lengkap }}"
            tabindex="0"
            role="button"
            aria-label="Aksi untuk {{ $pemuridanGroup['name'] }}"
          >
            <div class="tree-v2-node-head">
              <div class="tree-v2-name" title="{{ $pemuridanGroup['name'] }}">{{ $pemuridanGroup['name'] }}</div>
              <span class="badge">Kelompok</span>
            </div>
            <div class="tree-v2-meta">{{ $pemuridanGroupMeta }}</div>
          </article>

          @if ($pemuridanGroupMembers->isNotEmpty())
            <ul class="tree-v2-children tree-v2-level-members">
              @foreach ($pemuridanGroupMembers as $childNode)
                @include('dashboard.partials.tree-person-node', [
                  'node' => $childNode,
                  'campusId' => $nodeCampusId,
                  'campusName' => $nodeCampusName,
                ])
              @endforeach
            </ul>
          @endif
        </li>
      @endforeach
    </ul>
  @endif
</li>
