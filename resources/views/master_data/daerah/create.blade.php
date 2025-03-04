@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>Tambah Daerah Baru</h2>

        <form action="{{ route('daerah.store') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label for="kota" class="form-label">Kota</label>
                <input type="text" class="form-control" id="kota" name="kota" required>
            </div>
            <div class="mb-3">
                <label for="provinsi" class="form-label">Provinsi</label>
                <input type="text" class="form-control" id="provinsi" name="provinsi" required>
            </div>

            <button type="submit" class="btn btn-primary">Simpan</button>
        </form>
    </div>
@endsection
