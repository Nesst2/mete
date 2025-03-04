<?php

namespace App\Exports;

use App\Models\Vendor;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class ReturSheet implements FromCollection, WithHeadings, WithTitle
{
    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $filters     = $this->filters;
        $monthFilter = $filters['month'] ?? Carbon::now()->format('Y-m');
        $yearMonth   = Carbon::createFromFormat('Y-m', $monthFilter);
        $year        = $yearMonth->year;
        $month       = $yearMonth->month;
        $lastDay     = $yearMonth->daysInMonth;

        // Ambil vendor sesuai filter kota (jika ada)
        $kota = $filters['kota'] ?? null;
        $vendorQuery = Vendor::query();
        if ($kota) {
            $vendorQuery->whereHas('wilayah.daerah', function($q) use ($kota) {
                $q->where('kota', $kota);
            });
        }

        // Muat tagihan beserta retur untuk bulan yang dipilih
        $vendorQuery->with(['tagihan' => function($q) use ($year, $month) {
            $q->whereYear('tanggal_masuk', $year)
              ->whereMonth('tanggal_masuk', $month)
              ->with('retur');
        }]);

        $vendors = $vendorQuery->get();

        $rows = [];
        foreach ($vendors as $vendor) {
            $row = [];
            $row['Vendor ID']   = $vendor->id;
            $row['Vendor Name'] = $vendor->nama;

            // Indeks retur berdasarkan hari (dari tagihan)
            $returByDay = [];
            foreach ($vendor->tagihan as $tagihan) {
                $day = Carbon::parse($tagihan->tanggal_masuk)->day;
                if ($tagihan->retur) {
                    $returByDay[$day] = $tagihan->retur->jumlah_retur;
                }
            }
            for ($d = 1; $d <= $lastDay; $d++) {
                $row[(string)$d] = isset($returByDay[$d]) ? $returByDay[$d] : '';
            }
            $rows[] = $row;
        }
        return collect($rows);
    }

    public function headings(): array
    {
        $monthFilter = $this->filters['month'] ?? Carbon::now()->format('Y-m');
        $yearMonth   = Carbon::createFromFormat('Y-m', $monthFilter);
        $lastDay     = $yearMonth->daysInMonth;

        $headings = ['Vendor ID', 'Vendor Name'];
        for ($d = 1; $d <= $lastDay; $d++) {
            $headings[] = $d;
        }
        return $headings;
    }

    public function title(): string
    {
        return 'Retur';
    }
}
