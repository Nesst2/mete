@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Dashboard Admin</h2>

    <!-- Grafik Kunjungan Vendor per Kota -->
    <div class="row mb-4">
        <div class="col-md-12">
            <h4>Grafik Kunjungan Vendor per Kota - Bulan {{ $bulan }}</h4>
            <canvas id="visitsChart" width="400" height="200"></canvas>
        </div>
    </div>

    <!-- Filter untuk Grafik Pemasukan per Hari Berdasarkan Kota -->
    <div class="row mb-4">
        <div class="col-md-4">
            <form action="{{ route('admin.dashboard') }}" method="GET">
                <div class="mb-3">
                    <label for="income_kota" class="form-label">Filter Pemasukan Berdasarkan Kota:</label>
                    <select name="income_kota" id="income_kota" class="form-select" onchange="this.form.submit()">
                        <option value="">-- Semua Kota --</option>
                        @foreach($allCities as $city)
                            <option value="{{ $city }}" {{ request('income_kota') == $city ? 'selected' : '' }}>{{ $city }}</option>
                        @endforeach
                    </select>
                </div>
            </form>
        </div>
    </div>

    <!-- Grafik Pemasukan per Hari pada Bulan Ini -->
    <div class="row">
        <div class="col-md-12">
            <h4>Grafik Pemasukan per Hari - Bulan {{ $bulan }}</h4>
            <canvas id="incomeChart" width="400" height="200"></canvas>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <!-- Sertakan Chart.js via CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Grafik Kunjungan Vendor per Kota (Stacked Bar)
        const ctx1 = document.getElementById('visitsChart').getContext('2d');
        const visitsChart = new Chart(ctx1, {
            type: 'bar',
            data: {
                labels: {!! json_encode($cityLabels) !!},
                datasets: [
                    {
                        label: 'Vendor Terchecklist',
                        data: {!! json_encode($checkedCounts) !!},
                        backgroundColor: 'rgba(75, 192, 192, 0.5)',
                        borderColor: 'rgba(75, 192, 192, 1)',
                        borderWidth: 1,
                        stack: 'Stack 0'
                    },
                    {
                        label: 'Vendor Belum Terchecklist',
                        data: {!! json_encode($notCheckedCounts) !!},
                        backgroundColor: 'rgba(255, 99, 132, 0.5)',
                        borderColor: 'rgba(255, 99, 132, 1)',
                        borderWidth: 1,
                        stack: 'Stack 0'
                    }
                ]
            },
            options: {
                plugins: {
                    title: {
                        display: true,
                        text: 'Grafik Kunjungan Vendor per Kota - Bulan {{ $bulan }}'
                    }
                },
                scales: {
                    x: {
                        stacked: true,
                        title: {
                            display: true,
                            text: 'Kota'
                        }
                    },
                    y: {
                        stacked: true,
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Jumlah Vendor'
                        }
                    }
                }
            }
        });

        // Grafik Pemasukan per Hari pada Bulan Ini (Line Chart)
        const ctx2 = document.getElementById('incomeChart').getContext('2d');
        const incomeChart = new Chart(ctx2, {
            type: 'line',
            data: {
                labels: {!! json_encode($dayLabels) !!},
                datasets: [{
                    label: 'Pemasukan (Rp)',
                    data: {!! json_encode($dailyIncome) !!},
                    borderColor: 'rgba(54, 162, 235, 1)',
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    tension: 0.3,
                    fill: true,
                    pointRadius: 3
                }]
            },
            options: {
                plugins: {
                    title: {
                        display: true,
                        text: 'Grafik Pemasukan per Hari - Bulan {{ $bulan }}'
                    },
                    tooltip: {
                        callbacks: {
                            title: function(tooltipItems) {
                                return 'Tanggal ' + tooltipItems[0].label + ' - Bulan {{ $bulan }}';
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: 'Hari'
                        }
                    },
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Pemasukan (Rp)'
                        }
                    }
                }
            }
        });
    </script>
@endpush
