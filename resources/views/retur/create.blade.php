@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Tambah Retur</h2>

    <form action="{{ route('retur.store') }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="vendor_id">Vendor</label>
            <select name="vendor_id" class="form-control" required>
                <option value="">Pilih Vendor</option>
                @foreach($vendors as $vendor)
                    <option value="{{ $vendor->id }}" {{ old('vendor_id') == $vendor->id ? 'selected' : '' }}>{{ $vendor->nama }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="nominal_debet">Nominal Debet</label>
            <input type="number" step="0.01" class="form-control" name="nominal_debet" value="{{ old('nominal_debet') }}" required>
        </div>

        <div class="form-group">
            <label for="jumlah_retur">Jumlah Retur</label>
            <input type="number" class="form-control" name="jumlah_retur" value="{{ old('jumlah_retur') }}" required>
        </div>

        <div class="form-group">
            <label for="keterangan">Keterangan</label>
            <textarea class="form-control" name="keterangan">{{ old('keterangan') }}</textarea>
        </div>

        <button type="submit" class="btn btn-primary">Simpan</button>
    </form>
</div>
@endsection
