@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Daftar Wilayah</h2>
    
    @if(Auth::user()->role == 'admin')
        <!-- Form Search dan Filter Berdasarkan Kota untuk Admin -->
        <form action="{{ route('wilayah.index') }}" method="GET" class="mb-3">
            <div class="row">
                <div class="col-md-4">
                    <label for="search">Cari Wilayah:</label>
                    <input type="text" name="search" id="search" class="form-control" placeholder="Cari berdasarkan nama, kota, atau provinsi" value="{{ request('search') }}">
                </div>
                <div class="col-md-4">
                    <label for="kota">Filter Berdasarkan Kota:</label>
                    <select name="kota" id="kota" class="form-select">
                        <option value="">-- Semua Kota --</option>
                        @foreach($kotaList as $kota)
                            <option value="{{ $kota }}" {{ request('kota') == $kota ? 'selected' : '' }}>{{ $kota }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary">Cari</button>
                </div>
            </div>
        </form>
    @else
        <!-- Untuk Sales, hanya menampilkan fitur search -->
        <form action="{{ route('wilayah.index') }}" method="GET" class="mb-3">
            <div class="row">
                <div class="col-md-8">
                    <label for="search">Cari Wilayah:</label>
                    <input type="text" name="search" id="search" class="form-control" placeholder="Cari berdasarkan nama, kota, atau provinsi" value="{{ request('search') }}">
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary">Cari</button>
                </div>
            </div>
        </form>
    @endif

    <a href="{{ route('wilayah.create') }}" class="btn btn-primary mb-3">Tambah Wilayah</a>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <table class="table">
        <thead>
            <tr>
                <th>Wilayah</th>
                <th>Kota</th>
                <th>Provinsi</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($wilayahs as $wilayah)
                <tr>
                    <td>{{ $wilayah->nama }}</td>
                    <td>{{ $wilayah->daerah->kota }}</td>
                    <td>{{ $wilayah->daerah->provinsi }}</td>
                    <td>
                        <a href="{{ route('wilayah.edit', $wilayah->id) }}" class="btn btn-warning">Edit</a>
                        <form action="{{ route('wilayah.destroy', $wilayah->id) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Pagination Links -->
    <div class="d-flex justify-content-center">
        {{ $wilayahs->links() }}
    </div>
</div>
@endsection
