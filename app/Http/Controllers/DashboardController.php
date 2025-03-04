<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Vendor;
use App\Models\Tagihan;
use App\Models\Wilayah;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $currentYear  = Carbon::now()->year;
        $currentMonth = Carbon::now()->month;
        $bulan = Carbon::now()->translatedFormat('F Y'); // Menambahkan Nama Bulan
        
        $userRole = Auth::user()->role;
        
        // --- Grafik Kunjungan Vendor per Kota ---
        $query = Vendor::with(['wilayah.daerah', 'tagihan']);
        
        // Untuk sales, filter vendor berdasarkan kota sales
        if ($userRole === 'sales') {
            $salesKota = optional(Auth::user()->daerah)->kota;
            if ($salesKota) {
                $query->whereHas('wilayah.daerah', function($q) use ($salesKota) {
                    $q->where('kota', $salesKota);
                });
            } else {
                $query->whereRaw('1=0'); // Hasilkan koleksi kosong jika tidak ada kota
            }
        }
        $vendors = $query->get();
        
        // Kelompokkan vendor berdasarkan kota
        $grouped = $vendors->groupBy(function($vendor) {
            return ($vendor->wilayah && $vendor->wilayah->daerah)
                ? $vendor->wilayah->daerah->kota
                : 'N/A';
        });
        
        $cityLabels = [];
        $checkedCounts = [];
        $notCheckedCounts = [];
        
        foreach ($grouped as $city => $vendorsGroup) {
            $checked = 0;
            $notChecked = 0;
            foreach ($vendorsGroup as $vendor) {
                // Vendor dianggap terchecklist jika memiliki data tagihan pada bulan berjalan
                if ($vendor->tagihan()
                        ->whereYear('tanggal_masuk', $currentYear)
                        ->whereMonth('tanggal_masuk', $currentMonth)
                        ->exists()) {
                    $checked++;
                } else {
                    $notChecked++;
                }
            }
            $cityLabels[] = $city;
            $checkedCounts[] = $checked;
            $notCheckedCounts[] = $notChecked;
        }
        
        // --- Grafik Pemasukan per Hari pada Bulan Ini ---
        // Mulai query tagihan untuk bulan berjalan
        $tagihanQuery = Tagihan::whereYear('tanggal_masuk', $currentYear)
            ->whereMonth('tanggal_masuk', $currentMonth);
        
        // Jika user adalah admin dan ingin memfilter berdasarkan kota untuk grafik income
        if ($userRole === 'admin' && $request->filled('income_kota')) {
            $selectedIncomeKota = $request->input('income_kota');
            $tagihanQuery->whereHas('vendor.wilayah.daerah', function($q) use ($selectedIncomeKota) {
                $q->where('kota', $selectedIncomeKota);
            });
        }
        // Jika user adalah sales, filter otomatis berdasarkan kota sales
        elseif ($userRole === 'sales') {
            $salesKota = optional(Auth::user()->daerah)->kota;
            if ($salesKota) {
                $tagihanQuery->whereHas('vendor.wilayah.daerah', function($q) use ($salesKota) {
                    $q->where('kota', $salesKota);
                });
            }
        }
        
        $tagihanMonth = $tagihanQuery->get();
        $groupedByDay = $tagihanMonth->groupBy(function($item) {
            return Carbon::parse($item->tanggal_masuk)->format('d');
        });
        
        $daysInMonth = Carbon::now()->daysInMonth;
        $dayLabels = [];
        $dailyIncome = [];
        for ($i = 1; $i <= $daysInMonth; $i++) {
            $dayStr = str_pad($i, 2, '0', STR_PAD_LEFT);
            $dayLabels[] = $dayStr;
            $sum = isset($groupedByDay[$dayStr]) ? $groupedByDay[$dayStr]->sum('uang_masuk') : 0;
            $dailyIncome[] = $sum;
        }
        
        // Ambil daftar semua kota untuk filter grafik income (untuk admin)
        $allCities = Vendor::with('wilayah.daerah')
            ->get()
            ->pluck('wilayah.daerah.kota')
            ->unique()
            ->filter()
            ->values();
        
        // Tentukan view berdasarkan role
        $view = ($userRole === 'admin') ? 'dashboard.admin' : 'dashboard.sales';
        
        return view($view, compact('cityLabels', 'checkedCounts', 'notCheckedCounts', 'dayLabels', 'dailyIncome', 'allCities','bulan'));
    }
}
