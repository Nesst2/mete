@extends('layouts.app')

@section('content')
@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="container">
    <!-- Card Filter Vendor -->
    <div class="card mb-4">
        <div class="card-header">
            <h5>Filter Vendor</h5>
        </div>
        <div class="card-body">
            @if(Auth::user()->role == 'admin')
                <!-- Form Filter untuk Admin: Search, Kota, Wilayah & Status -->
                <form action="{{ route('vendor.index') }}" method="GET" id="adminFilterForm">
                    <div class="row mb-3">
                        <!-- Search Input dengan Button (Input Group) -->
                        <div class="col-md-3">
                            <label for="search" class="form-label">Cari Vendor:</label>
                            <div class="input-group">
                                <input type="text" name="search" id="search" class="form-control" placeholder="Cari vendor" value="{{ request('search') }}">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa fa-search"></i>
                                </button>
                            </div>
                        </div>
                        <!-- Filter Berdasarkan Kota -->
                        <div class="col-md-3">
                            <label for="kota" class="form-label">Filter Berdasarkan Kota:</label>
                            <select name="kota" id="kota" class="form-select" onchange="this.form.submit()">
                                <option value="">-- Semua Kota --</option>
                                @foreach($kotaList as $kota)
                                    <option value="{{ $kota }}" {{ request('kota') == $kota ? 'selected' : '' }}>{{ $kota }}</option>
                                @endforeach
                            </select>
                        </div>
                        <!-- Filter Berdasarkan Wilayah -->
                        <div class="col-md-3">
                            <label for="wilayah" class="form-label">Filter Berdasarkan Wilayah:</label>
                            <select name="wilayah" id="wilayah" class="form-select" onchange="this.form.submit()">
                                <option value="">-- Semua Wilayah --</option>
                                @foreach($wilayahList as $wil)
                                    <option value="{{ $wil }}" {{ request('wilayah') == $wil ? 'selected' : '' }}>{{ $wil }}</option>
                                @endforeach
                            </select>
                        </div>
                        <!-- Filter Berdasarkan Status Vendor -->
                        <div class="col-md-3">
                            <label for="status" class="form-label">Filter Berdasarkan Status Vendor:</label>
                            <select name="status" id="status" class="form-select" onchange="this.form.submit()">
                                <option value="">Semua Status</option>
                                <option value="aktif" {{ request('status') == 'aktif' ? 'selected' : '' }}>Aktif</option>
                                <option value="nonaktif" {{ request('status') == 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                                <option value="menunggu_verifikasi" {{ request('status') == 'menunggu_verifikasi' ? 'selected' : '' }}>Menunggu Verifikasi</option>
                            </select>
                        </div>
                    </div>
                    <!-- Baris Tombol Filter & Clear Filter -->
                    <div class="row">
                        <div class="col-md-3 mb-2 align-self-end">
                            <button type="submit" class="btn btn-primary">Filter</button>
                            <a href="{{ route('vendor.index') }}" class="btn btn-secondary">Clear Filter</a>
                        </div>
                    </div>
                </form>
            @else
                <!-- Form Filter untuk Sales: Search, Wilayah & Status (tanpa filter kota) -->
                <form action="{{ route('vendor.index') }}" method="GET" id="salesFilterForm">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="search" class="form-label">Cari Vendor:</label>
                            <div class="input-group">
                                <input type="text" name="search" id="search" class="form-control" placeholder="Cari vendor" value="{{ request('search') }}">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa fa-search"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label for="wilayah" class="form-label">Filter Berdasarkan Wilayah:</label>
                            <select name="wilayah" id="wilayah" class="form-select" onchange="this.form.submit()">
                                <option value="">-- Semua Wilayah --</option>
                                @foreach($wilayahList as $wil)
                                    <option value="{{ $wil }}" {{ request('wilayah') == $wil ? 'selected' : '' }}>{{ $wil }}</option>
                                @endforeach
                            </select>
                        </div>
                        <!-- Filter Berdasarkan Status Vendor -->
                        <div class="col-md-4">
                            <label for="status" class="form-label">Filter Berdasarkan Status Vendor:</label>
                            <select name="status" id="status" class="form-select" onchange="this.form.submit()">
                                <option value="">Semua Status</option>
                                <option value="aktif" {{ request('status') == 'aktif' ? 'selected' : '' }}>Aktif</option>
                                <option value="nonaktif" {{ request('status') == 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                                <option value="menunggu_verifikasi" {{ request('status') == 'menunggu_verifikasi' ? 'selected' : '' }}>Menunggu Verifikasi</option>
                            </select>
                        </div>
                    </div>
                    <!-- Baris Tombol Filter & Clear Filter -->
                    <div class="row">
                        <div class="col-md-3 mb-2 align-self-end">
                            <button type="submit" class="btn btn-primary">Filter</button>
                            <a href="{{ route('vendor.index') }}" class="btn btn-secondary">Clear Filter</a>
                        </div>
                    </div>
                </form>
            @endif
        </div>
    </div>

    <!-- Daftar Vendor -->
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
                <tr class="{{ $vendor->status == 'nonaktif' ? 'table-danger' : '' }}">
                    <td>{{ $vendor->kode_vendor }}</td>
                    <td>{{ $vendor->nama }}</td>
                    <td>{{ $vendor->jam_operasional }}</td>
                    <td>{{ $vendor->nomor_hp }}</td>
                    <td>{{ $vendor->status_label }}</td>
                    <td>{{ $vendor->wilayah ? $vendor->wilayah->daerah->kota : 'N/A' }}</td>
                    <td>{{ $vendor->wilayah ? $vendor->wilayah->nama : 'N/A' }}</td>
                    <td>
                        <a href="{{ route('vendor.show', $vendor->id) }}" class="btn btn-info btn-sm">Lihat Detail</a>
                        <a href="{{ route('vendor.edit', $vendor->id) }}" class="btn btn-secondary btn-sm">Edit</a>
                        @if(Auth::user()->role == 'admin')
                            @if($vendor->status == 'aktif')
                                <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#nonaktifVendorModal" onclick="setVendorId({{ $vendor->id }})">
                                    Nonaktifkan Vendor
                                </button>
                            @elseif($vendor->status == 'nonaktif')
                                <button type="button" class="btn btn-danger btn-sm" onclick="deleteNonActiveVendor({{ $vendor->id }})">
                                    Hapus Vendor
                                </button>
                            @endif
                        @endif
                    </td>                                      
                </tr>
            @endforeach
        </tbody>        
    </table>

    <div class="d-flex justify-content-center">{{ $vendors->links() }}</div>
</div>

<!-- Modal Nonaktif Vendor -->
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
                        <div id="reasonError" class="text-danger mt-1" style="display: none;">Alasan penonaktifan harus diisi!</div>
                    </div>                    
                    <button type="submit" class="btn btn-danger">Nonaktifkan Vendor</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript untuk Modal Nonaktif Vendor -->
<script>
    function setVendorId(id) {
        document.getElementById("vendor_id").value = id;
        document.getElementById("reason").value = "";
    }

        document.getElementById("nonaktifVendorForm").addEventListener("submit", function(e) {
        e.preventDefault();
        
        let reasonInput = document.getElementById("reason");
        let reasonError = document.getElementById("reasonError");
        let reason = reasonInput.value.trim();

        if (!reason) {
            reasonError.style.display = "block"
            reasonInput.classList.add("is-invalid");
            return;
        } else {
            reasonError.style.display = "none";
            reasonInput.classList.remove("is-invalid");
        }

        let vendorId = document.getElementById("vendor_id").value;
        let userRole = "{{ Auth::user()->role }}";
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
        .then(response => response.json())
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

<script>
    function deleteNonActiveVendor(id) {
        if (!confirm("Apakah Anda yakin ingin menghapus vendor nonaktif ini?")) {
            return;
        }

        fetch(`/vendor/${id}/delete-nonactive`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            alert(data.message);
            if (data.message.toLowerCase().includes("berhasil")) {
                location.reload();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert("Terjadi kesalahan saat menghapus vendor!");
        });
    }
</script>

<!-- JavaScript untuk Live Search (khusus Admin) -->
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
                            <td>${vendor.wilayah ? vendor.wilayah.daerah.kota : 'N/A'}</td>
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
        }, 300);
    });
</script>
@endif
@endsection
