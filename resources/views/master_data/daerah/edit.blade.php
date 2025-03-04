@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>Edit Daerah</h2>

        <form action="{{ route('daerah.update', $daerah->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label for="kota" class="form-label">Kota</label>
                <input type="text" class="form-control" id="kota" name="kota" value="{{ $daerah->kota }}" required>
            </div>
            <div class="mb-3">
                <label for="provinsi" class="form-label">Provinsi</label>
                <input type="text" class="form-control" id="provinsi" name="provinsi" value="{{ $daerah->provinsi }}" required>
            </div>

            <button type="submit" class="btn btn-primary">Update</button>
        </form>
    </div>
@endsection
