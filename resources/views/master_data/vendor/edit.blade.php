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
    <h2>Edit Vendor</h2>

    <form action="{{ route('vendor.update', $vendor->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <!-- Field Umum -->
        <div class="form-group">
            <label for="nama">Nama</label>
            <input type="text" class="form-control" name="nama" value="{{ old('nama', $vendor->nama) }}" required>
        </div>
        <div class="form-group">
            <label for="keterangan">Keterangan</label>
            <input type="text" class="form-control" name="keterangan" value="{{ old('keterangan', $vendor->keterangan) }}" required>
        </div>
        <div class="form-group">
            <label for="jam_operasional">Jam Operasional</label>
            <input type="text" class="form-control" name="jam_operasional" value="{{ old('jam_operasional', $vendor->jam_operasional) }}" required>
        </div>
        <div class="form-group">
            <label for="nomor_hp">Nomor HP</label>
            <input type="text" class="form-control" name="nomor_hp" value="{{ old('nomor_hp', $vendor->nomor_hp) }}" required>
        </div>
        <div class="form-group">
            <label for="location_link">Link Lokasi</label>
            <input type="text" class="form-control" name="location_link" value="{{ old('location_link', $vendor->location_link) }}">
        </div>
        <div class="form-group">
            <label for="gambar_vendor">Gambar Vendor</label>
            <input type="file" class="form-control" name="gambar_vendor">
            @if ($vendor->gambar_vendor)
                <p>
                    <img src="{{ asset('storage/' . $vendor->gambar_vendor) }}" alt="Gambar Vendor" style="width: 200px; height: auto;">
                </p>
            @endif
        </div>

        <!-- Field Kota/Daerah -->
        @if(Auth::user()->role != 'sales')
            <!-- Untuk Admin: Dropdown Daerah (Kota) -->
            <div class="form-group">
                <label for="daerah_id">Daerah (Kota)</label>
                <select name="daerah_id" class="form-control" id="daerah_id" required>
                    @foreach ($daerahs as $daerah)
                        <option value="{{ $daerah->id }}" data-kota="{{ $daerah->kota }}" {{ $daerah->id == $vendor->daerah_id ? 'selected' : '' }}>
                            {{ $daerah->kota }}
                        </option>
                    @endforeach
                </select>
                <!-- Input tersembunyi untuk mengirimkan nilai kota -->
                <input type="hidden" name="kota" id="kota" value="{{ old('kota', $vendor->kota) }}">
            </div>
        @else
            <!-- Untuk Sales: Nilai daerah diambil dari data user -->
            <input type="hidden" name="daerah_id" value="{{ Auth::user()->daerah->id }}">
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
                @if(Auth::user()->role != 'sales')
                    @foreach ($wilayahs as $wilayah)
                        <option value="{{ $wilayah->id }}" {{ $wilayah->id == $vendor->wilayah_id ? 'selected' : '' }}>
                            {{ $wilayah->nama }}
                        </option>
                    @endforeach
                @endif
                <!-- Untuk Sales, dropdown wilayah akan diisi via AJAX -->
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Simpan</button>
    </form>
</div>
@endsection

@push('scripts')
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script>
    $(document).ready(function () {
        @if(Auth::user()->role == 'sales')
            // Untuk Sales: muat dropdown wilayah berdasarkan kota user
            var kota = "{{ Auth::user()->daerah->kota }}";
            var vendorWilayahId = "{{ $vendor->wilayah_id }}";
            $.ajax({
                url: '/wilayah/' + kota,
                type: 'GET',
                dataType: 'json',
                success: function (data) {
                    $('#wilayah_id').empty();
                    if (data.length > 0) {
                        $('#wilayah_id').append('<option value="">Pilih Wilayah</option>');
                        data.forEach(function (wilayah) {
                            $('#wilayah_id').append('<option value="' + wilayah.id + '">' + wilayah.nama + '</option>');
                        });
                        $('#wilayah_id').val(vendorWilayahId);
                    } else {
                        $('#wilayah_id').append('<option value="">Wilayah tidak tersedia</option>');
                    }
                },
                error: function (xhr, status, error) {
                    console.error("AJAX Error:", error);
                }
            });
        @else
            // Untuk Admin: ketika dropdown daerah (kota) berubah, perbarui input tersembunyi dan dropdown wilayah
            $('#daerah_id').change(function () {
                var selectedOption = $("#daerah_id option:selected");
                var kota = selectedOption.data('kota');
                $('#kota').val(kota); // Perbarui nilai input tersembunyi

                $.ajax({
                    url: '/wilayah/' + kota,
                    type: 'GET',
                    dataType: 'json',
                    success: function (data) {
                        $('#wilayah_id').empty();
                        $('#wilayah_id').append('<option value="">Pilih Wilayah</option>');
                        data.forEach(function (wilayah) {
                            $('#wilayah_id').append('<option value="' + wilayah.id + '">' + wilayah.nama + '</option>');
                        });
                    },
                    error: function (xhr, status, error) {
                        console.error("AJAX Error:", error);
                    }
                });
            });
        @endif
    });
</script>
@endpush
