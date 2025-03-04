@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>Edit Wilayah</h2>

        <form action="{{ route('wilayah.update', $wilayah->id) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="mb-3">
                <label for="nama" class="form-label">Nama Wilayah</label>
                <input type="text" class="form-control @error('nama') is-invalid @enderror" id="nama" name="nama" value="{{ old('nama', $wilayah->nama) }}" required>
                @error('nama')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="daerah_id" class="form-label">Daerah (Kota/Provinsi)</label>
            
                @if(Auth::user()->role == 'sales')
                    <!-- Jika Sales, kunci kota (readonly) -->
                    <input type="text" class="form-control" value="{{ $wilayah->daerah->kota }} - {{ $wilayah->daerah->provinsi }}" readonly>
                    <input type="hidden" name="daerah_id" value="{{ $wilayah->daerah->id }}">
                @else
                    <!-- Jika Admin, tampilkan dropdown biasa -->
                    <select class="form-select @error('daerah_id') is-invalid @enderror" id="daerah_id" name="daerah_id" required>
                        <option value="">Pilih Daerah</option>
                        @foreach($daerahs as $daerah)
                            <option value="{{ $daerah->id }}" 
                                {{ old('daerah_id', $wilayah->daerah_id) == $daerah->id ? 'selected' : '' }}>
                                {{ $daerah->kota }} - {{ $daerah->provinsi }}
                            </option>
                        @endforeach
                    </select>
                @endif
            
                @error('daerah_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>            

            <button type="submit" class="btn btn-primary">Update</button>
        </form>
    </div>
@endsection
