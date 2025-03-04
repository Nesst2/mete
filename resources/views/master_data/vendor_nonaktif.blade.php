@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Daftar Vendor</h2>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>#</th>
                <th>Nama Vendor</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($vendors as $vendor)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $vendor->nama }}</td>
                <td>
                    @if($vendor->status == 'aktif')
                        <span class="badge bg-success">Aktif</span>
                    @elseif($vendor->status == 'nonaktif')
                        <span class="badge bg-secondary">Nonaktif</span>
                    @elseif($vendor->status == 'diblokir')
                        <span class="badge bg-danger">Diblokir</span>
                    @elseif($vendor->status == 'menunggu_verifikasi')
                        <span class="badge bg-warning">Menunggu Verifikasi</span>
                    @endif
                </td>
                <td>
                    @if(auth()->user()->role == 'sales' && $vendor->status == 'aktif')
                        <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#nonaktifVendorModal" onclick="setVendorId({{ $vendor->id }})">
                            Nonaktifkan Vendor
                        </button>
                    @else
                        <button class="btn btn-secondary" disabled>Nonaktifkan</button>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    @if(auth()->user()->role == 'admin')
    {{-- Tampilkan daftar request penonaktifan yang masih pending --}}
    <h3>Request Penonaktifan</h3>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>#</th>
                <th>Nama Vendor</th>
                <th>Sales</th>
                <th>Alasan</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($requests as $request)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $request->vendor->nama }}</td>
                <td>{{ $request->sales->name }}</td>
                <td>{{ $request->reason }}</td>
                <td>
                    @if($request->status == 'pending')
                        <span class="badge bg-warning">Pending</span>
                    @elseif($request->status == 'approved')
                        <span class="badge bg-success">Disetujui</span>
                    @elseif($request->status == 'rejected')
                        <span class="badge bg-danger">Ditolak</span>
                    @elseif($request->status == 'canceled')
                        <span class="badge bg-secondary">Dibatalkan</span>
                    @endif
                </td>
                <td>
                    @if($request->status == 'pending')
                        <button onclick="approveRequest({{ $request->id }})" class="btn btn-success">Setujui</button>
                        <button onclick="rejectRequest({{ $request->id }})" class="btn btn-danger">Tolak</button>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
</div>

{{-- Modal untuk input alasan --}}
<div class="modal fade" id="nonaktifVendorModal" tabindex="-1" aria-labelledby="nonaktifVendorModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="nonaktifVendorModalLabel">Request Nonaktifkan Vendor</h5>
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
                    <button type="submit" class="btn btn-danger">Kirim Permintaan</button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- JavaScript --}}
<script>
    function setVendorId(id) {
        document.getElementById("vendor_id").value = id;
    }

    document.getElementById("nonaktifVendorForm").addEventListener("submit", function (e) {
        e.preventDefault();

        let vendorId = document.getElementById("vendor_id").value;
        let reason = document.getElementById("reason").value;

        fetch(`/vendor/${vendorId}/request-deactivation`, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            body: JSON.stringify({ reason })
        })
        .then(response => response.json())
        .then(data => {
            alert(data.message);
            location.reload();
        })
        .catch(error => console.error("Error:", error));
    });

    function approveRequest(id) {
        fetch(`/deactivation-request/${id}/approve`, {
            method: "POST",
            headers: { "X-CSRF-TOKEN": "{{ csrf_token() }}" }
        }).then(response => response.json())
          .then(data => { alert(data.message); location.reload(); });
    }

    function rejectRequest(id) {
        fetch(`/deactivation-request/${id}/reject`, {
            method: "POST",
            headers: { "X-CSRF-TOKEN": "{{ csrf_token() }}" }
        }).then(response => response.json())
          .then(data => { alert(data.message); location.reload(); });
    }
</script>

@endsection
