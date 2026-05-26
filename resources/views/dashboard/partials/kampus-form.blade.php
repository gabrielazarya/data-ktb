@php
  $kampus ??= null;
  $isEdit = filled($kampus);
  $formId = $isEdit ? 'kampus-edit-'.$kampus->kampus_id : 'kampus-create';
@endphp

<form method="POST" action="{{ $isEdit ? route('dashboard.kampus.update', $kampus) : route('dashboard.kampus.store') }}" class="compact-edit-form">
  @csrf
  <input type="hidden" name="_modal_id" value="{{ $isEdit ? 'modal-kampus-edit-'.$kampus->kampus_id : 'modal-kampus-create' }}">
  @if ($isEdit)
    @method('PUT')
  @endif
  <div class="form-grid">
    <div class="field is-full">
      <label for="{{ $formId }}-nama">Nama Kampus</label>
      <input id="{{ $formId }}-nama" type="text" name="nama_kampus" value="{{ $isEdit ? $kampus->nama_kampus : old('nama_kampus') }}" maxlength="256" required>
    </div>
    <div class="field">
      <label for="{{ $formId }}-singkatan">Singkatan</label>
      <input id="{{ $formId }}-singkatan" type="text" name="singkatan" value="{{ $isEdit ? $kampus->singkatan : old('singkatan') }}" maxlength="50">
    </div>
    <label class="checkbox-field">
      <input type="hidden" name="is_active" value="0">
      <input type="checkbox" name="is_active" value="1" {{ $isEdit ? ($kampus->is_active ? 'checked' : '') : (old('is_active', '1') ? 'checked' : '') }}>
      Aktif
    </label>
  </div>
  <div class="form-actions">
    <button class="btn is-compact" type="submit">{{ $isEdit ? 'Simpan Kampus' : 'Tambah Kampus' }}</button>
  </div>
</form>
