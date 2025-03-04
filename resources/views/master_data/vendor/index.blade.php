@extends('layouts.app')

@section('content')
@if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

{{-- Form filter untuk admin --}}
@if(Auth::user()->role == 'admin')
        <!-- Form Search dan Filter Berdasarkan Kota untuk Admin -->
        <form action="{{ route('vendor.index') }}" method="GET" class="mb-3">
            <div class="row">
                <div class="col-md-4">
                    <label for="search">Cari Vendor:</label>
                    <input type="text" name="search" id="search" class="form-control" placeholder="Cari vendor" value="{{ request('search') }}">
                </div>
                <div class="col-md-4">
                    <label for="kota">Filter Berdasarkan Kota:</label>
                    <select name="kota" id="kota" class="form-select">
                        <option value="">-- Semua Kota --</option>
                        @foreach($kotaList as $kota)
                            <option value="{{ $kota }}" {{ request('kota') == $kota ? 'selected' : '' }}>{{ $kota }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary">Cari</button>
                </div>
            </div>
        </form>
    @else
        <!-- Untuk Sales, hanya menampilkan fitur search -->
        <form action="{{ route('vendor.index') }}" method="GET" class="mb-3">
            <div class="row">
                <div class="col-md-8">
                    <label for="search">Cari Vendor:</label>
                    <input type="text" name="search" id="search" class="form-control" placeholder="Cari Vendor" value="{{ request('search') }}">
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary">Cari</button>
                </div>
            </div>
        </form>
    @endif

{{-- Form filter untuk sales: hanya menampilkan filter status --}}
{{-- @if(Auth::user()->role == 'sales') --}}
<form action="{{ route('vendor.index') }}" method="GET" class="mb-3">
    <div class="row">
        <div class="col-md-6 mb-2">
            <label for="status">Filter Berdasarkan Status Vendor:</label>
            <select name="status" id="status" class="form-select" onchange="this.form.submit()">
                <option value="">Semua Status</option>
                <option value="aktif" {{ request('status') == 'aktif' ? 'selected' : '' }}>Aktif</option>
                <option value="nonaktif" {{ request('status') == 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                <option value="menunggu_verifikasi" {{ request('status') == 'menunggu_verifikasi' ? 'selected' : '' }}>Menunggu Verifikasi</option>
            </select>
        </div>
    </div>
</form>
{{-- @endif --}}

<div class="container">
    <h2>Daftar Vendor</h2>
    <a href="{{ route('vendor.create') }}" class="btn btn-primary mb-3">Tambah Vendor</a>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Kode Vendor</th>
                <th>Nama</th>
                <th>Jam Operasional</th>
                <th>Nomor HP</th>
                <th>Status</th>
                <th>Kota</th>
                <th>Wilayah</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody id="vendor-table-body">
            @foreach($vendors as $vendor)
                {{-- Untuk sales: jika filter status sudah diisi dan bukan "nonaktif", lewati vendor nonaktif --}}
                @if(Auth::user()->role == 'sales' && request()->filled('status') && request('status') != 'nonaktif' && $vendor->status == 'nonaktif')
                    @continue
                @endif
                <tr>
                    <td>{{ $vendor->kode_vendor }}</td>
                    <td>{{ $vendor->nama }}</td>
                    <td>{{ $vendor->jam_operasional }}</td>
                    <td>{{ $vendor->nomor_hp }}</td>
                    <td>{{ $vendor->status_label }}</td>
                    <td>{{ $vendor->wilayah ? $vendor->wilayah->kota : 'N/A' }}</td>
                    <td>{{ $vendor->wilayah ? $vendor->wilayah->nama : 'N/A' }}</td>
                    <td>
                        <a href="{{ route('vendor.show', $vendor->id) }}" class="btn btn-info btn-sm">Lihat Detail</a>
                        <a href="{{ route('vendor.edit', $vendor->id) }}" class="btn btn-warning btn-sm">Edit</a>
                        @if(Auth::user()->role == 'admin' && $vendor->status == 'aktif')
                        <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#nonaktifVendorModal" onclick="setVendorId({{ $vendor->id }})">
                            Nonaktifkan Vendor
                        </button>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    
    {{-- Tampilkan pagination --}}
    <div class="d-flex justify-content-center">
            {{ $vendors->links() }}
    </div>
</div>

{{-- Modal untuk input alasan nonaktif --}}
<div class="modal fade" id="nonaktifVendorModal" tabindex="-1" aria-labelledby="nonaktifVendorModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="nonaktifVendorModalLabel">Nonaktifkan Vendor</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="nonaktifVendorForm">
                    @csrf
                    <input type="hidden" id="vendor_id">
                    <div class="mb-3">
                        <label for="reason" class="form-label">Alasan Penonaktifan</label>
                        <textarea class="form-control" id="reason" rows="3" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-danger">Nonaktifkan Vendor</button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- JavaScript untuk modal nonaktif vendor --}}
<script>
    function setVendorId(id) {
        document.getElementById("vendor_id").value = id;
        document.getElementById("reason").value = "";
    }

    document.getElementById("nonaktifVendorForm").addEventListener("submit", function(e){
        e.preventDefault();
        let vendorId = document.getElementById("vendor_id").value;
        let reason = document.getElementById("reason").value;
        if (!reason) {
            alert("Alasan penonaktifan harus diisi!");
            return;
        }
        var userRole = "{{ Auth::user()->role }}";
        let endpoint = (userRole === 'admin')
            ? `/vendor/${vendorId}/deactivate`
            : `/vendor/${vendorId}/request-deactivation`;
        fetch(endpoint, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            body: JSON.stringify({ reason: reason })
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(err => {
                    console.error("Error response:", err);
                    throw new Error(err.message || "Network response was not ok");
                });
            }
            return response.json();
        })
        .then(data => {
            alert(data.message);
            if (data.message.toLowerCase().includes("berhasil")) {
                let modalEl = document.getElementById('nonaktifVendorModal');
                let modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
                modal.hide();
                document.getElementById("nonaktifVendorForm").reset();
                location.reload();
            }
        })
        .catch(error => {
            console.error("Error:", error);
            alert("Terjadi kesalahan saat mengirim permintaan!");
        });
    });
</script>

{{-- JavaScript untuk live search (hanya aktif untuk admin) --}}
@if(Auth::user()->role == 'admin')
<script>
    let searchInput = document.getElementById('search');
    let timer;
    searchInput.addEventListener('keyup', function() {
        clearTimeout(timer);
        timer = setTimeout(function() {
            let query = searchInput.value;
            fetch(`{{ route('vendor.search') }}?search=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(data => {
                    let tableBody = document.getElementById('vendor-table-body');
                    tableBody.innerHTML = '';
                    data.vendors.forEach(vendor => {
                        let row = `<tr>
                            <td>${vendor.kode_vendor}</td>
                            <td>${vendor.nama}</td>
                            <td>${vendor.jam_operasional}</td>
                            <td>${vendor.nomor_hp}</td>
                            <td>${vendor.status}</td>
                            <td>${vendor.wilayah ? vendor.wilayah.kota : 'N/A'}</td>
                            <td>${vendor.wilayah ? vendor.wilayah.nama : 'N/A'}</td>
                            <td>
                                <a href="/vendor/${vendor.id}" class="btn btn-info btn-sm">Lihat Detail</a>
                                <a href="/vendor/${vendor.id}/edit" class="btn btn-warning btn-sm">Edit</a>
                            </td>
                        </tr>`;
                        tableBody.innerHTML += row;
                    });
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        }, 300); // Delay 300ms
    });
</script>
@endif
@endsection
