@php
  $roleLabel = strtoupper($role);
  $routeName = $role === 'pkk' ? 'dashboard.pkk.store' : 'dashboard.akk.store';
  $formId = $role.'-create';
  $isOldRole = $oldFormRole === $role;
@endphp

<form method="POST" action="{{ route($routeName) }}" class="compact-edit-form">
  @csrf
  <input type="hidden" name="_form_role" value="{{ $role }}">
  <input type="hidden" name="_modal_id" value="modal-{{ $role }}-create">
  <div class="form-grid">
    <div class="field">
      <label for="{{ $formId }}-username">Username</label>
      <input id="{{ $formId }}-username" type="text" name="username" value="{{ $isOldRole ? old('username') : '' }}" maxlength="256" required>
    </div>
    <div class="field">
      <label for="{{ $formId }}-password">Password</label>
      <input id="{{ $formId }}-password" type="password" name="password" minlength="6" required>
    </div>
    <div class="field is-full">
      <label for="{{ $formId }}-nama">Nama Lengkap</label>
      <input id="{{ $formId }}-nama" type="text" name="nama_lengkap" value="{{ $isOldRole ? old('nama_lengkap') : '' }}" maxlength="256" required>
    </div>
    <div class="field">
      <label for="{{ $formId }}-kampus">Kampus</label>
      <select id="{{ $formId }}-kampus" name="kampus_id">
        <option value="">Tanpa kampus</option>
        @foreach ($campusOptions as $option)
          <option value="{{ $option->kampus_id }}" {{ $isOldRole && (string) old('kampus_id') === (string) $option->kampus_id ? 'selected' : '' }}>
            {{ $option->nama_kampus }}{{ $option->singkatan ? ' ('.$option->singkatan.')' : '' }}
          </option>
        @endforeach
      </select>
    </div>
    @if ($role === 'akk')
      <div class="field">
        <label for="{{ $formId }}-pkk">Pemimpin PKK</label>
        <select id="{{ $formId }}-pkk" name="pkk_id">
          <option value="">Belum dihubungkan</option>
          @foreach ($pkkOptions as $option)
            <option value="{{ $option->user_id }}" {{ $isOldRole && (string) old('pkk_id') === (string) $option->user_id ? 'selected' : '' }}>
              {{ $option->nama_lengkap }}{{ $option->kampus?->singkatan ? ' - '.$option->kampus->singkatan : '' }}
            </option>
          @endforeach
        </select>
      </div>
    @endif
    <div class="field">
      <label for="{{ $formId }}-angkatan">Angkatan</label>
      <input id="{{ $formId }}-angkatan" type="number" name="angkatan" value="{{ $isOldRole ? old('angkatan') : '' }}" min="1900" max="{{ date('Y') + 1 }}">
    </div>
    <div class="field">
      <label for="{{ $formId }}-tanggal-lahir">Tanggal Lahir</label>
      <input id="{{ $formId }}-tanggal-lahir" type="date" name="tanggal_lahir" value="{{ $isOldRole ? old('tanggal_lahir') : '' }}">
    </div>
    <div class="field">
      <label for="{{ $formId }}-regio">Regio</label>
      <select id="{{ $formId }}-regio" name="regio_id">
        <option value="">Tanpa regio</option>
        @foreach ($regioOptions as $option)
          <option value="{{ $option->regio_id }}" {{ $isOldRole && (string) old('regio_id') === (string) $option->regio_id ? 'selected' : '' }}>
            {{ $option->nama_regio }}
          </option>
        @endforeach
      </select>
    </div>
    <div class="field">
      <label for="{{ $formId }}-jurusan">Jurusan</label>
      <input id="{{ $formId }}-jurusan" type="text" name="jurusan" value="{{ $isOldRole ? old('jurusan') : '' }}" maxlength="255">
    </div>
    <div class="field">
      <label for="{{ $formId }}-kategori">Kategori Jurusan</label>
      <select id="{{ $formId }}-kategori" name="kategori_jurusan_id">
        <option value="">Tanpa kategori</option>
        @foreach ($kategoriJurusanOptions as $option)
          <option value="{{ $option->kategori_jurusan_id }}" {{ $isOldRole && (string) old('kategori_jurusan_id') === (string) $option->kategori_jurusan_id ? 'selected' : '' }}>
            {{ $option->nama_kategori }}
          </option>
        @endforeach
      </select>
    </div>
    @if ($role === 'pkk')
      <label class="checkbox-field">
        <input type="hidden" name="is_target" value="0">
        <input type="checkbox" name="is_target" value="1" {{ $isOldRole && old('is_target') ? 'checked' : '' }}>
        Target
      </label>
    @endif
    <label class="checkbox-field">
      <input type="hidden" name="is_active" value="0">
      <input type="checkbox" name="is_active" value="1" {{ ! $isOldRole || old('is_active', '1') ? 'checked' : '' }}>
      Aktif
    </label>
  </div>
  <div class="form-actions">
    <button class="btn is-compact" type="submit">Tambah {{ $roleLabel }}</button>
  </div>
</form>
