@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Log Aktivitas</h2>

    <!-- Form filter tanggal -->
    <form action="{{ route('log_activity.index') }}" method="GET" class="mb-3">
        <div class="row">
            <div class="col-md-4">
                <label for="date_filter">Filter Tanggal:</label>
                <input type="date" name="date_filter" id="date_filter" class="form-control" value="{{ request('date_filter') }}">
            </div>
            <div class="col-md-2 align-self-end">
                <button type="submit" class="btn btn-primary">Filter</button>
            </div>
        </div>
    </form>

    <!-- Tabel Log Aktivitas -->
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>No</th>
                <th>User</th>
                <th>Tabel</th>
                <th>Aksi</th>
                <th>Data Lama</th>
                <th>Data Baru</th>
                <th>Waktu</th>
                <th>IP Address</th>
            </tr>
        </thead>
        <tbody>
            @foreach($logs as $index => $log)
                @php
                    // Pastikan data lama dan data baru dalam bentuk array
                    $oldData = is_array($log->old_data) ? $log->old_data : [];
                    $newData = is_array($log->new_data) ? $log->new_data : [];
                    $diffOld = [];
                    $diffNew = [];
                    // Ambil semua kunci dari kedua array
                    $allKeys = array_unique(array_merge(array_keys($oldData), array_keys($newData)));
                    foreach ($allKeys as $key) {
                        $oldVal = $oldData[$key] ?? null;
                        $newVal = $newData[$key] ?? null;
                        // Jika terjadi perbedaan, simpan nilai lama dan baru
                        if ($oldVal != $newVal) {
                            $diffOld[$key] = $oldVal;
                            $diffNew[$key] = $newVal;
                        }
                    }
                @endphp
                <tr>
                    <td>{{ $logs->firstItem() + $index }}</td>
                    <td>{{ $log->user->nama ?? 'System' }}</td>
                    <td>{{ $log->table_name }}</td>
                    <td>{{ $log->action }}</td>
                    <td>
                        @if(!empty($diffOld))
                            <pre>{{ json_encode($diffOld, JSON_PRETTY_PRINT) }}</pre>
                        @else
                            -
                        @endif
                    </td>
                    <td>
                        @if(!empty($diffNew))
                            <pre>{{ json_encode($diffNew, JSON_PRETTY_PRINT) }}</pre>
                        @else
                            -
                        @endif
                    </td>
                    <td>{{ $log->created_at }}</td>
                    <td>{{ $log->ip_address }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Pagination dengan mempertahankan filter -->
    {{ $logs->appends(request()->input())->links() }}
</div>

@push('styles')
<style>
    .page-link svg {
        width: 1em !important;
        height: 1em !important;
    }
</style>
@endpush

@endsection
