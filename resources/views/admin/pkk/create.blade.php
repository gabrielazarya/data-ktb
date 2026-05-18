@extends('adminlte::page')

@section('title', 'Tambah PKK')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Tambah Profil PKK</h1>
        <a href="{{ route('pkk.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-md-8">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Form Informasi PKK</h3>
                </div>
                
                <form action="{{ route('pkk.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="card-body">
                        
                        <div class="form-group">
                            <label for="username">Username</label>
                            <input type="text" class="form-control @error('username') is-invalid @enderror" id="username" name="username" value="{{ old('username') }}" placeholder="Masukkan username" required>
                            @error('username')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="password">Password</label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" placeholder="Minimal 8 karakter" required>
                            @error('password')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="nama_lengkap">Nama Lengkap</label>
                            <input type="text" class="form-control @error('nama_lengkap') is-invalid @enderror" id="nama_lengkap" name="nama_lengkap" value="{{ old('nama_lengkap') }}" placeholder="Masukkan nama lengkap" required>
                            @error('nama_lengkap')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="tanggal_lahir">Tanggal Lahir</label>
                            <input type="date" class="form-control @error('tanggal_lahir') is-invalid @enderror" id="tanggal_lahir" name="tanggal_lahir" value="{{ old('tanggal_lahir') }}" required>
                            @error('tanggal_lahir')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="kampus_id">Asal Kampus</label>
                            <select class="form-control @error('kampus_id') is-invalid @enderror" id="kampus_id" name="kampus_id" required>
                                <option value="">-- Pilih Kampus --</option>
                                @foreach($kampuses as $kampus)
                                    <option value="{{ $kampus->kampus_id }}" {{ old('kampus_id') == $kampus->kampus_id ? 'selected' : '' }}>
                                        {{ $kampus->nama_kampus }}
                                    </option>
                                @endforeach
                            </select>
                            @error('kampus_id')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="angkatan">Angkatan</label>
                            <input type="number" class="form-control @error('angkatan') is-invalid @enderror" id="angkatan" name="angkatan" value="{{ old('angkatan', date('Y')) }}" min="2000" max="{{ date('Y') + 1 }}" required>
                            @error('angkatan')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="foto_profil">Foto Profil (Opsional)</label>
                            <div class="input-group">
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input @error('foto_profil') is-invalid @enderror" id="foto_profil" name="foto_profil" accept="image/*">
                                    <label class="custom-file-label" for="foto_profil">Pilih file</label>
                                </div>
                            </div>
                            @error('foto_profil')
                                <span class="text-danger text-sm">{{ $message }}</span>
                            @enderror
                        </div>

                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">Simpan Profil PKK</button>
                        <a href="{{ route('pkk.index') }}" class="btn btn-default float-right">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@section('css')
@stop

@section('js')
    <script>
        // Update label file input dengan nama file yang dipilih
        $('.custom-file-input').on('change', function() {
            let fileName = $(this).val().split('\\').pop();
            $(this).next('.custom-file-label').addClass("selected").html(fileName);
        });
    </script>
@stop
