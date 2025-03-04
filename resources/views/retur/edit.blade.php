@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Edit Retur</h2>

    <form action="{{ route('retur.update', $retur->id) }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="form-group">
            <label for="vendor_id">Vendor</label>
            <select name="vendor_id" class="form-control" required>
                <option value="">Pilih Vendor</option>
                @foreach($vendors as $vendor)
                    <option value="{{ $vendor->id }}" {{ $retur->vendor_id == $vendor->id ? 'selected' : '' }}>{{ $vendor->nama }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="nominal_debet">Nominal Debet</label>
            <input type="number" step="0.01" class="form-control" name="nominal_debet" value="{{ $retur->nominal_debet }}" required>
        </div>

        <div class="form-group">
            <label for="jumlah_retur">Jumlah Retur</label>
            <input type="number" class="form-control" name="jumlah_retur" value="{{ $retur->jumlah_retur }}" required>
        </div>

        <div class="form-group">
            <label for="keterangan">Keterangan</label>
            <textarea class="form-control" name="keterangan">{{ $retur->keterangan }}</textarea>
        </div>

        <button type="submit" class="btn btn-primary">Simpan</button>
    </form>
</div>
@endsection
