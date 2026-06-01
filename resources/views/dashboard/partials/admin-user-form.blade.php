@php
  $adminUser ??= null;
  $regioOptions ??= collect();
  $isEdit = filled($adminUser);
  $formId = $isEdit ? 'admin-user-edit-'.$adminUser->user_id : 'admin-user-create';
  $surabayaRegio = $regioOptions->firstWhere('nama_regio', 'Surabaya');
  $selectedRegioId = old('regio_id', $isEdit ? $adminUser->regio_id : $surabayaRegio?->regio_id);
  $selectedAdminType = old('admin_tipe', $isEdit ? $adminUser->admin_tipe : 'pelihat');
@endphp

<form method="POST" action="{{ $isEdit ? route('dashboard.pengguna.update', $adminUser) : route('dashboard.pengguna.store') }}" class="compact-edit-form">
  @csrf
  <input type="hidden" name="_modal_id" value="{{ $isEdit ? 'modal-admin-user-edit-'.$adminUser->user_id : 'modal-admin-user-create' }}">
  @if ($isEdit)
    @method('PUT')
  @endif
  <div class="form-grid">
    <div class="field is-full">
      <label for="{{ $formId }}-nama">Nama Lengkap</label>
      <input id="{{ $formId }}-nama" type="text" name="nama_lengkap" value="{{ old('nama_lengkap', $isEdit ? $adminUser->nama_lengkap : '') }}" maxlength="256" required>
    </div>
    <div class="field">
      <label for="{{ $formId }}-regio">Regio</label>
      <select id="{{ $formId }}-regio" name="regio_id" required>
        <option value="">Pilih regio</option>
        @foreach ($regioOptions as $regio)
          <option value="{{ $regio->regio_id }}" {{ (string) $selectedRegioId === (string) $regio->regio_id ? 'selected' : '' }}>
            {{ $regio->nama_regio }}{{ $regio->is_active ? '' : ' (Nonaktif)' }}
          </option>
        @endforeach
      </select>
    </div>
    <div class="field">
      <label for="{{ $formId }}-admin-tipe">Tipe Admin</label>
      <select id="{{ $formId }}-admin-tipe" name="admin_tipe" required>
        <option value="pelihat" {{ $selectedAdminType === 'pelihat' ? 'selected' : '' }}>Pelihat</option>
        <option value="editor" {{ $selectedAdminType === 'editor' ? 'selected' : '' }}>Editor</option>
      </select>
    </div>
  </div>
  <div class="form-actions">
    <button class="btn is-compact" type="submit">{{ $isEdit ? 'Simpan Pengguna' : 'Tambah Pengguna' }}</button>
  </div>
</form>
