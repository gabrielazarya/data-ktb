@php
  $regio ??= null;
  $isEdit = filled($regio);
  $formId = $isEdit ? 'regio-edit-'.$regio->regio_id : 'regio-create';
@endphp

<form method="POST" action="{{ $isEdit ? route('dashboard.regio.update', $regio) : route('dashboard.regio.store') }}" class="compact-edit-form">
  @csrf
  <input type="hidden" name="_modal_id" value="{{ $isEdit ? 'modal-regio-edit-'.$regio->regio_id : 'modal-regio-create' }}">
  @if ($isEdit)
    @method('PUT')
  @endif
  <div class="form-grid">
    <div class="field">
      <label for="{{ $formId }}-nama">Nama Regio</label>
      <input id="{{ $formId }}-nama" type="text" name="nama_regio" value="{{ $isEdit ? $regio->nama_regio : old('nama_regio') }}" maxlength="100" required>
    </div>
    <label class="checkbox-field">
      <input type="hidden" name="is_active" value="0">
      <input type="checkbox" name="is_active" value="1" {{ $isEdit ? ($regio->is_active ? 'checked' : '') : (old('is_active', '1') ? 'checked' : '') }}>
      Aktif
    </label>
    <div class="field is-full">
      <label for="{{ $formId }}-keterangan">Keterangan</label>
      <input id="{{ $formId }}-keterangan" type="text" name="keterangan" value="{{ $isEdit ? $regio->keterangan : old('keterangan') }}" maxlength="255">
    </div>
  </div>
  <div class="form-actions">
    <button class="btn is-compact" type="submit">{{ $isEdit ? 'Simpan Regio' : 'Tambah Regio' }}</button>
  </div>
</form>
