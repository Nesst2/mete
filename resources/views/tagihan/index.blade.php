@extends('layouts.app')

@section('content')
@if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

<div class="container">
    <h2>Daftar Tagihan</h2>
    
    <!-- Link ke halaman History Tagihan -->
    <div class="mb-3">
        <a href="{{ route('tagihan.history') }}" class="btn btn-info">History Tagihan</a>
    </div>
    
    <!-- Form Filter untuk Admin dan Sales -->
    <form action="{{ route('tagihan.index') }}" method="GET" class="mb-3">
        <div class="row">
            <!-- Cari Vendor -->
            <div class="col-md-3 mb-2">
                <label for="search">Cari Vendor:</label>
                <input type="text" name="search" id="search" class="form-control" placeholder="Masukkan kata kunci..." value="{{ request('search') }}">
            </div>

            @if(in_array(Auth::user()->role, ['admin', 'sales']))
            <!-- Filter Berdasarkan Kota (hanya admin) -->
            @if(Auth::user()->role == 'admin')
            <div class="col-md-3 mb-2">
                <label for="kota">Filter Berdasarkan Kota:</label>
                <select name="kota" id="kota" class="form-select" onchange="this.form.submit()">
                    <option value="">-- Semua Kota --</option>
                    @foreach($kotaList as $kota)
                        <option value="{{ $kota }}" {{ request('kota') == $kota ? 'selected' : '' }}>
                            {{ $kota }}
                        </option>
                    @endforeach
                </select>
            </div>
            @endif

            <!-- Filter Berdasarkan Status Vendor (hanya admin) -->
            @if(Auth::user()->role == 'admin')
            <div class="col-md-3 mb-2">
                <label for="status">Filter Berdasarkan Status Vendor:</label>
                <select name="status" id="status" class="form-select" onchange="this.form.submit()">
                    <option value="">-- Semua Status --</option>
                    <option value="aktif" {{ request('status') == 'aktif' ? 'selected' : '' }}>Aktif</option>
                    <option value="nonaktif" {{ request('status') == 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                    <option value="menunggu_verifikasi" {{ request('status') == 'menunggu_verifikasi' ? 'selected' : '' }}>Menunggu Verifikasi</option>
                </select>
            </div>
            @endif

            <!-- Filter Checklist / Status Kunjungan Vendor -->
            <div class="col-md-3 mb-2">
                <label for="checklist">Status Kunjungan Vendor:</label>
                <select name="checklist" id="checklist" class="form-select" onchange="this.form.submit()">
                    <option value="">-- Semua Vendor --</option>
                    <option value="belum" {{ request('checklist') == 'belum' ? 'selected' : '' }}>Belum Pernah Dikunjungi (bulan ini)</option>
                    <option value="sudah" {{ request('checklist') == 'sudah' ? 'selected' : '' }}>Sudah Pernah Dikunjungi (bulan ini)</option>
                </select>
            </div>
            @endif

            <!-- Filter Tanggal Harian (untuk bulan berjalan) -->
            @php
                $defaultMin = \Carbon\Carbon::now()->startOfMonth()->format('Y-m-d');
                $defaultMax = \Carbon\Carbon::now()->endOfMonth()->format('Y-m-d');
            @endphp
            <div class="col-md-3 mb-2">
                <label for="tanggal_filter">Pilih Tanggal:</label>
                <input type="date" name="tanggal_filter" id="tanggal_filter" class="form-control"
                    value="{{ request('tanggal_filter') }}"
                    min="{{ $defaultMin }}" max="{{ $defaultMax }}">
            </div>
        </div>
        <div class="row">
            <div class="col-md-3 mb-2 align-self-end">
                <button type="submit" class="btn btn-primary">Filter</button>
                <a href="{{ route('tagihan.index') }}" class="btn btn-secondary">Clear Filter</a>
            </div>
        </div>
        <div class="row">
            <div class="col">
                <small class="text-muted">
                    Jika Anda mengisi filter tanggal, hanya vendor dengan tagihan pada tanggal tersebut (dalam bulan berjalan) yang akan ditampilkan.<br>
                    Filter checklist akan mempertimbangkan tagihan yang terjadi di bulan berjalan.
                </small>
            </div>
        </div>
    </form>


    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Kode Vendor</th>
                <th>Vendor</th>
                @if(Auth::user()->role == 'admin')
                    <th>Status</th>
                @endif
                <th>Wilayah</th>
                <th>Jam Operasional</th>
                <th>Checklist Kunjungan Vendor</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($vendors as $vendor)
                @php
                    // Ambil tagihan vendor yang terjadi di bulan berjalan
                    $tagihanCurrentMonth = $vendor->tagihan->filter(function($tagihan) use ($defaultMin, $defaultMax) {
                        return $tagihan->tanggal_masuk >= $defaultMin && $tagihan->tanggal_masuk <= $defaultMax;
                    })->sortByDesc('created_at')->values();
                @endphp

                {{-- Filter checklist vendor hanya dengan tagihan bulan berjalan --}}
                @if(request('checklist') == 'belum' && $tagihanCurrentMonth->count() > 0)
                    @continue
                @elseif(request('checklist') == 'sudah' && $tagihanCurrentMonth->count() == 0)
                    @continue
                @endif

                {{-- Untuk Sales: tampilkan vendor hanya jika statusnya bukan nonaktif --}}
                @if(Auth::user()->role == 'sales' && $vendor->status == 'nonaktif')
                    @continue
                @endif

                @php
                    // Jika filter tanggal (tanggal_filter) diisi, ambil tagihan sesuai tanggal tersebut
                    if(request('tanggal_filter')) {
                        $selectedDate = request('tanggal_filter');
                        $tagihans = $tagihanCurrentMonth->filter(function($tagihan) use ($selectedDate) {
                            return \Carbon\Carbon::parse($tagihan->tanggal_masuk)->toDateString() === $selectedDate;
                        })->sortByDesc('created_at')->values();
                    } else {
                        $tagihans = $tagihanCurrentMonth;
                    }
                    
                    // Contoh pengecekan untuk tanda peringatan
                    $redMark = $tagihans->filter(function($tagihan) {
                        return ($tagihan->status_kunjungan == 'ada orang' || $tagihan->status_kunjungan == 'tertunda')
                               && $tagihan->uang_masuk <= 10000;
                    })->count() > 0;
                @endphp

                <tr class="{{ $redMark ? 'table-danger' : '' }}">
                    <td>{{ $vendor->kode_vendor }}</td>
                    <td>
                        {!! $redMark ? '<i class="bi bi-exclamation-circle-fill text-danger"></i> ' : '' !!}
                        {{ $vendor->nama }}
                    </td>
                    @if(Auth::user()->role == 'admin')
                        <td>{{ $vendor->status_label }}</td>
                    @endif
                    <td>{{ $vendor->wilayah ? $vendor->wilayah->nama : 'N/A' }}</td>
                    <td>{{ $vendor->jam_operasional ?? 'Tidak Diketahui' }}</td>
                    <td>
                        @for ($i = 1; $i <= 15; $i++)
                            @php
                                $tagihan = $tagihans->get($i - 1);
                                // Default: ikon belum dikunjungi (abu-abu)
                                $icon = '<i class="bi bi-circle text-secondary"></i>';
                                if ($tagihan) {
                                    if ($tagihan->status_kunjungan == 'ada orang' && $tagihan->uang_masuk > 0) {
                                        $icon = '<i class="bi bi-check-circle-fill text-success"></i>';
                                    } elseif ($tagihan->status_kunjungan == 'tertunda' && $tagihan->uang_masuk > 0) {
                                        $icon = '<i class="bi bi-clock-fill text-info"></i>';
                                    } elseif ($tagihan->status_kunjungan == 'masih') {
                                        $icon = '<i class="bi bi-dash-circle-fill text-warning"></i>';
                                    } elseif ($tagihan->status_kunjungan == 'tidak ada orang') {
                                        $icon = '<i class="bi bi-x-circle-fill text-danger"></i>';
                                    }
                                }
                            @endphp
                            {!! $icon !!}
                        @endfor
                    </td>
                    <td>
                        <a href="{{ route('tagihan.create', ['vendor_id' => $vendor->id]) }}" class="btn btn-primary">Tambah Tagihan</a>
                        @if(Auth::user()->role == 'admin' && $tagihans->count() > 0)
                            <a href="{{ route('tagihan.edit', ['vendor_id' => $vendor->id]) }}" class="btn btn-warning">Edit Retur</a>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center">Tidak ada data vendor.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Pagination -->
    <div class="d-flex justify-content-center">
        {{ $vendors->links() }}
    </div>
</div>
@endsection
