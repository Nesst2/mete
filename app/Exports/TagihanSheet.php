<?php

namespace App\Exports;

use App\Models\Vendor;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class TagihanSheet implements FromCollection, WithHeadings, WithTitle
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

        // Muat tagihan untuk bulan yang dipilih
        $vendorQuery->with(['tagihan' => function($q) use ($year, $month) {
            $q->whereYear('tanggal_masuk', $year)
              ->whereMonth('tanggal_masuk', $month);
        }]);

        $vendors = $vendorQuery->get();

        $rows = [];
        foreach ($vendors as $vendor) {
            $row = [];
            $row['Vendor ID']   = $vendor->id;
            $row['Vendor Name'] = $vendor->nama;

            // Indeks tagihan berdasarkan hari
            $tagihanByDay = [];
            foreach ($vendor->tagihan as $tagihan) {
                $day = Carbon::parse($tagihan->tanggal_masuk)->day;
                $tagihanByDay[$day] = $tagihan;
            }

            for ($d = 1; $d <= $lastDay; $d++) {
                if (isset($tagihanByDay[$d])) {
                    $tag = $tagihanByDay[$d];
                    $status = strtolower($tag->status_kunjungan);
                    if ($status === 'ada orang' || $status === 'tunggu') {
                        $cell = number_format($tag->uang_masuk, 0);
                    } elseif ($status === 'tidak ada orang') {
                        $cell = 'T';
                    } elseif ($status === 'masih') {
                        $cell = 'M';
                    } else {
                        $cell = '';
                    }
                } else {
                    $cell = '';
                }
                $row[(string)$d] = $cell;
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
        return 'Tagihan';
    }
}
