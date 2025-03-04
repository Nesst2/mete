@extends('layouts.app')

@section('content')
<div class="container">
    <h2>History Tagihan</h2>
    
    
    @if(Auth::user()->role == 'admin')
        <form action="{{ route('tagihan.history') }}" method="GET" class="mb-3">
            <div class="card p-3 mb-3">
                <h4>Filter Tagihan</h4>
                <p class="text-muted">
                    Pilih salah satu metode filter: <br>
                    - <strong>Berdasarkan Bulan</strong>: Isi filter bulan saja untuk melihat data tagihan pada bulan tersebut. <br>
                    - <strong>Berdasarkan Rentang Tanggal</strong>: Isi "Dari Tanggal" dan "Sampai Tanggal" untuk melihat data pada rentang tanggal yang diinginkan. <br>
                </p>
                <div class="row">
                    <!-- Filter Berdasarkan Bulan -->
                    <div class="col-md-4 mb-2">
                        <label for="month">Berdasarkan Bulan:</label>
                        <input type="month" id="month" name="month" class="form-control" 
                               value="{{ request('month') ?: (isset($year) && isset($month) ? $year.'-'.sprintf('%02d', $month) : '') }}">
                    </div>
                    <!-- Filter Berdasarkan Rentang Tanggal -->
                    <div class="col-md-4 mb-2">
                        <label for="start_date">Dari Tanggal:</label>
                        <input type="date" id="start_date" name="start_date" class="form-control" 
                               value="{{ request('start_date') }}">
                    </div>
                    <div class="col-md-4 mb-2">
                        <label for="end_date">Sampai Tanggal:</label>
                        <input type="date" id="end_date" name="end_date" class="form-control" 
                               value="{{ request('end_date') }}">
                    </div>
                </div>
                <!-- Filter Berdasarkan Kota -->
                <div class="row mt-2">
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
            <button type="submit" class="btn btn-primary">Filter</button>
        </form>
            <a href="{{ route('tagihan.export', request()->query()) }}" class="btn btn-success">
                Export ke Excel
            </a>

    @else
        <p>Menampilkan data tagihan untuk bulan {{ \Carbon\Carbon::create($year, $month)->translatedFormat('F Y') }}.</p>
    @endif

    
    <table class="table table-bordered">
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

    <!-- Tampilkan pagination -->
    <div class="d-flex justify-content-center">
        {{ $tagihan->appends(request()->input())->links() }}
    </div>
</div>
@endsection
