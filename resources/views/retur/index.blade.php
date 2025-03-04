@extends('layouts.app')

@section('content')
@if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

<div class="container">
    <h2>Daftar Retur</h2>

    <a href="{{ route('retur.create') }}" class="btn btn-primary mb-3">Tambah Retur</a>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Vendor</th>
                <th>Nominal Debet</th>
                <th>Jumlah Retur</th>
                <th>Keterangan</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($returs as $retur)
            <tr>
                <td>{{ $retur->vendor->nama }}</td>
                <td>{{ number_format($retur->nominal_debet, 3) }}</td>
                <td>{{ $retur->jumlah_retur }}</td>
                <td>{{ $retur->keterangan }}</td>
                <td>
                    <a href="{{ route('retur.edit', $retur->id) }}" class="btn btn-warning">Edit</a>
                    <form action="{{ route('retur.destroy', $retur->id) }}" method="POST" style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Hapus</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
