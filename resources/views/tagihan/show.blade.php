@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Detail Tagihan</h2>

    <table class="table">
        <tr>
            <th>Vendor</th>
            <td>{{ $tagihan->vendor->nama }}</td>
        </tr>
        <tr>
            <th>Uang Masuk</th>
            <td>{{ number_format($tagihan->uang_masuk, 2) }}</td>
        </tr>
        <tr>
            <th>Daerah</th>
            <td>{{ $tagihan->daerah->nama_daerah }}</td>
        </tr>
        <tr>
            <th>Tanggal Masuk</th>
            <td>{{ $tagihan->tanggal_masuk->format('d-m-Y') }}</td>
        </tr>
        <tr>
            <th>Status Kunjungan</th>
            <td>{{ ucfirst($tagihan->status_kunjungan) }}</td>
        </tr>
    </table>
</div>
@endsection
