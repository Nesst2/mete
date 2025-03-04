@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>Edit User</h2>

        <form action="{{ route('user.update', $user->id) }}" method="POST">
            @csrf
            @method('PUT') <!-- Menggunakan PUT untuk update data -->
            <div class="form-group">
                <label for="nama">Nama</label>
                <input type="text" class="form-control" name="nama" value="{{ old('nama', $user->nama) }}" required>
            </div>
            <div class="form-group">
                <label for="tanggal_lahir">Tanggal Lahir</label>
                <input type="date" class="form-control" name="tanggal_lahir" value="{{ old('tanggal_lahir', $user->tanggal_lahir) }}" required>
            </div>
            <div class="form-group">
                <label for="nomor_hp">Nomor HP</label>
                <input type="text" class="form-control" name="nomor_hp" value="{{ old('nomor_hp', $user->nomor_hp) }}" required>
            </div>
            <div class="form-group">
                <label for="role">Role</label>
                <select name="role" id="role" class="form-control" required>
                    <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>Admin</option>
                    <option value="sales" {{ old('role', $user->role) == 'sales' ? 'selected' : '' }}>Sales</option>
                </select>
            </div>
            <div class="form-group" id="daerah-group" style="{{ $user->role == 'sales' ? 'display:block;' : 'display:none;' }}">
                <label for="daerah_id">Daerah</label>
                <select name="daerah_id" class="form-control">
                    @foreach ($daerahs as $daerah)
                        <option value="{{ $daerah->id }}" {{ old('daerah_id', $user->daerah_id) == $daerah->id ? 'selected' : '' }}>
                            {{ $daerah->kota }}, {{ $daerah->provinsi }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" class="form-control" name="username" value="{{ old('username', $user->username) }}" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" class="form-control" id="email" name="email" value="{{ old('email', $user->email ?? '') }}" required>
            </div>            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" class="form-control" name="password" placeholder="Isi jika ingin mengganti password">
                <small class="form-text text-muted">Kosongkan jika tidak ingin mengubah password.</small>
            </div>
            <div class="form-group">
                <label for="password_confirmation">Konfirmasi Password</label>
                <input type="password" class="form-control" name="password_confirmation" placeholder="Konfirmasi password baru">
            </div>            
            <button type="submit" class="btn btn-primary">Update</button>
        </form>
    </div>

    <script>
        document.getElementById('role').addEventListener('change', function() {
            var daerahGroup = document.getElementById('daerah-group');
            if (this.value == 'sales') {
                daerahGroup.style.display = 'block';
            } else {
                daerahGroup.style.display = 'none';
            }
        });

        if (document.getElementById('role').value == 'sales') {
            document.getElementById('daerah-group').style.display = 'block';
        }
    </script>
@endsection
