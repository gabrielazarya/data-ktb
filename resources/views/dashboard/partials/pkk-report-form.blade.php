@php
  $report = $report ?? null;
  $method = $method ?? 'POST';
  $modalId = $modalId ?? '';
  $isCurrentModal = old('_modal_id') === $modalId;
  $fieldValue = fn (string $field, $default = null) => $isCurrentModal ? old($field, $default) : $default;
  $selectedAttendance = collect($fieldValue('anggota_hadir', $report?->anggota_hadir ?? []))
      ->map(fn ($id) => (int) $id)
      ->all();
@endphp

<form method="POST" action="{{ $action }}" class="compact-edit-form report-form">
  @csrf
  @if ($method !== 'POST')
    @method($method)
  @endif
  <input type="hidden" name="_modal_id" value="{{ $modalId }}">
  <div class="form-grid">
    <div class="field">
      <label for="{{ $modalId }}-date">Tanggal Pertemuan</label>
      <input
        id="{{ $modalId }}-date"
        type="date"
        name="tanggal_pertemuan"
        value="{{ $fieldValue('tanggal_pertemuan', $report?->tanggal_pertemuan?->format('Y-m-d') ?? now()->format('Y-m-d')) }}"
        required
      >
    </div>
    <div class="field">
      <label for="{{ $modalId }}-meeting">Pertemuan Ke</label>
      <input
        id="{{ $modalId }}-meeting"
        type="number"
        name="pertemuan_ke"
        value="{{ $fieldValue('pertemuan_ke', $report?->pertemuan_ke) }}"
        min="1"
        max="999"
      >
    </div>
    <div class="field">
      <label for="{{ $modalId }}-material">Bahan yang Dibahas</label>
      <textarea id="{{ $modalId }}-material" name="bahan" maxlength="2000" required>{{ $fieldValue('bahan', $report?->bahan) }}</textarea>
    </div>
    <div class="field">
      <label for="{{ $modalId }}-summary">Ringkasan Pembahasan</label>
      <textarea id="{{ $modalId }}-summary" name="ringkasan" maxlength="5000">{{ $fieldValue('ringkasan', $report?->ringkasan) }}</textarea>
    </div>
    <div class="field is-full">
      <label>Anggota Hadir</label>
      @if ($group->anggota->isEmpty())
        <div class="empty-state">Belum ada anggota di kelompok ini.</div>
      @else
        <div class="checkbox-stack">
          @foreach ($group->anggota as $member)
            @php
              $attendanceRow = $report?->relationLoaded('attendanceRows')
                  ? $report->attendanceRows->firstWhere('user_id', $member->user_id)
                  : null;
              $selectedStatus = $attendanceRow?->status
                  ?: (in_array((int) $member->user_id, $selectedAttendance, true) ? 'hadir' : 'alpa');
            @endphp
            <label class="checkbox-field attendance-field">
              <span>
                {{ $member->nama_lengkap }}
                @if ($member->isStaff())
                  <span class="badge staff">Staff</span>
                @endif
              </span>
              <select name="attendance_statuses[{{ $member->user_id }}]" aria-label="Status {{ $member->nama_lengkap }}">
                @foreach (['hadir' => 'Hadir', 'izin' => 'Izin', 'sakit' => 'Sakit', 'alpa' => 'Alpa'] as $statusValue => $statusLabel)
                  <option value="{{ $statusValue }}" {{ $selectedStatus === $statusValue ? 'selected' : '' }}>{{ $statusLabel }}</option>
                @endforeach
              </select>
            </label>
          @endforeach
        </div>
      @endif
    </div>
    <div class="field">
      <label for="{{ $modalId }}-notes">Catatan</label>
      <textarea id="{{ $modalId }}-notes" name="catatan" maxlength="5000">{{ $fieldValue('catatan', $report?->catatan) }}</textarea>
    </div>
    <div class="field">
      <label for="{{ $modalId }}-next">Rencana Tindak Lanjut</label>
      <textarea id="{{ $modalId }}-next" name="rencana_lanjutan" maxlength="5000">{{ $fieldValue('rencana_lanjutan', $report?->rencana_lanjutan) }}</textarea>
    </div>
    <div class="field is-full">
      <label for="{{ $modalId }}-issues">Kendala</label>
      <textarea id="{{ $modalId }}-issues" name="kendala" maxlength="5000">{{ $fieldValue('kendala', $report?->kendala) }}</textarea>
    </div>
  </div>
  <div class="form-actions">
    <button class="btn is-compact" type="submit">Simpan Laporan</button>
    <button class="btn is-compact" type="button" data-modal-close>Batal</button>
  </div>
</form>
