@extends('layouts.app')

@section('content')
@if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="container">
    <h2>Tambah Vendor</h2>

    <form action="{{ route('vendor.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="form-group">
            <label for="nama">Nama</label>
            <input type="text" class="form-control" name="nama" value="{{ old('nama') }}" required>
        </div>
        <div class="form-group">
            <label for="keterangan">Keterangan</label>
            <input type="text" class="form-control" name="keterangan" value="{{ old('keterangan') }}">
        </div>
        <div class="form-group">
            <label for="jam_operasional">Jam Operasional</label>
            <input type="text" class="form-control" name="jam_operasional" value="{{ old('jam_operasional') }}" required>
        </div>
        <div class="form-group">
            <label for="nomor_hp">Nomor HP</label>
            <input type="text" class="form-control" name="nomor_hp" value="{{ old('nomor_hp') }}" required>
        </div>
        <div class="form-group">
            <label for="location_link">Link Lokasi</label>
            <input type="text" class="form-control" name="location_link" value="{{ old('location_link') }}">
        </div>
        <div class="form-group">
            <label for="gambar_vendor">Gambar Vendor</label>
            <input type="file" class="form-control" name="gambar_vendor">
        </div>

        @if(Auth::user()->role != 'sales')
            <!-- Untuk Admin: Dropdown Kota -->
            <div class="form-group">
                <label for="kota">Kota</label>
                <select name="kota" class="form-control" id="kota" required>
                    <option value="">-- Pilih Kota --</option>
                    @foreach ($daerahs as $daerah)
                        <option value="{{ $daerah->kota }}" {{ old('kota') == $daerah->kota ? 'selected' : '' }}>
                            {{ $daerah->kota }}
                        </option>
                    @endforeach
                </select>
            </div>
        @else
            <!-- Untuk Sales: Kota diambil dari user -->
            <input type="hidden" name="kota" value="{{ Auth::user()->daerah->kota }}">
            <div class="form-group">
                <label for="kota">Kota</label>
                <input type="text" class="form-control" value="{{ Auth::user()->daerah->kota }}" disabled>
            </div>
        @endif

        <!-- Dropdown Wilayah -->
        <div class="form-group">
            <label for="wilayah_id">Wilayah</label>
            <select name="wilayah_id" class="form-control" id="wilayah_id" required>
                <option value="">Pilih Wilayah</option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Simpan</button>
    </form>
</div>

@endsection

@push('scripts')
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script>
    // Untuk admin: saat dropdown kota berubah, ambil wilayah berdasarkan kota yang dipilih
    $(document).on('change', '#kota', function() {
        var kota = $(this).val();
        var $wilayahSelect = $('#wilayah_id');
        $wilayahSelect.empty();

        if (kota) {
            $.ajax({
                url: '/wilayah/' + kota,
                type: 'GET',
                dataType: 'json',
                success: function (data) {
                    if (data.length > 0) {
                        $wilayahSelect.append('<option value="">Pilih Wilayah</option>');
                        data.forEach(function (item) {
                            $wilayahSelect.append('<option value="' + item.id + '">' + item.nama + '</option>');
                        });
                    } else {
                        $wilayahSelect.append('<option value="">Wilayah tidak tersedia</option>');
                    }
                },
                error: function (xhr, status, error) {
                    console.error("AJAX Error:", error);
                }
            });
        } else {
            $wilayahSelect.append('<option value="">Pilih Kota Terlebih Dahulu</option>');
        }
    });

    // Untuk sales: otomatis muat dropdown wilayah berdasarkan kota sales saat halaman siap
    $(document).ready(function(){
        @if(Auth::user()->role == 'sales')
            var kota = "{{ Auth::user()->daerah->kota }}";
            var $wilayahSelect = $('#wilayah_id');
            $.ajax({
                url: '/wilayah/' + kota,
                type: 'GET',
                dataType: 'json',
                success: function (data) {
                    $wilayahSelect.empty();
                    if (data.length > 0) {
                        $wilayahSelect.append('<option value="">Pilih Wilayah</option>');
                        data.forEach(function (item) {
                            $wilayahSelect.append('<option value="' + item.id + '">' + item.nama + '</option>');
                        });
                    } else {
                        $wilayahSelect.append('<option value="">Wilayah tidak tersedia</option>');
                    }
                },
                error: function (xhr, status, error) {
                    console.error("AJAX Error:", error);
                }
            });
        @endif
    });
</script>
@endpush
