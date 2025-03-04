@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Tambah Wilayah Baru</h2>

    <form action="{{ route('wilayah.store') }}" method="POST">
        @csrf
        
        <div class="mb-3">
            <label for="nama" class="form-label">Nama Wilayah</label>
            <input type="text" class="form-control" id="nama" name="nama" required>
        </div>

        <div class="mb-3">
            <label for="kota" class="form-label">Kota</label>

            @if(Auth::user()->role == 'sales')
                <!-- Jika Sales, kunci kota (readonly) -->
                <input type="text" class="form-control" value="{{ optional(Auth::user()->daerah)->kota }} - {{ optional(Auth::user()->daerah)->provinsi }}" readonly>
                <input type="hidden" name="kota" value="{{ optional(Auth::user()->daerah)->kota }}">
            @else
                <!-- Jika Admin, tampilkan dropdown biasa -->
                <select class="form-control" id="kota" name="kota" required>
                    <option value="">Pilih Kota</option>
                    @foreach($daerahs as $daerah)
                        <option value="{{ $daerah->kota }}">{{ $daerah->kota }} - {{ $daerah->provinsi }}</option>
                    @endforeach
                </select>
            @endif

        </div>

        <button type="submit" class="btn btn-primary">Simpan</button>
    </form>
</div>
@endsection
