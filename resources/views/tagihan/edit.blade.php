@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Edit Retur Tagihan Vendor : {{ $vendor->nama }}</h2>

    <form action="{{ route('tagihan.update', $vendor->id) }}" method="POST">
        @csrf
        @method('PUT')
        <input type="hidden" name="nama" value="{{ $vendor->nama }}">
        @foreach ($tagihanSebelumnya as $index => $tagihan)
            <!-- Form Dynamic -->
            <div class="mb-3" id="form{{ $index+1 }}">
                <label for="status_kunjungan{{ $index+1 }}" class="form-label">
                    Status Kunjungan (Form {{ $index+1 }})
                </label>
                <select class="form-select" id="status_kunjungan{{ $index+1 }}" name="status_kunjungan{{ $index+1 }}" disabled>
                    <option value="">Pilih Status</option>
                    <option value="ada orang" {{ $tagihan->status_kunjungan == 'ada orang' ? 'selected' : '' }}>Ada Orang</option>
                    <option value="tertunda" {{ $tagihan->status_kunjungan == 'tertunda' ? 'selected' : '' }}>Tertunda</option>
                    <option value="masih" {{ $tagihan->status_kunjungan == 'masih' ? 'selected' : '' }}>Masih</option>
                    <option value="tidak ada orang" {{ $tagihan->status_kunjungan == 'tidak ada orang' ? 'selected' : '' }}>Tidak Ada Orang</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="nominal{{ $index+1 }}" class="form-label">
                    Nominal (Form {{ $index+1 }})
                </label>
                <input type="number" class="form-control" id="nominal{{ $index+1 }}" name="nominal{{ $index+1 }}"
                    value="{{ number_format($tagihan->uang_masuk, 0, '', '') }}" disabled>
            </div>
        
            <!-- Retur Input -->
            <div class="mb-3">
                <label class="form-label">Edit Nominal Retur (Form {{ $index+1 }})</label>
                <input type="number" class="form-control" name="retur[{{ $tagihan->id }}]" 
                    value="{{ isset($returData[$tagihan->id]) ? $returData[$tagihan->id]->jumlah_retur : 0 }}" 
                    min="0">
            </div>
        
            <!-- Tanggal Update Retur -->
            @if(isset($returData[$tagihan->id]) && $returData[$tagihan->id]->updated_at)
                <p>
                    <small class="text-muted">Terakhir Diupdate: 
                        <span class="badge bg-info text-dark">
                            {{ \Carbon\Carbon::parse($returData[$tagihan->id]->updated_at)->format('H-i : d-m-Y') }}
                        </span>
                    </small>
                </p>
            @endif
        @endforeach
    
        <button type="submit" class="btn btn-primary">Update Retur</button>
        <a href="{{ route('tagihan.index') }}" class="btn btn-secondary">Kembali</a>
    </form>
</div>
@endsection
