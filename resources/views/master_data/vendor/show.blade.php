@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Detail Vendor</h2>
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">{{ $vendor->nama }}</h5>
            <p><strong>Keterangan:</strong> {{ $vendor->keterangan }}</p>
            <p><strong>Jam Operasional:</strong> {{ $vendor->jam_operasional }}</p>
            <p><strong>Nomor HP:</strong> {{ $vendor->nomor_hp }}</p>
            <p>
                <strong>Location Link:</strong> 
                <a href="{{ $vendor->location_link }}" target="_blank">
                    {{ $vendor->location_link }}
                </a>
            </p>
            <p><strong>Daerah (Kota):</strong> {{ $vendor->wilayah ? $vendor->wilayah->kota : 'N/A' }}</p>
            <p><strong>Wilayah:</strong> {{ $vendor->wilayah ? $vendor->wilayah->nama : 'N/A' }}</p>
            <p><strong>Status:</strong> {{ $vendor->status_label }}</p>

            @if($vendor->status == 'nonaktif')
                @php
                    // Urutkan berdasarkan approved_at secara descending dan ambil entri pertama
                    $latestRequest = $vendor->deactivationRequests->sortByDesc('approved_at')->first();
                @endphp
                <p><strong>Alasan Nonaktif:</strong> {{ $latestRequest ? $latestRequest->reason : 'Tidak ada alasan' }}</p>
            @endif

            @if ($vendor->gambar_vendor)
                <p><strong>Gambar Vendor:</strong></p>
                <img src="{{ Storage::url($vendor->gambar_vendor) }}" alt="Gambar Vendor" style="width: 200px; height: auto;">
                <!-- Tombol untuk melihat detail gambar -->
                <p>
                    <a href="{{ Storage::url($vendor->gambar_vendor) }}" target="_blank" class="btn btn-info">
                        Lihat Detail Gambar
                    </a>
                </p>
            @endif

            <a href="{{ route('vendor.edit', $vendor->id) }}" class="btn btn-primary">Edit Vendor</a>
            <a href="{{ route('vendor.index') }}" class="btn btn-secondary">Kembali</a>
            {{-- Jika vendor aktif dan user admin, tampilkan tombol nonaktifkan --}}
            @if($vendor->status == 'aktif')
                <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#nonaktifVendorModal" onclick="setVendorId({{ $vendor->id }})">
                    Nonaktifkan Vendor
                </button>
            @endif
            {{-- Jika vendor nonaktif dan user admin, tampilkan tombol aktifkan --}}
            @if($vendor->status == 'nonaktif' && Auth::user()->role == 'admin')
                <button class="btn btn-success" onclick="activateVendor({{ $vendor->id }})">
                    Aktifkan Vendor
                </button>
            @endif
        </div>
    </div>
</div>

{{-- Modal untuk input alasan nonaktif --}}
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

<script>
document.addEventListener("DOMContentLoaded", function() {
    // Ambil role user dengan aman
    const userRole = @json(Auth::user()->role);
    
    // Fungsi global untuk set vendor_id pada modal
    window.setVendorId = function(id) {
        document.getElementById("vendor_id").value = id;
        document.getElementById("reason").value = "";
    };

    // Jika user adalah admin, sesuaikan tampilan modal
    if (userRole === "admin") {
        const modalLabel = document.getElementById("nonaktifVendorModalLabel");
        if (modalLabel) {
            modalLabel.innerText = "Nonaktifkan Vendor";
        }
        const submitBtn = document.querySelector("#nonaktifVendorForm button[type=submit]");
        if (submitBtn) {
            submitBtn.innerText = "Nonaktifkan Vendor";
        }
    }

    // Proses submit form nonaktif vendor
    const form = document.getElementById("nonaktifVendorForm");
    form.addEventListener("submit", async function(e) {
        e.preventDefault();
    
        const vendorId = document.getElementById("vendor_id").value;
        const reason = document.getElementById("reason").value.trim();
    
        if (!reason) {
            alert("Alasan penonaktifan harus diisi!");
            return;
        }
    
        // Tentukan endpoint berdasarkan role user
        const endpoint = userRole === "admin"
            ? `/vendor/${vendorId}/deactivate`
            : `/vendor/${vendorId}/request-deactivation`;
    
        try {
            const response = await fetch(endpoint, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({ reason })
            });
    
            let data;
            try {
                data = await response.json();
            } catch (jsonError) {
                console.error("JSON parsing error:", jsonError);
                throw new Error("Server tidak mengembalikan format JSON yang valid");
            }
    
            if (!response.ok) {
                console.error("Error response data:", data);
                throw new Error(data.message || "Terjadi kesalahan pada server");
            }
    
            alert(data.message);
            if (data.message.toLowerCase().includes("berhasil")) {
                const modalEl = document.getElementById("nonaktifVendorModal");
                const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
                modal.hide();
                form.reset();
                location.reload();
            }
        } catch (error) {
            console.error("Fetch error:", error);
            alert("Terjadi kesalahan saat mengirim permintaan: " + error.message);
        }
    });
    
    // Fungsi untuk mengaktifkan vendor
    window.activateVendor = async function(id) {
        if (!confirm("Apakah Anda yakin ingin mengaktifkan vendor ini?")) {
            return;
        }
        try {
            const response = await fetch(`/vendor/${id}/activate`, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({}) // Tidak perlu data tambahan
            });
    
            let data;
            try {
                data = await response.json();
            } catch (jsonError) {
                console.error("JSON parsing error:", jsonError);
                throw new Error("Server tidak mengembalikan format JSON yang valid");
            }
    
            if (!response.ok) {
                console.error("Error response data:", data);
                throw new Error(data.message || "Terjadi kesalahan pada server");
            }
    
            alert(data.message);
            location.reload();
        } catch (error) {
            console.error("Fetch error:", error);
            alert("Terjadi kesalahan saat mengaktifkan vendor: " + error.message);
        }
    };
});
</script>
@endsection
