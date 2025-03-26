@extends('layouts.app')

@section('content')
<div class="container">
    <h2>History Tagihan</h2>

    <!-- Filter Tagihan Umum -->
    @if(Auth::user()->role == 'admin')
        <form action="{{ route('tagihan.history') }}" method="GET" class="mb-3">
            <div class="card p-3 mb-3">
                <h4>Filter Tagihan</h4>
                <p class="text-muted">
                    Gunakan filter harian untuk menampilkan data tagihan pada tanggal tertentu.
                </p>
                <div class="row">
                    <!-- Filter Berdasarkan Tanggal -->
                    <div class="col-md-4 mb-2">
                        <label for="tanggal">Pilih Tanggal:</label>
                        <input type="date" id="tanggal" name="tanggal" class="form-control"
                            value="{{ request('tanggal') }}">
                    </div>
                    <!-- Filter Berdasarkan Kota -->
                    <div class="col-md-4 mb-2">
                        <label for="kota">Filter Berdasarkan Kota:</label>
                        <select name="kota" id="kota" class="form-control">
                            <option value="">-- Semua Kota --</option>
                            @foreach($kotaList as $kota)
                                <option value="{{ $kota }}" {{ request('kota') == $kota ? 'selected' : '' }}>{{ $kota }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            <!-- Button Filter dan Export -->
            <div class="d-flex align-items-center">
                <button type="submit" class="btn btn-primary" style="margin-right: 20px;">Filter Tagihan</button>
                <!-- Tombol Export Laporan Memicu Modal -->
                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#exportModal">
                    Export Laporan
                </button>
            </div>
        </form>
    @else
        <p>Menampilkan data tagihan untuk tanggal 
            {{ request('tanggal') ? \Carbon\Carbon::parse(request('tanggal'))->translatedFormat('d F Y') : \Carbon\Carbon::now()->translatedFormat('d F Y') }}.
        </p>
    @endif

    <!-- Tampilkan Data Detail Tagihan (History Table) -->
    <table class="table table-bordered mt-3">
        <thead>
            <tr>
                <th>Vendor</th>
                <th>Status Kunjungan</th>
                <th>Uang Masuk</th>
                <th>Retur</th>
                <th>Tanggal Masuk</th>
            </tr>
        </thead>
        <tbody>
            @forelse($tagihan as $data)
                <tr>
                    <td>{{ $data->vendor->nama ?? '-' }}</td>
                    <td>{{ $data->status_kunjungan }}</td>
                    <td>{{ number_format($data->uang_masuk, 0) }}</td>
                    <td>{{ number_format($data->retur ? $data->retur->jumlah_retur : 0, 0) }}</td>
                    <td>
                        @if(Auth::user()->role == 'sales')
                            {{ \Carbon\Carbon::parse($data->tanggal_masuk)->format('d-m-Y') }}
                        @else
                            {{ $data->tanggal_masuk }}
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center">Tidak ada data tagihan untuk periode ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Total Retur dan Rekap Harian (khusus untuk admin) -->
    @if(Auth::user()->role == 'admin')
        <div class="alert alert-info mt-3">
            <h4>Total Retur Tanggal 
                {{ request('tanggal') ? \Carbon\Carbon::parse(request('tanggal'))->translatedFormat('d F Y') : \Carbon\Carbon::now()->translatedFormat('d F Y') }}: 
                {{ number_format($totalRetur, 0) }}
            </h4>
        </div>

        @if($dailyRetur->isNotEmpty())
            <div class="mt-3">
                <h4>Rekap Total Retur Harian</h4>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Total Retur</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($dailyRetur as $date => $sum)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($date)->translatedFormat('d F Y') }}</td>
                                <td>{{ number_format($sum, 0) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    @endif

    <!-- Pagination -->
    <div class="d-flex justify-content-center">
        {{ $tagihan->appends(request()->input())->links() }}
    </div>
</div>

<!-- Modal Export Laporan -->
<div class="modal fade" id="exportModal" tabindex="-1" aria-labelledby="exportModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form action="{{ route('tagihan.export') }}" method="GET">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="exportModalLabel">Pilih Filter Export Laporan</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
              <!-- Pilihan Bulan -->
              <div class="mb-3">
                  <label for="export_month" class="form-label">Bulan Export:</label>
                  <input type="month" id="export_month" name="month" class="form-control" 
                         value="{{ request('month') ?: now()->format('Y-m') }}">
              </div>
              <!-- Pilihan Kota -->
              <div class="mb-3">
                  <label for="export_kota" class="form-label">Kota Export:</label>
                  <select name="kota" id="export_kota" class="form-control">
                      <option value="">-- Semua Kota --</option>
                      @foreach($kotaList as $kota)
                          <option value="{{ $kota }}" {{ request('kota') == $kota ? 'selected' : '' }}>{{ $kota }}</option>
                      @endforeach
                  </select>
              </div>
              <!-- Jika terdapat filter lain yang aktif, masukkan sebagai hidden field -->
              @foreach(request()->except(['month', 'kota']) as $key => $value)
                  <input type="hidden" name="{{ $key }}" value="{{ $value }}">
              @endforeach
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
            <button type="submit" class="btn btn-success">Export Laporan</button>
          </div>
        </div>
    </form>
  </div>
</div>
@endsection
