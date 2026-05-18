@extends('adminlte::page')

@section('title', 'Data PKK')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Data Profil PKK</h1>
        <a href="{{ route('pkk.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Tambah PKK</a>
    </div>
@stop

@section('content')
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="card">
        <div class="card-body p-0 table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th style="width: 10px">#</th>
                        <th>Username</th>
                        <th>Nama Lengkap</th>
                        <th>Asal Kampus</th>
                        <th>Angkatan</th>
                        <th>Waktu Dibuat</th>
                        <th>Status</th>
                        <th style="width: 150px">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pkks as $pkk)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $pkk->username }}</td>
                            <td>{{ $pkk->nama_lengkap }}</td>
                            <td>{{ $pkk->kampus->nama_kampus ?? '-' }}</td>
                            <td>{{ $pkk->angkatan }}</td>
                            <td>{{ $pkk->created_at ? $pkk->created_at->format('d M Y, H:i') : '-' }}</td>
                            <td>
                                @if($pkk->is_active)
                                    <span class="badge badge-success">Aktif</span>
                                @else
                                    <span class="badge badge-danger">Non-Aktif</span>
                                @endif
                            </td>
                            <td>
                                <!-- <a href="{{ route('pkk.show', $pkk->user_id) }}" class="btn btn-sm btn-info" title="Detail"><i class="fas fa-eye"></i></a> -->
                                <a href="{{ route('pkk.edit', $pkk->user_id) }}" class="btn btn-sm btn-warning" title="Edit"><i class="fas fa-edit"></i></a>
                                
                                <button type="button" class="btn btn-sm btn-danger" title="Hapus" data-toggle="modal" data-target="#modal-delete-{{ $pkk->user_id }}">
                                    <i class="fas fa-trash"></i>
                                </button>

                                <!-- Modal Konfirmasi Hapus -->
                                <div class="modal fade" id="modal-delete-{{ $pkk->user_id }}" tabindex="-1" role="dialog" aria-hidden="true">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header bg-danger">
                                                <h5 class="modal-title">Konfirmasi Hapus</h5>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body">
                                                Apakah Anda yakin ingin menghapus profil PKK <strong>{{ $pkk->nama_lengkap }}</strong> ({{ $pkk->username }})? Data ini tidak dapat dikembalikan.
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                                                <form action="{{ route('pkk.destroy', $pkk->user_id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger">Ya, Hapus!</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center">Belum ada data PKK.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@stop

@section('css')
@stop

@section('js')
@stop
