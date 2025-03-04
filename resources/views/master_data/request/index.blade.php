@extends('layouts.app')

@section('title', 'Request Penonaktifan Vendor')

@section('content')
<div class="container">
    <h2>Request Penonaktifan Vendor</h2>

    @if(Auth::user()->role == 'admin')
    <!-- Form Filter untuk Admin: Kota & Tanggal -->
    <form action="{{ route('request.index') }}" method="GET" class="mb-3">
        <div class="card p-3 mb-3">
            <h4>Filter Request</h4>
            <div class="row">
                <!-- Filter Berdasarkan Kota -->
                <div class="col-md-4 mb-2">
                    <label for="kota">Filter Berdasarkan Kota:</label>
                    <select name="kota" id="kota" class="form-select">
                        <option value="">-- Semua Kota --</option>
                        @foreach($kotaList as $kota)
                            <option value="{{ $kota }}" {{ request('kota') == $kota ? 'selected' : '' }}>
                                {{ $kota }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <!-- Filter Berdasarkan Tanggal Request: Dari Tanggal -->
                <div class="col-md-4 mb-2">
                    <label for="start_date">Dari Tanggal:</label>
                    <input type="date" id="start_date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                </div>
                <!-- Filter Berdasarkan Tanggal Request: Sampai Tanggal -->
                <div class="col-md-4 mb-2">
                    <label for="end_date">Sampai Tanggal:</label>
                    <input type="date" id="end_date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                </div>
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Filter</button>
    </form>
    @endif

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Vendor</th>
                <th>Sales</th>
                <th>Alasan</th>
                <th>Kota</th>
                <th>Status</th>
                @if(Auth::user()->role == 'admin')
                    <th>Aksi</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach ($requests as $request)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $request->vendor ? $request->vendor->nama : 'N/A' }}</td>
                <td>{{ $request->sales ? $request->sales->nama : 'N/A' }}</td>
                <td>{{ $request->reason }}</td>
                <td>{{ ($request->vendor && $request->vendor->wilayah) ? $request->vendor->wilayah->kota : 'N/A' }}</td>
                <td>
                    <span class="badge bg-{{ $request->status == 'pending' ? 'warning' : ($request->status == 'approved' ? 'success' : 'danger') }}">
                        {{ ucfirst($request->status) }}
                    </span>
                </td>
                @if(Auth::user()->role == 'admin')
                    <td>
                        @if($request->status == 'pending')
                            <button onclick="approveRequest({{ $request->id }})" class="btn btn-success">Setujui</button>
                            <button onclick="rejectRequest({{ $request->id }})" class="btn btn-danger">Tolak</button>
                        @else
                            <button class="btn btn-secondary" disabled>Selesai</button>
                        @endif
                    </td>
                @endif
            </tr>
            @endforeach
        </tbody>
    </table>
</div>


<script>
    function approveRequest(id) {
        fetch(`/request/${id}/approve`, { 
            method: "POST", 
            headers: { 
                "X-CSRF-TOKEN": "{{ csrf_token() }}" 
            }
        })
        .then(response => response.json())
        .then(data => { 
            alert(data.message); 
            location.reload();
        })
        .catch(error => {
            alert("Terjadi kesalahan");
        });
    }

    function rejectRequest(id) {
        fetch(`/request/${id}/reject`, { 
            method: "POST", 
            headers: { 
                "X-CSRF-TOKEN": "{{ csrf_token() }}" 
            }
        })
        .then(response => response.json())
        .then(data => { 
            alert(data.message); 
            location.reload();
        })
        .catch(error => {
            alert("Terjadi kesalahan");
        });
    }
</script>
@endsection
