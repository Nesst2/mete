@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Tambah User</h2>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('user.store') }}" method="POST">
        @csrf

        <div class="form-group">
            <label for="nama">Nama</label>
            <input type="text" class="form-control" name="nama" value="{{ old('nama') }}" required>
        </div>

        <div class="form-group">
            <label for="tanggal_lahir">Tanggal Lahir</label>
            <input type="date" class="form-control" name="tanggal_lahir" value="{{ old('tanggal_lahir') }}" required>
        </div>

        <div class="form-group">
            <label for="nomor_hp">Nomor HP</label>
            <input type="text" class="form-control" name="nomor_hp" value="{{ old('nomor_hp') }}" required>
            {{-- Jika duplicate, error akan muncul dari validasi --}}
        </div>

        <div class="form-group">
            <label for="role">Role</label>
            <select name="role" id="role" class="form-control" required>
                <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                <option value="sales" {{ old('role') == 'sales' ? 'selected' : '' }}>Sales</option>
            </select>
        </div>

        <!-- Input Daerah hanya tampil jika role adalah 'sales' -->
        <div class="form-group" id="daerah-group" style="display: {{ old('role') == 'sales' ? 'block' : 'none' }};">
            <label for="daerah_id">Daerah</label>
            <select name="daerah_id" class="form-control" required>
                @foreach ($daerahs as $daerah)
                    <option value="{{ $daerah->id }}" {{ old('daerah_id') == $daerah->id ? 'selected' : '' }}>
                        {{ $daerah->kota }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" class="form-control" name="username" value="{{ old('username') }}" required>
        </div>

        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" required>
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" class="form-control" name="password" required>
        </div>

        <div class="form-group">
            <label for="password_confirmation">Konfirmasi Password</label>
            <input type="password" class="form-control" name="password_confirmation" required>
        </div>

        <button type="submit" class="btn btn-primary">Simpan</button>
    </form>
</div>

<script>
    // Menampilkan atau menyembunyikan input daerah berdasarkan role yang dipilih
    document.getElementById('role').addEventListener('change', function() {
        var daerahGroup = document.getElementById('daerah-group');
        if (this.value === 'sales') {
            daerahGroup.style.display = 'block';
        } else {
            daerahGroup.style.display = 'none';
        }
    });

    // Saat form dimuat, pastikan input daerah ditampilkan jika role sudah 'sales'
    if (document.getElementById('role').value === 'sales') {
        document.getElementById('daerah-group').style.display = 'block';
    }
</script>
@endsection
