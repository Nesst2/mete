@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>Daftar Daerah</h2>
        <a href="{{ route('daerah.create') }}" class="btn btn-primary mb-3">Tambah Daerah</a>

        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <table class="table">
            <thead>
                <tr>
                    <th>Kota</th>
                    <th>Provinsi</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($daerahs as $daerah)
                    <tr>
                        <td>{{ $daerah->kota }}</td>
                        <td>{{ $daerah->provinsi }}</td>
                        <td>
                            <a href="{{ route('daerah.edit', $daerah->id) }}" class="btn btn-warning">Edit</a>
                            <form action="{{ route('daerah.destroy', $daerah->id) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
