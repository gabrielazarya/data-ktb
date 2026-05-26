@php
  $role = $row->role;
  $routeName = $role === 'pkk' ? 'dashboard.pkk.update' : 'dashboard.akk.update';
  $roleLabel = strtoupper($role);
@endphp

<form method="POST" action="{{ route($routeName, $row) }}" class="compact-edit-form">
  @csrf
  <input type="hidden" name="_modal_id" value="modal-pemuridan-edit-{{ $row->user_id }}">
  @method('PUT')
  <div class="form-grid">
    <div class="field">
      <label for="{{ $role }}-edit-username-{{ $row->user_id }}">Username</label>
      <input id="{{ $role }}-edit-username-{{ $row->user_id }}" type="text" name="username" value="{{ $row->username }}" maxlength="256" required>
    </div>
    <div class="field">
      <label for="{{ $role }}-edit-password-{{ $row->user_id }}">Password Baru</label>
      <input id="{{ $role }}-edit-password-{{ $row->user_id }}" type="password" name="password" minlength="6">
    </div>
    <div class="field is-full">
      <label for="{{ $role }}-edit-nama-{{ $row->user_id }}">Nama Lengkap</label>
      <input id="{{ $role }}-edit-nama-{{ $row->user_id }}" type="text" name="nama_lengkap" value="{{ $row->nama_lengkap }}" maxlength="256" required>
    </div>
    <div class="field">
      <label for="{{ $role }}-edit-kampus-{{ $row->user_id }}">Kampus</label>
      <select id="{{ $role }}-edit-kampus-{{ $row->user_id }}" name="kampus_id">
        <option value="" {{ $row->kampus_id ? '' : 'selected' }}>Tanpa kampus</option>
        @foreach ($campusOptions as $option)
          <option value="{{ $option->kampus_id }}" {{ (string) $row->kampus_id === (string) $option->kampus_id ? 'selected' : '' }}>
            {{ $option->nama_kampus }}{{ $option->singkatan ? ' ('.$option->singkatan.')' : '' }}
          </option>
        @endforeach
      </select>
    </div>
    @if ($role === 'akk')
      <div class="field">
        <label for="{{ $role }}-edit-pkk-{{ $row->user_id }}">Pemimpin PKK</label>
        <select id="{{ $role }}-edit-pkk-{{ $row->user_id }}" name="pkk_id">
          <option value="" {{ $row->pkk_id ? '' : 'selected' }}>Belum dihubungkan</option>
          @foreach ($pkkOptions as $option)
            <option value="{{ $option->user_id }}" {{ (string) $row->pkk_id === (string) $option->user_id ? 'selected' : '' }}>
              {{ $option->nama_lengkap }}{{ $option->kampus?->singkatan ? ' - '.$option->kampus->singkatan : '' }}
            </option>
          @endforeach
        </select>
      </div>
    @endif
    <div class="field">
      <label for="{{ $role }}-edit-angkatan-{{ $row->user_id }}">Angkatan</label>
      <input id="{{ $role }}-edit-angkatan-{{ $row->user_id }}" type="number" name="angkatan" value="{{ $row->angkatan }}" min="1900" max="{{ date('Y') + 1 }}">
    </div>
    <div class="field">
      <label for="{{ $role }}-edit-tanggal-lahir-{{ $row->user_id }}">Tanggal Lahir</label>
      <input id="{{ $role }}-edit-tanggal-lahir-{{ $row->user_id }}" type="date" name="tanggal_lahir" value="{{ $row->tanggal_lahir?->format('Y-m-d') }}">
    </div>
    <div class="field">
      <label for="{{ $role }}-edit-regio-{{ $row->user_id }}">Regio</label>
      <select id="{{ $role }}-edit-regio-{{ $row->user_id }}" name="regio_id">
        <option value="" {{ $row->regio_id ? '' : 'selected' }}>Tanpa regio</option>
        @foreach ($regioOptions as $option)
          <option value="{{ $option->regio_id }}" {{ (string) $row->regio_id === (string) $option->regio_id ? 'selected' : '' }}>
            {{ $option->nama_regio }}
          </option>
        @endforeach
      </select>
    </div>
    <div class="field">
      <label for="{{ $role }}-edit-jurusan-{{ $row->user_id }}">Jurusan</label>
      <input id="{{ $role }}-edit-jurusan-{{ $row->user_id }}" type="text" name="jurusan" value="{{ $row->jurusan }}" maxlength="255">
    </div>
    <div class="field">
      <label for="{{ $role }}-edit-kategori-{{ $row->user_id }}">Kategori Jurusan</label>
      <select id="{{ $role }}-edit-kategori-{{ $row->user_id }}" name="kategori_jurusan_id">
        <option value="" {{ $row->kategori_jurusan_id ? '' : 'selected' }}>Tanpa kategori</option>
        @foreach ($kategoriJurusanOptions as $option)
          <option value="{{ $option->kategori_jurusan_id }}" {{ (string) $row->kategori_jurusan_id === (string) $option->kategori_jurusan_id ? 'selected' : '' }}>
            {{ $option->nama_kategori }}
          </option>
        @endforeach
      </select>
    </div>
    @if ($role === 'pkk')
      <label class="checkbox-field">
        <input type="hidden" name="is_target" value="0">
        <input type="checkbox" name="is_target" value="1" {{ $row->is_target ? 'checked' : '' }}>
        Target
      </label>
    @endif
    <label class="checkbox-field">
      <input type="hidden" name="is_active" value="0">
      <input type="checkbox" name="is_active" value="1" {{ $row->is_active ? 'checked' : '' }}>
      Aktif
    </label>
  </div>
  <div class="form-actions">
    <button class="btn is-compact" type="submit">Simpan {{ $roleLabel }}</button>
  </div>
</form>
